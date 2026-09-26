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

namespace CodeAlfa\Component\JchOptimize\Administrator\Controller;

use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use Joomla\CMS\MVC\Controller\BaseController;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class AjaxController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    public function filetree(): void
    {
        echo AdminAjax::getInstance('FileTree')->run();

        $this->app->close();
    }

    public function multiselect(): void
    {
        echo AdminAjax::getInstance('MultiSelect')->run();

        $this->app->close();
    }

    public function optimizeimage(): void
    {
        echo AdminAjax::getInstance('OptimizeImage')->run();

        $this->app->close();
    }

    public function smartcombine(): void
    {
        echo AdminAjax::getInstance('SmartCombine')->run();

        $this->app->close();
    }

    public function garbagecron(): void
    {
        echo AdminAjax::getInstance('GarbageCron')->run();

        $this->app->close();
    }
}
