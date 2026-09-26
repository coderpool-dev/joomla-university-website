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

use JchOptimize\Core\Admin\MultiSelectItems;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die('Restricted access');

extract($displayData);

/**
 * @var FormField $field
 * @var array $value
 * @var string $valueType
 * @var MultiSelectItems $multiSelect
 * @var array $dataAttributes
 * @var array $subfields
 * @var array $jsSubfields
 */

$value = array_values($value);
$nextIndex = count($value);
$field->layout = 'joomla.form.field.list';
$fieldName = $field->fieldname;

// JSON config for JS (what getSubConfigs() will read)
$json = json_encode(
    $jsSubfields,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<?php $subfieldsCount = count($subfields); ?>
<script type="application/json" id="subfields-<?= $fieldName; ?>">
    <?= $json; ?>





</script>

<fieldset id="fieldset-<?= $fieldName; ?>" data-index="<?= (int)$nextIndex; ?>"
          data-value-type="<?= htmlspecialchars($valueType, ENT_COMPAT, 'UTF-8'); ?>"
          class="mb-1 jch-ms-fieldset-grid" style="--jch-ms-subfield-count: <?= $subfieldsCount; ?>"
>

    <?php if ($subfields) : ?>
            <?php foreach ($subfields as $sf) : ?>
                <?php if (!empty($sf['header'])) : ?>
                    <span class="jch-ms-<?= htmlspecialchars($sf['name']); ?>-header jch-ms-cell jch-ms-header">
                        &nbsp;&nbsp;<?= htmlspecialchars($sf['header']); ?>&nbsp;&nbsp;
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
    <?php endif; ?>

    <?php foreach ($value as $i => $v) : ?>
        <?php if (isset($v[$valueType]) && is_string($v[$valueType])) : ?>
                <span class="group<?= $i; ?> jch-ms-excludes jch-ms-cell">
                    <span>
                        <input type="text" readonly
                               size="<?= max(11, (int)(strlen($v[$valueType]) / 2)); ?>"
                               value="<?= htmlspecialchars($v[$valueType]); ?>"
                               name="jform[<?= $fieldName; ?>][<?= $i; ?>][<?= $valueType; ?>]">
                        <?php
                        $method = 'prepare' . ucfirst((string)($dataAttributes['data-jch_group'] ?? '')) . 'Values';
                        ?>
                        <?php if (method_exists($multiSelect, $method)) : ?>
                            <?= $multiSelect->$method($v[$valueType]); ?>
                        <?php else : ?>
                            <?= $v[$valueType]; ?>
                        <?php endif; ?>
                        <button type="button" class="jch-multiselect-remove-button"
                                aria-label="<?= Text::_('JGLOBAL_FIELD_REMOVE'); ?>">
                            <?= Text::_('JGLOBAL_FIELD_REMOVE'); ?>
                        </button>
                    </span>
                </span>

                <?php foreach ($subfields as $sf) : ?>
                    <?php
                    $layoutName = $sf['layout'] ?: ('subfield.' . $sf['type']);
                    echo LayoutHelper::render(
                        $layoutName,
                        [
                            'subFieldClass' => 'jch-ms-' . $sf['name'],
                            'fieldName' => $fieldName,
                            'i' => $i,
                            'v' => $v,
                            'option' => $sf['name'],
                            'class' => $sf['class'] ?? '',
                            'config' => $sf,
                        ],
                        __DIR__
                    );
                    ?>
                <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</fieldset>

<div id="div-<?= $fieldName; ?>">
    <?= $field->input; ?>
    <button type="button" class="btn btn-sm btn-secondary jch-multiselect-add-button">
        <?= Text::_('COM_JCHOPTIMIZE_ADD_ITEM'); ?>
    </button>
</div>
