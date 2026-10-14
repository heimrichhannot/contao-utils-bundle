<?php

namespace HeimrichHannot\UtilsBundle\SearchVector;

use Composer\InstalledVersions;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Util\LocaleUtil;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Runs code in the language of a record, independent of the language of the current user.
 *
 * Since Contao 5.6.3 the locale is switched with the Symfony locale switcher, as Contao itself does. It also updates
 * the translator and the legacy $GLOBALS['TL_LANGUAGE']. Contao 5.3 to 5.6.2 set the translator and
 * $GLOBALS['TL_LANGUAGE'] directly, so the locale switcher does not know the language of the user there. In these
 * versions the translator and $GLOBALS['TL_LANGUAGE'] are switched directly as well.
 *
 * All loaded language files are reloaded in the language of the record and afterwards in the previous language, as
 * labels can come from the language files of any table. Language files loaded by the callback are restored as well,
 * so the current user never ends up with labels in the language of the record.
 *
 * @internal
 */
class SearchVectorLanguageSwitcher
{
    /**
     * Contao version that switches the locale of the user with the Symfony locale switcher.
     */
    private const LOCALE_SWITCHER_SINCE = '5.6.3';

    private readonly bool $contaoUsesLocaleSwitcher;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly TranslatorInterface $translator,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
        // Null if the core bundle is replaced, e.g. by the contao/contao monorepo
        $version = InstalledVersions::getVersion('contao/core-bundle');

        $this->contaoUsesLocaleSwitcher = null === $version || \str_starts_with($version, 'dev-') || \version_compare($version, self::LOCALE_SWITCHER_SINCE, '>=');
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function run(string $locale, string $table, callable $callback): mixed
    {
        $locale = LocaleUtil::formatAsLocale($locale);
        $previousLocale = $this->getCurrentLocale();

        if ($locale === $previousLocale) {
            // The DCA loader does not load translations, so the labels of the table might be missing otherwise
            $this->loadLanguageFiles(['default', $table], $locale);

            return $callback();
        }

        try {
            return $this->switchLocale($locale, function () use ($locale, $table, $callback): mixed {
                $this->loadLanguageFiles([...LoadedLanguageFiles::getNames(), 'default', $table], $locale);

                return $callback();
            });
        } finally {
            $this->loadLanguageFiles([...LoadedLanguageFiles::getNames(), 'default', $table], $previousLocale);
        }
    }

    private function getCurrentLocale(): string
    {
        if ($this->contaoUsesLocaleSwitcher) {
            return LocaleUtil::formatAsLocale($this->localeSwitcher->getLocale());
        }

        // Contao before 5.6.3
        return LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE'] ?? ($this->translator instanceof LocaleAwareInterface ? $this->translator->getLocale() : 'en'));
    }

    /**
     * @param callable(): mixed $callback
     */
    private function switchLocale(string $locale, callable $callback): mixed
    {
        if ($this->contaoUsesLocaleSwitcher) {
            return $this->localeSwitcher->runWithLocale($locale, $callback);
        }

        // Contao before 5.6.3, which sets the translator and $GLOBALS['TL_LANGUAGE'] directly as well
        $previousLanguage = $GLOBALS['TL_LANGUAGE'] ?? null;
        $translator = $this->translator instanceof LocaleAwareInterface ? $this->translator : null;
        $previousTranslatorLocale = $translator?->getLocale();

        $GLOBALS['TL_LANGUAGE'] = LocaleUtil::formatAsLanguageTag($locale);
        $translator?->setLocale($locale);

        try {
            return $callback();
        } finally {
            if (null !== $previousTranslatorLocale) {
                $translator->setLocale($previousTranslatorLocale);
            }

            if (null === $previousLanguage) {
                unset($GLOBALS['TL_LANGUAGE']);
            } else {
                $GLOBALS['TL_LANGUAGE'] = $previousLanguage;
            }
        }
    }

    /**
     * @param list<string> $names
     */
    private function loadLanguageFiles(array $names, string $locale): void
    {
        $controller = $this->framework->getAdapter(Controller::class);

        foreach (\array_unique($names) as $name) {
            $controller->loadLanguageFile($name, $locale);
        }
    }
}
