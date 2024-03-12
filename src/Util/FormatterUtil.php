<?php

namespace HeimrichHannot\UtilsBundle\Util;

use Codefog\TagsBundle\Model\TagModel;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\DataContainer;
use Contao\Date;
use Contao\Environment;
use Contao\Model;
use Contao\StringUtil as Str;
use Contao\System;
use Contao\Validator;

class FormatterUtil
{
    public function __construct(
        protected ContaoFramework $framework,
        protected InsertTagParser $insertTagParser,
        protected Utils $utils,
        protected array $kernelBundles
    ) {}

    const OPTION_PRESERVE_EMPTY_ARRAY_VALUES = 2^0;
    const OPTION_SKIP_LOCALIZATION = 2^1;
    const OPTION_SKIP_DCA_LOADING = 2^2;
    const OPTION_SKIP_OPTION_CACHING = 2^3;
    const OPTION_SKIP_REPLACE_INSERT_TAGS = 2^4;
    /**
     * @var FormatterUtil::OPTION_CALL_IS_RECURSIVE Whether the current call is recursive.
     * @internal You should not set this option manually.
     */
    const OPTION_CALL_IS_RECURSIVE = 2^5;

    /**
     * Makes a DCA field value human-readable.
     *
     * {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Form/FormUtil.php#L99 This succeeds `prepareSpecialValueForOutput(...)` from Utils v2.}
     *
     * @param DataContainer $dc The data container whose table to use and options-callback to evaluate.
     * @param string $field The DCA field name.
     * @param array|string|null $value The value to format. If an array is passed, the values will be evaluated recursively.
     * @param int $settings Additional settings flags.
     * @param string $arrayJoiner The string that joins array values. Default: ', '.
     * @param array|null $dcaOverride Override the DCA field settings. If not set, the DCA field settings will be used.
     * @param array|null $cachedOptions The cached options to use. If not set, the options-callback will be evaluated.
     * @return mixed The formatted value.
     *
     * @see FormatterUtil::OPTION_PRESERVE_EMPTY_ARRAY_VALUES Preserve empty array values.
     * @see FormatterUtil::OPTION_SKIP_LOCALIZATION Skip localization.
     * @see FormatterUtil::OPTION_SKIP_DCA_LOADING Skip DCA loading.
     * @see FormatterUtil::OPTION_SKIP_OPTION_CACHING Skip option caching.
     * @see FormatterUtil::OPTION_SKIP_REPLACE_INSERT_TAGS Skip replace insert tags.
     */
    public function formatDcaFieldValue(
        DataContainer     $dc,
        string            $field,
        array|string|null $value,
        int               $settings = 0,
        string            $arrayJoiner = ', ',
        array             $dcaOverride = null,
        ?array            $cachedOptions = null
    ): mixed {
        $value = Str::deserialize($value);
        $table = $dc->table;

        [$system, $controller] = $this->prepareServices();

        if (~$settings & self::OPTION_SKIP_DCA_LOADING) {
            $controller->loadDataContainer($table);
            $system->loadLanguageFile($table);
        }

        // dca can be overridden from outside
        $data = is_array($dcaOverride)
            ? $dcaOverride
            : ($GLOBALS['TL_DCA'][$table]['fields'][$field] ?? null);

        if (!is_array($data)) {
            return $value;
        }

        $inputType = $data['inputType'] ?? null;

        if ($inputType === 'inputUnit')
        {
            return $this->formatInputUnitField($value, $arrayJoiner);
        }

        if ($cachedOptions === null || $settings & self::OPTION_SKIP_OPTION_CACHING)
        {
            $cachedOptions = $data['options'] ?? $this->utils->dca()
                ->executeCallback($data['options_callback'] ?? null, $dc);
        }

        if (!is_array($cachedOptions)) {
            $cachedOptions = [];
        }

        if ($inputType === 'multiColumnEditor' && $this->isMultiColumnsActive() && is_array($value))
        {
            return $this->formatMultiColumnField(
                $value,
                $data,
                function (string $f, array|string|null $v) use (
                    $dc,
                    $settings,
                    $arrayJoiner,
                    $dcaOverride,
                    $cachedOptions
                ): string {
                    return $this->formatDcaFieldValue(
                        $dc,
                        $f,
                        $v,
                        $settings,
                        $arrayJoiner,
                        $dcaOverride,
                        $cachedOptions
                    );
                }
            );
        }

        if (is_array($value))
        {
            return $this->formatArray(
                $value,
                $settings,
                $arrayJoiner,
                function (array|string|null $v) use (
                    $dc,
                    $field,
                    $settings,
                    $arrayJoiner,
                    $dcaOverride,
                    $cachedOptions
                ): string {
                    return $this->formatDcaFieldValue(
                        $dc,
                        $field,
                        $v,
                        $settings | self::OPTION_CALL_IS_RECURSIVE,
                        $arrayJoiner,
                        $dcaOverride,
                        $cachedOptions
                    );
                }
            );
        }

        if ($inputType === 'explanation' && isset($data['eval']['text']))
        {
            return $data['eval']['text'];
        }

        $rgxp = $data['eval']['rgxp'] ?? null;

        if (!empty($data['foreignKey']))
        {
            [$foreignTable, $foreignField] = explode('.', $data['foreignKey']);

            $instance = $this->utils->model()->findModelInstanceByPk($foreignTable, $value);
            if (null !== $instance) {
                $value = $instance->{$foreignField};
            }
        }

        if ($inputType === 'cfgTags' && ($tagModel = $this->getTagModel()))
        {
            $collection = $tagModel->findBy(['source=?', 'id = ?'], [$data['eval']['tagsManager'], $value]);
            $value = null;

            if (null !== $collection) {
                $result = $collection->fetchEach('name');
                $value = implode($arrayJoiner, $result);
            }
        }
        elseif ($rgxp === 'date')
        {
            $value = Date::parse(Config::get('dateFormat'), $value);
        }
        elseif ($rgxp === 'time')
        {
            $value = Date::parse(Config::get('timeFormat'), $value);
        }
        elseif ($rgxp === 'datim')
        {
            $value = Date::parse(Config::get('datimFormat'), $value);
        }
        elseif (Validator::isBinaryUuid($value))
        {
            $strPath = $this->utils->file()->getPathFromUuid($value);
            $value = $strPath ? Environment::get('url') . '/' . $strPath : Str::binToUuid($value);
        }
        elseif ($data['eval']['isBoolean'] ??
            $inputType === 'checkbox' && !($data['eval']['multiple'] ?? false))
        {
            $value = ('' != $value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
        }
        elseif (is_array($cachedOptions) && !array_is_list($cachedOptions))
        {
            $value = $cachedOptions[$value] ?? $value;
        }

        if (~$settings & self::OPTION_SKIP_LOCALIZATION
            && ($reference = $data['reference'][$value] ?? null))
        {
            $value = is_array($reference)
                ? $reference[0] ?? $reference[array_key_first($reference)] ?? $value
                : $reference;
        }

        if ($data['eval']['encrypt'] ?? false)
        {
            [$encrypted, $iv] = explode('.', $value);
            $key = System::getContainer()->getParameter('secret');
            $value = openssl_decrypt($encrypted, 'aes-256-ctr', $key, 0, base64_decode($iv, true));
        }

        if (~$settings & self::OPTION_SKIP_REPLACE_INSERT_TAGS)
        {
            $value = $this->insertTagParser->replace($value);
        }

        return Str::specialchars($value);
    }

    /** @return array<System, Controller> */
    private function prepareServices(): array
    {
        /** @var System $system */
        $system = $this->framework->getAdapter(System::class);

        /** @var Controller $controller */
        $controller = $this->framework->getAdapter(Controller::class);

        $system->loadLanguageFile('default');

        return [$system, $controller];
    }

    private function getTagModel(): ?Model
    {
        if (class_exists(TagModel::class)) {
            return $this->framework->getAdapter(TagModel::class);
        }

        return null;
    }

    private function isMultiColumnsActive(): bool
    {
        return in_array(
            'HeimrichHannot\MultiColumnEditorBundle\HeimrichHannotContaoMultiColumnEditorBundle',
            $this->kernelBundles
        );
    }

    private function formatArray(array $values, int $settings, string $arraySeparator, callable $callback): string
    {
        foreach ($values as $k => $v)
        {
            $result = $callback($v);

            if ($settings & self::OPTION_PRESERVE_EMPTY_ARRAY_VALUES)
            {
                $values[$k] = $result;
                continue;
            }

            if (empty($result))
            {
                unset($values[$k]);
            }
            else
            {
                $values[$k] = $result;
            }
        }

        return implode($arraySeparator, $values);
    }

    private function formatInputUnitField(array|string|null $values, string $arraySeparator): string
    {
        $data = Str::deserialize($values, true);
        return ($data['value'] ?? '') . $arraySeparator . ($data['unit'] ?? '');
    }

    private function formatMultiColumnField(array $values, array $data, callable $callback = null): string
    {
        $formatted = '';

        foreach ($values as $row) {
            $formatted .= "\t\n";

            foreach ($row as $fieldName => $fieldValue) {
                $dca = $data['eval']['multiColumnEditor']['fields'][$fieldName];

                $label = '';

                if (!$data['eval']['skipMceFieldLabels']) {
                    $label = ($dca['label'][0] ?: $fieldName).': ';

                    if ($data['eval']['skipMceFieldLabelFormatting']) {
                        $label = $fieldName.': ';
                    }
                }

                $formatted .= "\t" . $label . ($callback ? $callback($fieldName, $fieldValue) : '');
            }
        }

        $formatted .= "\t\n";

        return $formatted;
    }
}