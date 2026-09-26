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
use CodeAlfa\Component\JchOptimize\Administrator\Model\ApiParamsModel;
use CodeAlfa\Component\JchOptimize\Administrator\View\OptimizeImages\HtmlView;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\CMS\MVC\View\ViewInterface;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\Input\Input;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class OptimizeImagesTabDisplayController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    private ViewInterface|HtmlView $view;

    private ModelInterface|ApiParamsModel $model;

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);
    }

    public function display($cachable = false, $urlparams = []): OptimizeImagesTabDisplayController
    {
        $status = $this->input->get('status');
        $view = $this->getView('OptimizeImages', 'html', 'Administrator');

        if (is_null($status)) {
            $view->setModel($this->getModel('ApiParams'));
        } else {
            if ($status == 'success') {
                $cnt = $this->input->getInt('cnt', 0);
                $webp = $this->input->getInt('webp', 0);

                $this->app->enqueueMessage(sprintf(
                    JText::_('%1$d images successfully optimized, %2$d WEBPs generated.'),
                    $cnt,
                    $webp
                ));
            } else {
                $msg = $this->input->getString('msg', '');
                $this->app->enqueueMessage(
                    JText::_('Image optimization failed with message: "' . urldecode($msg) . '"'),
                    'error'
                );
            }

            $this->app->redirect(JRoute::_('index.php?option=com_jchoptimize&view=OptimizeImages', false));
        }

        return parent::display($cachable, $urlparams);
    }
}
