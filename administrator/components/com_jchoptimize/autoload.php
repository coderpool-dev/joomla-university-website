<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use CodeAlfa\Component\JchOptimize\Administrator\Container\ContainerFactory;

defined('_JEXEC') or die('Restricted access');

if (!defined('_JCH_EXEC')) {
    define('_JCH_EXEC', 1);
}

if (!defined('_JCH_BASE_DIR')) {
    define('_JCH_BASE_DIR', __DIR__);
}

require_once __DIR__ . '/version.php';
require_once __DIR__ . '/class_map.php';
require_once __DIR__ . '/lib/vendor/scoper-autoload.php';

if (!class_exists('\JchOptimize\Container\ContainerFactory', false)) {
    class_alias(ContainerFactory::class, '\\JchOptimize\\Container\\ContainerFactory');
}
