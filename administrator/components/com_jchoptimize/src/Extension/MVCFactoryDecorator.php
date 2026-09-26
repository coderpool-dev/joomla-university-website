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

namespace CodeAlfa\Component\JchOptimize\Administrator\Extension;

use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Controller\ControllerInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\CMS\MVC\View\ViewInterface;
use Joomla\CMS\Table\TableInterface;
use Joomla\Input\Input;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

use function defined;
use function str_starts_with;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');

// phpcs:enable PSR1.Files.SideEffects

class MVCFactoryDecorator implements ContainerAwareInterface, MVCFactoryInterface
{
    use ContainerAwareTrait;

    public function __construct(private MVCFactoryInterface $factory)
    {
    }

    public function createController(
        $name,
        $prefix,
        array $config,
        CMSApplicationInterface $app,
        Input $input
    ): ?ControllerInterface {
        $controller = $this->factory->createController($name, $prefix, $config, $app, $input);

        if (!$controller instanceof ControllerInterface) {
            return null;
        }

        $this->setMVCFactoryDecoratorOnController($controller);
        $this->setDependenciesOnObject($controller);

        return $controller;
    }

    public function createModel($name, $prefix = '', array $config = []): ?ModelInterface
    {
        $model = $this->factory->createModel($name, $prefix, $config);

        if (!$model instanceof ModelInterface) {
            return null;
        }

        $this->setDependenciesOnObject($model);

        return $model;
    }

    public function createView($name, $prefix = '', $type = '', array $config = []): ?ViewInterface
    {
        $view = $this->factory->createView($name, $prefix, $type, $config);

        if (!$view instanceof ViewInterface) {
            return null;
        }

        $this->setDependenciesOnObject($view);

        return $view;
    }

    /**
     * @inheritDoc
     */
    public function createTable($name, $prefix = '', array $config = []): ?TableInterface
    {
        return $this->factory->createTable($name, $prefix, $config);
    }

    private function setDependenciesOnObject($object): void
    {
        if ($object instanceof ContainerAwareInterface) {
            $object->setContainer($this->getContainer());
        }

        if ($object !== null) {
            $reflection = new ReflectionClass($object);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (
                    str_starts_with($method->getName(), 'set')
                    && $method->getDeclaringClass()->getName() === $reflection->getName()
                ) {
                    $parameters = $method->getParameters();
                    if (count($parameters) == 1 && $parameters[0]->hasType()) {
                        $dependency = $parameters[0]->getType();
                        if ($dependency instanceof ReflectionNamedType) {
                            $class = $dependency->getName();
                            if ($this->getContainer()->has($class)) {
                                $object->{$method->getName()}($this->getContainer()->get($class));
                            }
                        }
                    }
                }
            }
        }
    }

    private function setMVCFactoryDecoratorOnController(ControllerInterface $controller): void
    {
        if ($controller instanceof MVCFactoryDecoratorAwareInterface) {
            $controller->setMVCFactoryDecorator($this);
        }
    }
}
