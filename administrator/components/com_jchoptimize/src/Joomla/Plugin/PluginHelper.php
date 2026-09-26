<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

abstract class PluginHelper extends \Joomla\CMS\Plugin\PluginHelper
{
    public static bool $testEnabled = false;
    /**
     * Used to reset the plugins list after one has been modified to
     * force a reload from the database
     */
    public static function reload(): void
    {
        static::$plugins = null;
    }

    public static function isEnabled($type, $plugin = null): bool
    {
        if (self::$testEnabled) {
            return true;
        }

        return parent::isEnabled($type, $plugin);
    }
}
