<?php

namespace CodeAlfa\Component\JchOptimize\Administrator\Service\Html;

use Joomla\CMS\Layout\FileLayout;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class DashIcons
{
    public function buttons(array $buttons): string
    {
        if (empty($buttons)) {
            return '';
        }

        $html = '';

        foreach ($buttons as $button) {
            $html .= $this->button($button);
        }

        return $html;
    }

    public function button(array $button): string
    {
        $layout = new FileLayout('dashicons.icon');
        $layout->setComponent('com_jchoptimize');

        return $layout->render($button);
    }
}
