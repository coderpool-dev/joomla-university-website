<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
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

class Mod_JchsupportinfoInstallerScript extends InstallerScript
{
    protected $allowDowngrades = true;

    public function postflight(string $type)
    {
        if ($type == 'install' || $type == 'update') {
            $ids = $this->getInstances(true);

            $module = [
                'id' => $ids[0] ?? 0,
                'asset_id' => 0,
                'language' => '*',
                'note' => '',
                'published' => 1,
                'module' => $this->extension,
                'showtitle' => 1,
                'access' => 1,
                'client_id' => 1,
                'position' => 'cpanel-com-jchoptimize-cpanel',
                'title' => 'Support Info',
                'params' => [
                    'header_icon' => 'none fa fa-ticket',
                    'context' => 'automatic',
                    'module_tag' => 'div',
                    'bootstrap_size' => '12',
                    'header_tag' => 'h2',
                    'header_class' => '',
                    'style' => '0'
                ]
            ];

            $moduleModel = Factory::getApplication()->bootComponent('com_modules')->getMVCFactory()
                ->createModel('Module', 'Administrator', ['ignore_request' => true]);

            if (!$moduleModel->save($module)) {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf('MOD_JCHDASHICONS_INSTALL_ERROR', $moduleModel->getError())
                );
            }
        }
    }
}
