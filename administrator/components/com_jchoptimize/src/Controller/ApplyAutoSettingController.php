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
use CodeAlfa\Component\JchOptimize\Administrator\Model\ConfigureModel;
use JchOptimize\Core\Exception\ExceptionInterface;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\Input\Input;

use function defined;
use function ini_set;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ApplyAutoSettingController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    public function __construct(
        $config = [],
        MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        ini_set('display_errors', 0);

        parent::__construct($config, $factory, $app, $input);
    }

    public function display($cachable = false, $urlparams = []): ApplyAutoSettingController
    {
        try {
            /** @var ConfigureModel $model */
            $model = $this->getModel('Configure');
            $model->applyAutoSettings((string)$this->input->get('autosetting', 's1'));
        } catch (ExceptionInterface $e) {
        }

        return parent::display($cachable, $urlparams);
    }
}
