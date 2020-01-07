<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Rsce;

use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Form\FormUtil;
use MadeYourDay\RockSolidCustomElements\CustomElements;

class RsceUtil
{
    /**
     * @var FormUtil
     */
    private $formUtil;

    public function __construct(FormUtil $formUtil)
    {
        $this->formUtil = $formUtil;
    }

    public function explodeRsceData(string $cteType, string $data, int $recordId = 0, $options = [])
    {
        $result = [];
        $rsceData = json_decode($data, true);
        $dca = CustomElements::getConfigByType($cteType);

        $skipFormat = isset($options['skipFormatting']) && $options['skipFormatting'];
        $outputPrepareConfig = isset($options['outputPrepareConfig']) && \is_array($options['outputPrepareConfig']) ? $options['outputPrepareConfig'] : [];

        $skipFields = isset($options['skipFields']) && \is_array($options['skipFields']) ? $options['skipFields'] : [];
        $fields = isset($options['fields']) && \is_array($options['fields']) ? $options['fields'] : [];

        $nestedSkipFields = isset($options['nestedSkipFields']) && \is_array($options['nestedSkipFields']) ? $options['nestedSkipFields'] : [];
        $nestedFields = isset($options['nestedFields']) && \is_array($options['nestedFields']) ? $options['nestedFields'] : [];
        $nestedFieldSeparator = $options['nestedFieldSeparator'] ?? "\t";
        $nestedRowSeparator = $options['nestedRowSeparator'] ?? "\t\n";
        $skipNestedFieldLabels = $options['skipNestedFieldLabels'] ?? false;
        $skipNestedFieldLabelFormatting = $options['skipNestedFieldLabelFormatting'] ?? false;

        $dc = new DC_Table_Utils('tl_content');
        $dc->activeRecord = $rsceData;
        $dc->id = $recordId;

        foreach ($dca['fields'] as $field => $fieldData) {
            if (\in_array($field, $skipFields) || (\is_array($fields) && !\in_array($field, $fields))) {
                continue;
            }

            $value = $rsceData[$field];

            if (isset($fieldData['fields']) && \is_array($fieldData['fields']) && \is_array($value)) {
                $nestedResult = '';

                foreach ($value as $row) {
                    $nestedResult .= $nestedRowSeparator;

                    foreach ($row as $nestedField => $nestedFieldValue) {
                        if (\in_array($nestedField, $nestedSkipFields) || (\is_array($nestedFields) && !\in_array($nestedField, $nestedFields))) {
                            continue;
                        }

                        $dca = $fieldData['fields'][$nestedField];

                        $label = '';

                        if (!$skipNestedFieldLabels) {
                            $label = ($dca['label'][0] ?: $nestedField).': ';

                            if ($skipNestedFieldLabelFormatting) {
                                $label = $nestedField.': ';
                            }
                        }

                        $nestedResult .= $nestedFieldSeparator.$label.$this->formUtil->prepareSpecialValueForOutput($nestedField, $nestedFieldValue, $dc, array_merge($outputPrepareConfig, [
                                '_dcaOverride' => $dca,
                            ]));
                    }
                }

                // new line - add "\t\n" after each line and not only "\n" to prevent outlook line break remover
                $nestedResult .= $nestedRowSeparator;

                $result[$field] = $nestedResult;

                continue;
            }

            if (!$skipFormat) {
                $value = $this->formUtil->prepareSpecialValueForOutput(
                    $field, $value, $dc, array_merge($outputPrepareConfig, [
                        '_dcaOverride' => $fieldData,
                    ])
                );
            }

            $result[$field] = $value;
        }

        return $result;
    }
}
