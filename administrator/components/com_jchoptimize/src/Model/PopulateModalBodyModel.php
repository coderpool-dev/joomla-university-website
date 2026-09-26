<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2024 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use JchOptimize\Core\Admin\HtmlCrawler;
use JchOptimize\Core\Html\HtmlProcessor;
use JchOptimize\Core\Model\PopulateModalBodyTrait;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class PopulateModalBodyModel extends BaseDatabaseModel
{
    use PopulateModalBodyTrait;

    public function setHtmlObj(HtmlCrawler $htmlCrawler): void
    {
        $this->htmlCrawler = $htmlCrawler;
    }

    public function setHtmlProcessor(HtmlProcessor $htmlProcessor): void
    {
        $this->htmlProcessor = $htmlProcessor;
    }
}
