<?php

namespace CodeAlfa\Component\JchOptimize\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class Dispatcher extends ComponentDispatcher
{
    protected string $defaultController = 'ControlPanelTabDisplay';

    public function dispatch(): void
    {
        $this->setDefaultController();

        parent::dispatch();
    }

    private function setDefaultController(): void
    {
        $controller = $this->input->getCmd('controller');
        $view = $this->input->getCmd('view');

        if (empty($controller)) {
            if (!empty($view)) {
                $controller = $this->getControllerFromView($view);
            } else {
                $controller = $this->defaultController;
            }

            $this->input->set('controller', $controller);
        }
    }

    private function getControllerFromView(string $view): string
    {
        if (in_array($view, ['ControlPanel', 'OptimizeImages', 'PageCache'])) {
            return $view . 'TabDisplay';
        }

        return $view;
    }
}
