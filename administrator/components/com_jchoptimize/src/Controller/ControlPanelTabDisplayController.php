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

use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use CodeAlfa\Component\JchOptimize\Administrator\Model\UpdatesModel;
use JchOptimize\Core\PageCache\CaptureCache;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

use function base64_encode;
use function defined;

use const JCH_PRO;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ControlPanelTabDisplayController extends BaseController implements
    ContainerAwareInterface,
    MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;
    use ContainerAwareTrait;

    protected $default_view = 'ControlPanelTabDisplay';

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);
    }

    public function display($cachable = false, $urlparams = []): static
    {
        $redirect = $this->manageUpdates();

        if (JCH_PRO) {
            $this->getContainer()->get(CaptureCache::class)->updateHtaccess();
        }

        //$this->cdn->updateHtaccess();

        if (!PluginHelper::isEnabled('system', 'jchoptimize')) {
            $editUrl = Route::_(
                'index.php?option=com_jchoptimize&view=ModeSwitcher&task=setProduction&return=' . base64_encode(
                    (string)Uri::getInstance()
                ),
                false
            );

            $this->app->enqueueMessage(
                Text::sprintf('COM_JCHOPTIMIZE_PLUGIN_NOT_ENABLED', $editUrl),
                'warning'
            );

            $redirect = true;
        }

        if (
            $redirect
            || $this->input->getCmd('option') != 'com_cpanel'
            || $this->input->getCmd('view') != 'cpanel'
            || $this->input->getCmd('dashboard') != 'com_jchoptimize.cpanel'
        ) {
            $this->setRedirect(
                Route::_('index.php?option=com_cpanel&view=cpanel&dashboard=com_jchoptimize.cpanel', false)
            );
        }

        return $this;
    }

    private function manageUpdates(): bool
    {
        //$this->updatesModel->upgradeLicenseKey();
        //$this->updatesModel->refreshUpdateSite();
        //$this->updatesModel->removeObsoleteUpdateSites();
        /** @var UpdatesModel $model */
        $model = $this->getModel('Updates');
        $model->removeObsoleteUpdateSites();

        if (JCH_PRO) {
            if ($model->getLicenseKey() == '') {
                $dlidEditUrl = Route::_(
                    'index.php?option=com_installer&view=updatesites&filter[search]=JCH Optimize&filter[supported]=1'
                );

                $this->app->enqueueMessage(
                    Text::sprintf('COM_JCHOPTIMIZE_DOWNLOADID_MISSING', $dlidEditUrl),
                    'warning'
                );

                return true;
            }
        }

        return false;
    }
}
