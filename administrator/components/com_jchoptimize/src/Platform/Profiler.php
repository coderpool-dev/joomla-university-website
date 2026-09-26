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

namespace CodeAlfa\Component\JchOptimize\Administrator\Platform;

use JchOptimize\Core\Platform\ProfilerInterface;
use Joomla\CMS\Profiler\Profiler as JProfiler;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

class Profiler implements ProfilerInterface
{
    public function attachProfiler(&$html, $isAmpPage = false)
    {
    }

    public function start($text, $mark = false): void
    {
        if ($mark) {
            self::mark('before' . $text);
        }
    }

    public function mark($text): void
    {
        JProfiler::getInstance('Application')->mark($text . ' plgSystem (JCH Optimize)');
    }

    /**
     * @param string $text
     * @param bool $mark
     *
     * @return void
     */
    public function stop($text, $mark = false): void
    {
        if ($mark) {
            self::mark('after' . $text);
        }
    }
}
