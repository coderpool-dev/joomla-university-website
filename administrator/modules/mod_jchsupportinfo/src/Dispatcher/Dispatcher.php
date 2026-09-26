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

namespace CodeAlfa\Module\JchSupportInfo\Administrator\Dispatcher;

use CodeAlfa\Component\JchOptimize\Administrator\Controller\ControlPanelTabDisplayController;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use Exception;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;

class Dispatcher extends AbstractModuleDispatcher
{
    public function dispatch(): void
    {
        $app = Factory::getApplication();
        $component = $app->bootComponent('com_jchoptimize');
        if (
            $component instanceof JchOptimizeComponent &&
            count($app->getMessageQueue()) <= 0
        ) {
            try {
                /** @var ControlPanelTabDisplayController $controller */
                $controller = $component->getMVCFactory()->createController(
                    'ControlPanelTabDisplay',
                    'Administrator',
                    [],
                    $app,
                    $app->getInput()
                );
                $controller->execute('display');
                $controller->redirect();
            } catch (Exception $e) {
            }
        }

        parent::dispatch();
    }
}
