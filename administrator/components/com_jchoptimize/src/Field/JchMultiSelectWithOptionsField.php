<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Field;

use SimpleXMLElement;

use function array_merge;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

class JchMultiSelectWithOptionsField extends JchMultiSelectField
{
    protected $type = 'JchMultiSelectWithOptions';

    protected string $valueType = 'url';

    /**
     * @var array<int,array<string,mixed>>
     * [
     *   'name'   => string,
     *   'type'   => 'checkbox'|'text'|'select',
     *   'label'  => string,
     *   'header' => string,
     *   'class'  => string,
     *   'layout' => string|null,
     *   'checked'      => bool,            // checkbox
     *   'defaultValue' => string,         // text/select
     *   'options'      => array[]         // select
     * ]
     */
    protected array $subfields = [];

    protected $layout = 'form.field.jch-multiselect-with-options';

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        if ($element['valueType']) {
            $this->valueType = (string)$element['valueType'];
        }

        $this->subfields = $this->parseSubfields($element);

        // BC: no <subfield> = old 2-option mode
        if (!$this->subfields) {
            $this->subfields = $this->buildLegacySubfields($element);
        }

        return parent::setup($element, $value, $group);
    }

    protected function getLayoutData(): array
    {
        $jsSubfields = [];
        foreach ($this->subfields as $sf) {
            $cfg = [
                'name' => $sf['name'],
                'type' => $sf['type'],
            ];
            if (!empty($sf['class'])) {
                $cfg['class'] = $sf['class'];
            }
            if (array_key_exists('checked', $sf)) {
                $cfg['checked'] = $sf['checked'];
            }
            if (array_key_exists('defaultValue', $sf)) {
                $cfg['defaultValue'] = $sf['defaultValue'];
            }
            if (!empty($sf['options']) && is_array($sf['options'])) {
                $cfg['options'] = $sf['options'];
            }

            $jsSubfields[] = $cfg;
        }

        return array_merge(
            parent::getLayoutData(),
            [
                'valueType' => $this->valueType,
                'multiSelect' => $this->multiSelect,
                'subfields' => $this->subfields,
                'jsSubfields' => $jsSubfields,
            ]
        );
    }

    protected function getOptions(): array
    {
        // All options are dynamic via AJAX + JS
        return [];
    }

    /**
     * Parse <subfield> children from XML.
     *
     * @param SimpleXMLElement $element
     * @return array<int,array<string,mixed>>
     */
    private function parseSubfields(SimpleXMLElement $element): array
    {
        $subfields = [];

        foreach ($element->subfield as $sf) {
            $name = trim((string)$sf['name']);
            if ($name === '') {
                continue;
            }

            $type = (string)($sf['type'] ?: 'checkbox');
            $label = (string)($sf['label'] ?: '');
            $header = (string)($sf['header'] ?: $label);
            $class = (string)($sf['class'] ?: '');
            $layout = (string)($sf['layout'] ?: '');

            $cfg = [
                'name' => $name,
                'type' => $type,
                'label' => $label,
                'header' => $header,
                'class' => $class,
                'layout' => $layout ?: null,
            ];

            // Parse legacy="a,b,c" into ["a","b","c"]
            if (isset($sf['legacy'])) {
                $legacy = array_values(array_filter(array_map('trim', explode(',', (string)$sf['legacy']))));
                if ($legacy) {
                    $cfg['legacy'] = $legacy;
                }
            }

            // Parse default="..." generically (radio/select/text useful; checkbox optional)
            if (isset($sf['default'])) {
                $cfg['defaultValue'] = (string)$sf['default'];
            }

            if ($type === 'checkbox') {
                if (isset($sf['checked'])) {
                    $v = strtolower((string)$sf['checked']);
                    $cfg['checked'] = in_array($v, ['1', 'true', 'yes', 'on'], true);
                } elseif (isset($cfg['defaultValue'])) {
                    // Optional: allow defaultValue for checkbox too
                    $v = strtolower((string)$cfg['defaultValue']);
                    $cfg['checked'] = in_array($v, ['1', 'true', 'yes', 'on'], true);
                    unset($cfg['defaultValue']); // checkbox uses checked boolean
                }
            } elseif ($type === 'text') {
                // already handled by defaultValue
            } elseif ($type === 'select' || $type === 'radio') {
                $options = [];
                foreach ($sf->option as $opt) {
                    $optVal = (string)$opt['value'];
                    $optText = trim((string)$opt);

                    $row = [
                        'value' => $optVal,
                        // JS renderSubfield reads label for radio and text for select; we can provide both safely:
                        'text' => $optText,
                        'label' => $optText,
                    ];

                    if ($type === 'select') {
                        $selected = isset($opt['selected']) && in_array(
                            strtolower((string)$opt['selected']),
                            ['1', 'true', 'yes', 'selected'],
                            true
                        );
                        $row['selected'] = $selected;
                    }

                    $options[] = $row;
                }

                $cfg['options'] = $options;
            }

            $subfields[] = $cfg;
        }

        return $subfields;
    }


    /**
     * Build the old 2-checkbox config from attributes (for BC).
     */
    private function buildLegacySubfields(SimpleXMLElement $element): array
    {
        $option1 = (string)($element['option1'] ?: 'ieo');
        $option2 = (string)($element['option2'] ?: 'dontmove');
        $option1Header = (string)($element['option1Header'] ?: 'Ignore execution order');
        $option2Header = (string)($element['option2Header'] ?: 'Don\'t move to bottom');
        $subFieldClass = (string)($element['subFieldClass'] ?: '');

        return [
            [
                'name' => $option1,
                'type' => 'checkbox',
                'label' => $option1Header,
                'header' => $option1Header,
                'class' => $subFieldClass,
                'layout' => null,
            ],
            [
                'name' => $option2,
                'type' => 'checkbox',
                'label' => $option2Header,
                'header' => $option2Header,
                'class' => $subFieldClass,
                'layout' => null,
            ],
        ];
    }
}
