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

use CodeAlfa\Component\JchOptimize\Administrator\Container\ContainerFactory;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecorator;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory as ComponentDispatcherFactoryProvider;
use Joomla\CMS\Extension\Service\Provider\MVCFactory as MVCFactoryProvider;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\ApiMVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

defined('_JEXEC') or die('Restricted Access');

require_once __DIR__ . '/../autoload.php';

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $namespace = '\\CodeAlfa\\Component\\JchOptimize';
        $container->registerServiceProvider(new MVCFactoryProvider($namespace));
        $container->registerServiceProvider(new ComponentDispatcherFactoryProvider($namespace));

        $container->extend(MVCFactoryInterface::class, [$this, 'extendMVCFactoryProvider']);

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new JchOptimizeComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                $compContainer = ContainerFactory::getInstance($container);
                $component->setContainer($compContainer);

                return $component;
            }
        );
    }

    public function extendMVCFactoryProvider(MVCFactoryInterface $factory, Container $container): MVCFactoryInterface
    {
        if ($factory instanceof ApiMVCFactory) {
            return $factory;
        }

        $mvcFactoryDecorator = new MVCFactoryDecorator($factory);
        $mvcFactoryDecorator->setContainer(ContainerFactory::getInstance($container));

        return $mvcFactoryDecorator;
    }
};
