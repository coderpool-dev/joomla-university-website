<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
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

class OptimizeImageController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    public function optimizeimage(): void
    {
        AdminAjax::getInstance('OptimizeImage')->run();

        $this->app->close();
    }
}
