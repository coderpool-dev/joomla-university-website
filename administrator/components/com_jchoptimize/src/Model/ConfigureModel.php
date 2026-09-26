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

use Exception;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\Exception\ExceptionInterface;
use JchOptimize\Core\PageCache\CaptureCache;
use JchOptimize\Core\Platform\CacheInterface;
use JchOptimize\Core\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;

use function array_column;
use function array_intersect_key;
use function array_map;
use function array_merge;
use function array_values;
use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ConfigureModel extends BaseDatabaseModel implements ContainerAwareInterface
{
    use SaveSettingsTrait;
    use ContainerAwareTrait;

    /**
     * @var ModelInterface&TogglePluginsModel
     */
    private $togglePluginsModel;

    private CacheInterface $cacheUtils;

    private ?string $setting = null;

    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->togglePluginsModel = $this->getMVCFactory()->createModel('TogglePlugins');
    }

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }

    public function setCacheUtils(CacheInterface $cacheUtils): void
    {
        $this->cacheUtils = $cacheUtils;
    }

    /**
     * @throws ExceptionInterface
     */
    public function applyAutoSettings(string $autoSetting): void
    {
        $aAutoParams = Icons::autoSettingsArrayMap();

        $aSelectedSetting = array_column($aAutoParams, $autoSetting);
        /** @psalm-var array<string, string> $aSettingsToApply */
        $aSettingsToApply = array_combine(array_keys($aAutoParams), $aSelectedSetting);

        $params = $this->getState('params');

        foreach ($aSettingsToApply as $setting => $value) {
            $params->set($setting, $value);
        }

        $params->set('combine_files_enable', '1');
        $this->setState('params', $params);
        $this->saveSettings();
    }

    /**
     * @throws ExceptionInterface
     */
    public function toggleSetting(?string $setting): bool
    {
        $this->setting = $setting;

        if (is_null($this->setting)) {
            //@TODO some logging here
            return false;
        }

        if ($this->setting == 'integrated_page_cache_enable') {
            try {
                if (JCH_PRO) {
                    /** @var ModeSwitcherModel $modeSwitcher */
                    $modeSwitcher = $this->getMVCFactory()->createModel('ModeSwitcher');
                    $modeSwitcher->togglePageCacheState();
                    /** @see CaptureCache::updateHtaccess() */
                    $this->getContainer()->get(CaptureCache::class)->updateHtaccess();
                } else {
                    $this->togglePluginsModel->togglePageCacheState('jchpagecache');
                }
                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        $params = $this->getState('params');
        $iCurrentSetting = (int)$params->get($this->setting);
        $newSetting = (string)abs($iCurrentSetting - 1);

        if ($this->setting == 'pro_reduce_unused_css' && $newSetting == '1') {
            $params->set('optimizeCssDelivery_enable', '1');
        }

        if ($this->setting == 'optimizeCssDelivery_enable' && $newSetting == '0') {
            $params->set('pro_reduce_unused_css', '0');
        }

        $params->set($this->setting, $newSetting);
        $this->setState('params', $params);
        $this->saveSettings();

        return true;
    }

    public function getOutput(): array
    {
        $currentSettingValue = (string)$this->getState('params')->get($this->setting);

        if ($this->setting == 'integrated_page_cache_enable') {
            $currentSettingValue = $this->cacheUtils->isPageCacheEnabled($this->getState('params'));
        }

        $class = $currentSettingValue ? 'enabled' : 'disabled';
        $class2 = '';
        $auto = false;
        $pageCacheStatus = '';
        $statusClass = '';

        if ($this->setting == 'pro_reduce_unused_css') {
            $class2 = $this->getState('params')->get('optimizeCssDelivery_enable') ? 'enabled' : 'disabled';
        }

        if ($this->setting == 'optimizeCssDelivery_enable') {
            $class2 = $this->getState('params')->get('pro_reduce_unused_css') ? 'enabled' : 'disabled';
        }

        if ($this->setting == 'combine_files_enable' && $currentSettingValue) {
            $auto = $this->getEnabledAutoSetting();
        }

        if (JCH_PRO && $this->setting == 'integrated_page_cache_enable') {
            /** @var ModeSwitcherModel $modeSwitcher */
            $modeSwitcher = $this->getMVCFactory()->createModel('ModeSwitcher');
            [, , $pageCacheStatus, $statusClass] = $modeSwitcher->getIndicators();
            $pageCacheStatus = Text::sprintf('MOD_JCHMODESWITCHER_PAGECACHE_STATUS', $pageCacheStatus);
        }


        return [
            'class' => $class,
            'class2' => $class2,
            'auto' => $auto,
            'page_cache_status' => $pageCacheStatus,
            'status_class' => $statusClass,
            'enabled' => (bool)$currentSettingValue
        ];
    }

    /**
     * @return false|string
     */
    private function getEnabledAutoSetting(): bool|string
    {
        $autoSettingsMap = Icons::autoSettingsArrayMap();

        $autoSettingsInitialized = array_map(function ($a) {
            return '0';
        }, $autoSettingsMap);

        $currentAutoSettings = array_intersect_key($this->getState('params')->toArray(), $autoSettingsInitialized);
        //order array
        $orderedCurrentAutoSettings = array_merge($autoSettingsInitialized, $currentAutoSettings);

        $autoSettings = ['minimum', 'intermediate', 'average', 'deluxe', 'premium', 'optimum'];

        for ($j = 0; $j < 6; $j++) {
            if (array_values($orderedCurrentAutoSettings) === array_column($autoSettingsMap, 's' . ($j + 1))) {
                return $autoSettings[$j];
            }
        }

        //No auto setting configured
        return false;
    }
}
