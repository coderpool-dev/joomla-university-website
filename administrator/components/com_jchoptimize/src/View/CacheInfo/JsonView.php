<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\View\CacheInfo;

use JchOptimize\Core\Model\CacheMaintainer;

use function defined;
use function json_encode;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

class JsonView extends \Joomla\CMS\MVC\View\JsonView
{
    private CacheMaintainer $cacheMaintainer;

    public function setCacheMaintainer(CacheMaintainer $cacheMaintainer): void
    {
        $this->cacheMaintainer = $cacheMaintainer;
    }

    public function display($tpl = null): void
    {
        [$size, $numFiles] = $this->cacheMaintainer->getCacheSize();

        echo json_encode([
            'size' => $size,
            'numFiles' => $numFiles
        ]);
    }
}
