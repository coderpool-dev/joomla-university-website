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

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use CodeAlfa\Component\JchOptimize\Administrator\Helper\CacheCleaner;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use Exception;
use JchOptimize\Core\Admin\AdminTasks;
use JchOptimize\Core\Model\CacheMaintainer;
use JchOptimize\Core\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ModelInterface;

use function array_diff;
use function array_keys;
use function defined;
use function is_null;

use const JPATH_ADMINISTRATOR;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ModeSwitcherModel extends BaseDatabaseModel
{
    /**
     * @var ModelInterface&TogglePluginsModel
     */
    private $togglePluginsModel;

    private AdminTasks $tasks;

    private Registry $params;

    private CacheMaintainer $cacheMaintainer;

    /**
     * @psalm-var array<string, string>
     */
    public array $pageCachePlugins = [
        'jchpagecache' => 'COM_JCHOPTIMIZE_SYSTEM_PAGE_CACHE',
        'cache' => 'COM_JCHOPTIMIZE_JOOMLA_SYSTEM_CACHE',
        'lscache' => 'COM_JCHOPTIMIZE_LITESPEED_CACHE',
        'pagecacheextended' => 'COM_JCHOPTIMIZE_PAGE_CACHE_EXTENDED'
    ];


    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->togglePluginsModel = $this->getMVCFactory()->createModel('TogglePlugins');
    }

    public function setTasks(AdminTasks $tasks): void
    {
        $this->tasks = $tasks;
    }

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }

    public function setCacheMaintainer(CacheMaintainer $cacheMaintainer): void
    {
        $this->cacheMaintainer = $cacheMaintainer;
    }

    public function setProduction(): void
    {
        $this->setPluginState('jchoptimize', '1');
        CacheCleaner::clearPluginsCache();
        PluginHelper::reload();

        if ($this->params->get('pro_page_cache_integration_enable', '0')) {
            $this->togglePageCacheState(1);
        }

        $this->tasks->generateNewCacheKey();
    }

    /**
     * @param null|string $state
     *
     * @return bool
     */
    public function togglePageCacheState(?string $state = null): bool
    {
        $integratedPlugin = $this->getIntegratedPageCachePlugin();

        if ($integratedPlugin == 'jchoptimizepagecache') {
            $integratedPlugin = 'jchpagecache';
        }
        //If state was not set then we toggle the existing state
        if (is_null($state)) {
            $state = PluginHelper::isEnabled('system', $integratedPlugin) ? 0 : 1;
        }

        if ($state == 1) {
            //disable other plugins
            $pluginsToDisable = array_diff(array_keys($this->pageCachePlugins), [$integratedPlugin]);

            foreach ($pluginsToDisable as $plugin) {
                $this->setPluginState($plugin, 0);
            }
        } else {
            //Disable all page_cache_plugins
            foreach ($this->pageCachePlugins as $plugin => $title) {
                $this->setPluginState($plugin, 0);
            }
        }

        return $this->togglePluginsModel->togglePageCacheState($integratedPlugin, $state);
    }

    protected function setPluginState(string $element, int $state): bool
    {
        return $this->togglePluginsModel->setPluginState($element, $state);
    }

    public function setDevelopment(): void
    {
        $this->setPluginState('jchoptimize', '0');
        CacheCleaner::clearPluginsCache();
        PluginHelper::reload();

        if ($this->params->get('pro_page_cache_integration_enable', '0')) {
            $this->togglePageCacheState(0);
        }

        $this->cacheMaintainer->cleanCache();
    }

    public function getIntegratedPageCachePlugin(): string
    {
        /** @var string */
        return $this->params->get('pro_page_cache_integration', 'jchpagecache');
    }

    /**
     * @return (string)[]
     *
     * @psalm-return array<int<0, max>, array-key>
     */
    public function getAvailablePageCachePlugins(): array
    {
        $db = $this->getDatabase();

        try {
            $query = $db->getQuery(true)
                ->select('element')
                ->from('#__extensions')
                ->where($db->quoteName('type') . '=' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . '=' . $db->quote('system'));
            $db->setQuery($query);
            /** @psalm-var array<array-key, string> $plugins */
            $plugins = $db->loadColumn();
        } catch (Exception) {
            $plugins = ['jchpagecache'];
        }

        return array_intersect(array_keys($this->pageCachePlugins), $plugins);
    }

    public function getIndicators(): array
    {
        $app = Factory::getApplication();
        $lang = $app->getLanguage();
        $lang->load('mod_jchmodeswitcher', JPATH_ADMINISTRATOR);

        if (PluginHelper::isEnabled('system', 'jchoptimize')) {
            $mode = Text::_('MOD_JCHMODESWITCHER_PRODUCTION');
            $task = 'setDevelopment';
            $statusClass = 'production';
        } else {
            $mode = Text::_('MOD_JCHMODESWITCHER_DEVELOPMENT');
            $task = 'setProduction';
            $statusClass = 'development';
        }

        $pageCachePlugin = $this->getIntegratedPageCachePlugin();

        if ($pageCachePlugin == 'jchoptimizepagecache') {
            $pageCachePlugin = 'jchpagecache';
        }

        if (PluginHelper::isEnabled('system', $pageCachePlugin)) {
            $pageCacheStatus = Text::_('MOD_JCHMODESWITCHER_PAGECACHE_ENABLED');

            if ($statusClass == 'development') {
                $statusClass = 'page-cache-only';
            }
        } else {
            $pageCacheStatus = Text::_('MOD_JCHMODESWITCHER_PAGECACHE_DISABLED');

            if ($statusClass == 'production') {
                $statusClass = 'page-cache-disabled';
            }
        }

        return [$mode, $task, $pageCacheStatus, $statusClass];
    }
}
