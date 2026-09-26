<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2024 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Module\JchDashIcons\Administrator\Helper;

use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use CodeAlfa\Component\JchOptimize\Administrator\Model\ModeSwitcherModel;
use CodeAlfa\Component\JchOptimize\Administrator\Model\UpdatesModel;
use Exception;
use JchOptimize\Core\Admin\Icons;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

use function defined;

use const JCH_PRO;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class JchDashIconsHelper
{
    public function getButtons(Registry $params, Icons $icons, MVCFactoryInterface $mvcFactory): array
    {
        $context = (string)$params->get('context', 'automatic_settings');

        switch ($context) {
            case 'automatic':
            default:
                $buttons = array_merge(
                    $icons->compileAutoSettingsIcons(
                        Icons::getAutoSettingsArray()
                    ),
                    $icons->compileToggleFeaturesIcons(
                        $icons->getCombineFilesEnableSetting()
                    )
                );
                break;
            case 'utility':
                $buttons = $icons->compileUtilityIcons(
                    $icons->getUtilityArray([
                        'browsercaching',
                        'orderplugins',
                        'keycache',
                        'recache',
                        'bulksettings',
                        'cleancache'
                    ])
                );
                break;
            case 'basic':
                $buttons = $icons->compileToggleFeaturesIcons(
                    $icons->getToggleSettings()
                );
                break;
            case 'images':
                $buttons = $icons->compileToggleFeaturesIcons(
                    $icons->getToggleSettings('images')
                );
                break;
            case 'css':
                $buttons = $icons->compileToggleFeaturesIcons(
                    $icons->getToggleSettings('css')
                );
                break;
            case 'advanced':
                $buttons = $icons->compileToggleFeaturesIcons(
                    $icons->getAdvancedToggleSettings()
                );

                break;
            case 'notifications':
                $buttons = $this->compileNotificationsIcons($mvcFactory);
                break;
        }

        return $buttons;
    }

    private function compileNotificationsIcons(MVCFactoryInterface $mvcFactory): array
    {
        $buttons = [];

        $buttons[0]['link'] = Route::_(
            'index.php?option=com_plugins&filter[element]=jchoptimize&filter[folder]=system'
        );
        $buttons[0]['icon'] = 'fa fa-plug';
        $buttons[0]['id'] = 'plugin-status';

        if (PluginHelper::isEnabled('system', 'jchoptimize')) {
            $buttons[0]['class'] = ['success'];
            $buttons[0]['name'] = Text::_('MOD_JCHDASHICONS_JCHOPTIMIZE_PLUGIN_ENABLED');
        } else {
            $buttons[0]['class'] = ['danger'];
            $buttons[0]['name'] = Text::_('MOD_JCHDASHICONS_JCHOPTIMIZE_PLUGIN_DISABLED');
        }

        if (JCH_PRO) {
            try {
                $buttons[1]['id'] = 'download-id-status';
                $buttons[1]['link'] = Route::_(
                    'index.php?option=com_installer&view=updatesites&'
                    . 'filter[search]=JCH Optimize&filter[supported]=1'
                );
                $buttons[1]['icon'] = 'fa fa-id-badge';
                /** @var UpdatesModel $updatesModel */
                $updatesModel = $mvcFactory->createModel('Updates', 'Administrator');
                if ($updatesModel->getLicenseKey() == '') {
                    $buttons[1]['class'] = ['danger'];
                    $buttons[1]['name'] = Text::_('MOD_JCHDASHICONS_DOWNLOAD_ID_MISSING');
                } else {
                    $buttons[1]['class'] = ['success'];
                    $buttons[1]['name'] = Text::_('MOD_JCHDASHICONS_DOWNLOAD_ID_ENTERED');
                }
            } catch (Exception $e) {
            }
        }

        /** @var ModeSwitcherModel $modeSwitcher */
        $modeSwitcher = $mvcFactory->createModel('ModeSwitcher', 'Administrator');
        $pageCache = $modeSwitcher->getIntegratedPageCachePlugin();

        $buttons[2]['link'] = Route::_(
            "index.php?option=com_plugins&filter[element]=$pageCache&filter[folder]=system"
        );
        $buttons[2]['icon'] = 'fa fa-archive';
        $buttons[2]['id'] = 'page-cache-status';

        if (PluginHelper::isEnabled('system', $pageCache)) {
            $buttons[2]['class'] = ['success'];
            $buttons[2]['name'] = Text::_('MOD_JCHDASHICONS_PAGE_CACHE_ENABLED');
        } else {
            $buttons[2]['class'] = ['danger'];
            $buttons[2]['name'] = Text::_('MOD_JCHDASHICONS_PAGE_CACHE_DISABLED');
        }

        return $buttons;
    }
}
