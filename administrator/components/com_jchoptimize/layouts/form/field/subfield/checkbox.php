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
 * @var array  $config  // from $subfields[]
 */

$checked = false;

// Existing saved value?
if (array_key_exists($option, $v)) {
    $checked = (bool) $v[$option];
} elseif (!empty($config['checked'])) {
    // default for new rows
    $checked = true;
}
?>
<span class="group<?= $i; ?> <?= htmlspecialchars($subFieldClass); ?> jch-ms-cell has-subfield has-checkbox">
    <input type="checkbox"
           class="<?= htmlspecialchars($class); ?> subfield"
           name="jform[<?= $fieldName; ?>][<?= $i; ?>][<?= $option; ?>]"
        <?= $checked ? ' checked' : ''; ?> />
</span>
