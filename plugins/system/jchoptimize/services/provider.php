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

use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use CodeAlfa\Plugin\System\JchOptimize\Extension\JchOptimize;
use JchOptimize\Core\Registry;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container): PluginInterface {
                $config = (array)PluginHelper::getPlugin('system', 'jchoptimize');
                $subject = $container->get(DispatcherInterface::class);
                $app = Factory::getApplication();

                //Boot component before initializing plugin to register required autoload classes
                $component = $app->bootComponent('com_jchoptimize');

                if ($component instanceof JchOptimizeComponent) {
                    $config['params'] = $component->getContainer()->get(Registry::class);
                }

                $plugin = new JchOptimize($subject, $config);
                $plugin->setApplication($app);

                if ($component instanceof JchOptimizeComponent) {
                    $plugin->setContainer($component->getContainer());
                }

                return $plugin;
            }
        );
    }
};
