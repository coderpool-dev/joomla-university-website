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

/**
 * @var string $fieldName
 * @var int $i
 * @var array $v
 * @var string $option Subfield name, e.g. 'crossorigin'
 * @var array $options Each: ['value' => string, 'label' => string]
 * @var string $class Classes for <input>
 * @var array $legacy Legacy checkbox keys, e.g. ['anonymous','use-credentials']
 * @var string $header Optional, for aria-label
 */

defined('_JEXEC') || die;

$name = "jform[{$fieldName}][{$i}][{$option}]";

// 1) Preferred: new stored value
$selected = isset($v[$option]) ? (string)$v[$option] : null;

// 2) Back-compat: infer from legacy checkboxes if new value missing
if ($selected === null) {
    $selected = '';
    foreach (($legacy ?? []) as $legacyKey) {
        if (!empty($v[$legacyKey])) {
            $selected = (string)$legacyKey;
            break;
        }
    }
}

// 3) aria label
$aria = !empty($header) ? $header : $option;

// Unique prefix per row to make ids stable
$idPrefix = "jchms-{$fieldName}-{$i}-{$option}-";
?>

<span class="group<?= $i; ?> jch-ms-cell has-subfield has-radio" role="radiogroup"
      aria-label="<?= htmlspecialchars($aria, ENT_QUOTES, 'UTF-8'); ?>">
  <?php foreach (($options ?? []) as $k => $opt) :
        $val = isset($opt['value']) ? (string)$opt['value'] : '';
        $lab = isset($opt['label']) ? (string)$opt['label'] : $val;
        $id = $idPrefix . $k;
        $isChecked = ($val === $selected);
        ?>
      <label class="jch-ms-radio-opt" for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
             title="<?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8'); ?>">
      <input id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>"
             type="radio"
             name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
             value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>"
             class="<?= htmlspecialchars(trim(($class ?? '') . ' subfield'), ENT_QUOTES, 'UTF-8'); ?>"
             <?= $isChecked ? 'checked' : ''; ?>
             aria-label="<?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8'); ?>">
      <span aria-hidden="true"><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8'); ?></span>
    </label>
  <?php endforeach; ?>
</span>

