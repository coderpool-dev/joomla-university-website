<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\Component\Modules\Administrator\Model\ModuleModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

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
class Mod_JchdashiconsInstallerScript extends InstallerScript
{
    protected $allowDowngrades = true;

    protected ?ModuleModel $moduleModel = null;

    public function postflight(string $type)
    {
        if ($type == 'uninstall') {
            return true;
        }
        if ($type == 'install' || $type == 'update') {
            $ids = $this->getInstances(true);
            $ids = array_pad($ids, count($this->modules()), 0);

            $module = [
                'asset_id' => 0,
                'language' => '*',
                'note' => '',
                'published' => 1,
                'module' => $this->extension,
                'showtitle' => 1,
                'access' => 1,
                'client_id' => 1,
                'position' => 'cpanel-com-jchoptimize-cpanel'
            ];

            $moduleModel = Factory::getApplication()
                ->bootComponent('com_modules')
                ->getMVCFactory()
                ->createModel('Module', 'Administrator', ['ignore_request' => true]);

            foreach ($this->modules() as $i => $dashIconModule) {
                $module['id'] = $ids[$i];
                $module['title'] = $dashIconModule['title'];
                $module['params'] = $dashIconModule['params'];

                if (!$moduleModel->save($module)) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf('MOD_JCHDASHICONS_INSTALL_ERROR', $moduleModel->getError())
                    );
                }
            }
        }
    }

    private function modules(): array
    {
        return [
             [
                'title' => 'Optimize Files',
                'params' => [
                    'header_icon' => 'none fa fa-file-download',
                    'context' => 'automatic',
                    'module_tag' => 'div',
                    'bootstrap_size' => '12',
                    'header_tag' => 'h2',
                    'header_class' => '',
                    'style' => '0'
                ]
             ],
             [
                'title' => 'Notifications',
                'params' => [
                    'header_icon' => 'none fa fa-exclamation-circle',
                    'context' => 'notifications',
                    'module_tag' => 'div',
                    'bootstrap_size' => '0',
                    'header_tag' => 'h2',
                    'header_class' => '',
                    'style' => '0'
                ]
             ],
             [
                'title' => 'Image/CDN Features',
                'params' => [
                    'header_icon' => 'none fa fa-image',
                    'context' => 'images',
                    'module_tag' => 'div',
                    'bootstrap_size' => '12',
                    'header_tag' => 'h2',
                    'header_class' => '',
                    'style' => '0'
                ]
             ],
             [
                'title' => 'Utility Tasks',
                'params' => [
                    'header_icon' => 'none fa fa-tasks',
                    'context' => 'utility',
                    'module_tag' => 'div',
                    'bootstrap_size' => '0',
                    'header_tag' => 'h2',
                    'header_class' => '',
                    'style' => '0'
                ]
             ],
             [
                'title' => 'CSS Features',
                'params' => [
                    'header_icon' => 'none fa fa-css3',
                    'context' => 'css',
                    'module_tag' => 'div',
                    'bootstrap_size' => '0',
                    'header_tag' => 'h2',
                    'header_class' => '',
                    'style' => '0'
                ]
             ],
             [
                'title' => 'Advanced Features',
                'params' => [
                    'header_icon' => 'none fa fa-users-cog',
                    'context' => 'advanced',
                    'module_tag' => 'div',
                    'bootstrap_size' => '0',
                    'header_tag' => 'h2',
                    'header_class' => '',
                    'style' => '0'
                ]
             ]
        ];
    }
}
