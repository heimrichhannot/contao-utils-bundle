<?php

namespace HeimrichHannot\UtilsBundle\SearchVector\Stemmer;

use Contao\CoreBundle\Util\LocaleUtil;
use Wamania\Snowball\NotFoundException;
use Wamania\Snowball\StemmerManager;

/**
 * Snowball stemmer for Catalan, Danish, Dutch, English, Finnish, French, German, Italian, Norwegian, Portuguese,
 * Romanian, Russian, Spanish and Swedish, provided by wamania/php-stemmer.
 *
 * Requires `composer require wamania/php-stemmer`. Words of other languages are returned unchanged.
 */
class SnowballStemmer implements StemmerInterface
{
    private ?StemmerManager $stemmerManager = null;

    public function stem(string $word, string $locale): string
    {
        if (!\class_exists(StemmerManager::class)) {
            throw new \LogicException('The snowball stemmer requires the wamania/php-stemmer package. Run "composer require wamania/php-stemmer".');
        }

        try {
            return ($this->stemmerManager ??= new StemmerManager())->stem($word, LocaleUtil::getPrimaryLanguage($locale));
        } catch (NotFoundException) {
            return $word;
        }
    }
}
