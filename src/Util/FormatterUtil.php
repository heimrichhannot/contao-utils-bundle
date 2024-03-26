<?php

namespace HeimrichHannot\UtilsBundle\Util;

use Codefog\TagsBundle\Model\TagModel;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\DataContainer;
use Contao\Date;
use Contao\Environment;
use Contao\Model;
use Contao\StringUtil as Str;
use Contao\Validator;
use Contao\Widget;
use HeimrichHannot\UtilsBundle\Util\FormatterUtil\FormatDcaFieldValueOptions;

class FormatterUtil
{
    public function __construct(
        protected ContaoFramework $framework,
        protected InsertTagParser $insertTagParser,
        protected Utils $utils,
        protected array $kernelBundles
    ) {}

    /**
     * Makes a DCA field value human-readable.
     *
     * This succeeds {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Form/FormUtil.php#L99 `prepareSpecialValueForOutput(...)`} from Utils v2.
     *
     * @param DataContainer $dc The data container whose table to use and options-callback to evaluate.
     * @param string $field The DCA field name.
     * @param array|string|null $value The value to format. If an array is passed, the values will be evaluated
     *     recursively.
     * @param ?FormatDcaFieldValueOptions $settings Additional settings.
     * @return mixed The formatted value.
     */
    public function formatDcaFieldValue(
        DataContainer              $dc,
        string                     $field,
        array|string|null          $value,
        FormatDcaFieldValueOptions $settings = null
    ): mixed {
        $settings ??= new FormatDcaFieldValueOptions();

        $value = Str::deserialize($value);
        $table = $dc->table;

        $controller = $this->framework->getAdapter(Controller::class);
        $controller->loadLanguageFile('default');

        if ($settings->loadDca) {
            $controller->loadDataContainer($table);
            $controller->loadLanguageFile($table);
        }

        // dca can be overridden from outside
        $data = is_array($settings->dcaOverride)
            ? $settings->dcaOverride
            : ($GLOBALS['TL_DCA'][$table]['fields'][$field] ?? null);

        if (!is_array($data)) {
            return $value;
        }

        $inputType = $data['inputType'] ?? null;

        if ($inputType === 'inputUnit')
        {
            return $this->formatInputUnitField($value, $settings->arrayJoiner);
        }

        if ($settings->optionsCache === null || !$settings->cacheOptions)
        {
            $optionsCallback = $data['options_callback'] ?? null;
            $options = $data['options'] ?? null;
            $settings->optionsCache = $optionsCallback
                ? $this->utils->dca()->executeCallback($optionsCallback, $dc) ?? $options
                : $options;
        }

        if (!is_array($settings->optionsCache)) {
            $settings->optionsCache = [];
        }

        if ($inputType === 'multiColumnEditor' && $this->isMultiColumnsActive() && is_array($value))
        {
            $callback = function (int|string $f, array|string|null $v) use ($dc, $settings): string {
                return $this->formatDcaFieldValue($dc, $f, $v, $settings);
            };

            return $this->formatMultiColumnField($value, $data, $callback);
        }

        if (is_array($value))
        {
            $callback = function (array|string|null $v) use ($dc, $field, $settings): string {
                return $this->formatDcaFieldValue($dc, $field, $v, $settings);
            };

            return $this->formatArray($value, $settings, $callback);
        }

        if ($inputType === 'explanation'
            && (!empty($textCallback = $data['eval']['text_callback'] ?? null)
                || isset($data['eval']['text'])))
        {
            if ($textCallback) {
                $attributes = Widget::getAttributesFromDca($data, $field, $value, $field, $table, $dc);
                return $this->utils->dca()->executeCallback($textCallback, $attributes);
            }
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

        if ($inputType === 'cfgTags'
            && (
                /** @var Model $tagModel */
                $tagModel = $this->getTagModel()
            )
        ) {
            $collection = $tagModel->findBy(['source=?', 'id = ?'], [$data['eval']['tagsManager'], $value]);
            $value = null;

            if (null !== $collection) {
                $result = $collection->fetchEach('name');
                $value = implode($settings->arrayJoiner, $result);
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
        elseif (is_array($settings->optionsCache) && !array_is_list($settings->optionsCache))
        {
            $value = $settings->optionsCache[$value] ?? $value;
        }

        if ($settings->localize && ($reference = $data['reference'][$value] ?? null))
        {
            $value = is_array($reference)
                ? $reference[0] ?? $reference[array_key_first($reference)] ?? $value
                : $reference;
        }

        if ($settings->replaceInsertTags)
        {
            $value = $this->insertTagParser->replace($value);
        }

        return Str::specialchars($value);
    }

    /**
     * @return Adapter<Model>|Adapter<TagModel>|Model|TagModel|null
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     * @phpstan-ignore-next-line For PHPStan, this method returns an object of an unknown class.
     */
    private function getTagModel(): mixed
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

    /**
     * @param array $values
     * @param FormatDcaFieldValueOptions $settings
     * @param callable(array|string|null $value): string $callback The callback to format each value, possibly
     *     recursively.
     * @return string
     */
    private function formatArray(
        array                      $values,
        FormatDcaFieldValueOptions $settings,
        callable                   $callback
    ): string {
        foreach ($values as $k => $v)
        {
            $result = $callback($v);

            if ($settings->preserveEmptyArrayValues)
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

        return implode($settings->arrayJoiner, $values);
    }

    private function formatInputUnitField(array|string|null $values, string $arraySeparator): string
    {
        $data = Str::deserialize($values, true);
        return ($data['value'] ?? '') . $arraySeparator . ($data['unit'] ?? '');
    }

    /**
     * @param array $values
     * @param array $data
     * @param ?callable(int|string $field, array|string|null $value): string $callback
     *   Callback used to format each field value, possibly recursively.
     * @return string
     */
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