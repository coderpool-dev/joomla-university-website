<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die('Restricted access');

extract($displayData);

/**
 * @var string $subFieldClass
 * @var string $fieldName
 * @var int    $i
 * @var string $option
 * @var array  $v
 * @var string $class
 * @var array  $config
 */

$selected = isset($v[$option]) ? (string)$v[$option] : null;

if ($selected === null && isset($config['legacy'])) {
    $selected = '';
    foreach ($config['legacy'] as $legacyKey) {
        if (!empty($v[$legacyKey])) {
            $selected = (string) $legacyKey;
            break;
        }
    }
}

$options = $config['options'] ?? [];
?>
<span class="group<?= $i; ?> <?= htmlspecialchars($subFieldClass); ?> jch-ms-cell has-subfield has-select">
    <select class="<?= htmlspecialchars($class); ?> subfield"
            name="jform[<?= $fieldName; ?>][<?= $i; ?>][<?= $option; ?>]">
        <?php foreach ($options as $opt) : ?>
            <?php
            $val = (string) ($opt['value'] ?? '');
            $text = (string) ($opt['text'] ?? $val);
            $isSel = ($val === $selected);
            ?>
            <option value="<?= htmlspecialchars($val); ?>" <?= $isSel ? 'selected' : ''; ?>>
                <?= htmlspecialchars($text); ?>
            </option>
        <?php endforeach; ?>
    </select>
</span>
