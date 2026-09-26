<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Codealfa\Module\JchModeSwitcher\Administrator\Dispatcher;

use CodeAlfa\Component\JchOptimize\Administrator\Model\ModeSwitcherModel;
use Exception;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Language\Text;
use Throwable;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class Dispatcher extends AbstractModuleDispatcher
{
    protected function getLayoutData(): bool|array
    {
        $data = parent::getLayoutData();

        $pageCachePlugins = [
            'jchpagecache' => 'MOD_JCHMODESWITCHER_JCHOPTIMIZE_SYSTEM_PAGE_CACHE',
            /* Added for backward compatibility */
            'jchoptimizepagecache' => 'MOD_JCHMODESWITCHER_JCHOPTIMIZE_SYSTEM_PAGE_CACHE',
            'cache' => 'MOD_JCHMODESWITCHER_JOOMLA_SYSTEM_CACHE',
            'lscache' => 'MOD_JCHMODESWITCHER_LITESPEED_CACHE',
            'pagecacheextended' => 'MOD_JCHMODESWITCHER_PAGE_CACHE_EXTENDED'
        ];

        try {
            /** @var ModeSwitcherModel $modeSwitcher */
            $modeSwitcher = $data['app']->bootComponent('com_jchoptimize')->getMVCFactory()->createModel(
                'ModeSwitcher',
                'Administrator'
            );

            if (!$modeSwitcher instanceof ModeSwitcherModel) {
                throw new Exception();
            }

            $integratedPageCache = $modeSwitcher->getIntegratedPageCachePlugin();
            $pageCachePluginTitle = Text::_($pageCachePlugins[$integratedPageCache]);
            [$mode, $task, $pageCacheStatus, $statusClass] = $modeSwitcher->getIndicators();
        } catch (Throwable) {
            return false;
        }

        $data['pageCachePluginTitle'] = $pageCachePluginTitle;
        $data['mode'] = $mode;
        $data['task'] = $task;
        $data['pageCacheStatus'] = $pageCacheStatus;
        $data['statusClass'] = $statusClass;

        return $data;
    }
}
