<?php

namespace CodeAlfa\Module\JchDashIcons\Administrator\Dispatcher;

use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use CodeAlfa\Module\JchDashIcons\Administrator\Helper\JchDashIconsHelper;
use JchOptimize\Core\Admin\Icons;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Throwable;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    public function getLayoutData(): bool|array
    {
        $data = parent::getLayoutData();

        try {
            /** @var JchOptimizeComponent $component */
            $component = $this->getApplication()->bootComponent('com_jchoptimize');
            $icons = $component->getContainer()->get(Icons::class);
            $mvcFactory = $component->getMVCFactory();

            /** @var JchDashIconsHelper $helper */
            $helper = $this->getHelperFactory()->getHelper('JchDashIconsHelper');
            $data['buttons'] = $helper->getButtons($data['params'], $icons, $mvcFactory);
        } catch (Throwable) {
            return false;
        }

        return $data;
    }
}
