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

use JchOptimize\Core\FeatureHelpers\ResponsiveImages;

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

$value = $v[$option] ?? ($config['defaultValue'] ?? '');
?>
<span class="group<?= $i; ?> <?= htmlspecialchars($subFieldClass); ?> jch-ms-cell has-subfield has-text">
    <input type="text"
           class="<?= htmlspecialchars($class); ?> subfield"
           name="jform[<?= $fieldName; ?>][<?= $i; ?>][<?= $option; ?>]"
           value="<?= htmlspecialchars($value); ?>" >
</span>
