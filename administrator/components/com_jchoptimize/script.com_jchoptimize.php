<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use _JchOptimizeVendor\V91\Psr\Container\ContainerExceptionInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use JchOptimize\Core\Admin\AdminTasks;
use JchOptimize\Core\Model\CacheMaintainer;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\DatabaseInterface;

// Protect from unauthorized access
defined('_JEXEC') or die();

class Com_JchoptimizeInstallerScript extends InstallerScript
{
    protected string $primaryKey = 'extension_id';

    protected $allowDowngrades = true;

    protected $deleteFolders = [
        '/administrator/components/com_jchoptimize/cache',
        '/administrator/components/com_jchoptimize/Controller',
        '/administrator/components/com_jchoptimize/Dispatcher',
        '/administrator/components/com_jchoptimize/fields',
        '/administrator/components/com_jchoptimize/Helper',
        '/administrator/components/com_jchoptimize/lib/tmpl',
        '/administrator/components/com_jchoptimize/lib/src/Core',
        '/administrator/components/com_jchoptimize/lib/src/Command',
        '/administrator/components/com_jchoptimize/lib/src/Controller',
        '/administrator/components/com_jchoptimize/lib/src/Crawlers',
        '/administrator/components/com_jchoptimize/lib/src/Helper',
        '/administrator/components/com_jchoptimize/lib/src/Joomla',
        '/administrator/components/com_jchoptimize/lib/src/Log',
        '/administrator/components/com_jchoptimize/lib/src/View',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/bus',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/container',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/events',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/filesystem',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/pipeline',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/support',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/view',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/contracts/Container',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/contracts/Events',
        '/administrator/components/com_jchoptimize/lib/vendor/illuminate/contracts/View',
        '/administrator/components/com_jchoptimize/lib/vendor/slim',
        '/administrator/components/com_jchoptimize/lib/vendor/laminas/laminas-cache-storage-adapter-wincache',
        '/administrator/components/com_jchoptimize/lib/vendor/nicmart/tree/src/Builder',
        '/administrator/components/com_jchoptimize/lib/vendor/nicmart/tree/src/Visitor',
        '/administrator/components/com_jchoptimize/Model',
        '/administrator/components/com_jchoptimize/Platform',
        '/administrator/components/com_jchoptimize/src/Joomla/Database',
        '/administrator/components/com_jchoptimize/src/View/ControlPanel',
        '/administrator/components/com_jchoptimize/sql',
        '/administrator/components/com_jchoptimize/Toolbar',
        '/administrator/components/com_jchoptimize/View',
        '/media/com_jchoptimize/filetree',
        '/media/com_jchoptimize/bootstrap',
        '/media/com_jchoptimize/core/css',
        '/media/com_jchoptimize/core/js',
        '/media/com_jchoptimize/icons',
        '/media/com_jchoptimize/jquery-ui'
    ];

    protected $deleteFiles = [
        '/administrator/components/com_jchoptimize/lib/src/ContainerFactory.php',
        '/administrator/components/com_jchoptimize/lib/src/Extension/MVCContainerFactory.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/ApiParams.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/BulkSettings.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/Cache.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/Configure.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/ModeSwitcher.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/OrderPlugins.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/PageCache.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/ReCache.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/ReCacheCliJ3.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/SaveSettingsTrait.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/TogglePlugins.php',
        '/administrator/components/com_jchoptimize/lib/src/Model/Updates.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/CachingConfigurationProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/CallbackProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/ConfigurationProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/CoreProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/DatabaseProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/FeatureHelpersProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/LoggerProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/ModeSwitcherProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/MvcProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/ReCacheProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/SharedEventProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/Service/SpatieProvider.php',
        '/administrator/components/com_jchoptimize/lib/src/ContainerFactory.php',
        '/administrator/components/com_jchoptimize/lib/src/ControllerResolver.php',
        '/administrator/components/com_jchoptimize/lib/src/GetApplicationTrait.php',
        '/administrator/components/com_jchoptimize/src/Controller/Ajax.php',
        '/administrator/components/com_jchoptimize/src/Controller/ApplyAutoSetting.php',
        '/administrator/components/com_jchoptimize/src/Controller/CacheInfo.php',
        '/administrator/components/com_jchoptimize/src/Controller/ControlPanel.php',
        '/administrator/components/com_jchoptimize/src/Controller/CriticalJsAutoSave.php',
        '/administrator/components/com_jchoptimize/src/Controller/CriticalJsTableBody.php',
        '/administrator/components/com_jchoptimize/src/Controller/ModeSwitcher.php',
        '/administrator/components/com_jchoptimize/src/Controller/OptimizeImage.php',
        '/administrator/components/com_jchoptimize/src/Controller/OptimizeImages.php',
        '/administrator/components/com_jchoptimize/src/Controller/PageCache.php',
        '/administrator/components/com_jchoptimize/src/Controller/ToggleSetting.php',
        '/administrator/components/com_jchoptimize/src/Controller/Utility.php',
        '/administrator/components/com_jchoptimize/src/ControllerResolver.php',
        '/administrator/components/com_jchoptimize/src/Crawlers/ReCacheCliJ3.php',
        '/administrator/components/com_jchoptimize/src/Extension/MVCContainerFactory.php',
        '/administrator/components/com_jchoptimize/src/Model/ApiParams.php',
        '/administrator/components/com_jchoptimize/src/Model/BulkSettings.php',
        '/administrator/components/com_jchoptimize/src/Model/Cache.php',
        '/administrator/components/com_jchoptimize/src/Model/Configure.php',
        '/administrator/components/com_jchoptimize/src/Model/ConfigureHelper.php',
        '/administrator/components/com_jchoptimize/src/Model/ModeSwitcher.php',
        '/administrator/components/com_jchoptimize/src/Model/OrderPlugins.php',
        '/administrator/components/com_jchoptimize/src/Model/PageCache.php',
        '/administrator/components/com_jchoptimize/src/Model/PopulateModalBody.php',
        '/administrator/components/com_jchoptimize/src/Model/ReCache.php',
        '/administrator/components/com_jchoptimize/src/Model/TogglePlugins.php',
        '/administrator/components/com_jchoptimize/src/Model/Updates.php',
        '/administrator/components/com_jchoptimize/src/Service/ConfigurationProvider.php',
        '/administrator/components/com_jchoptimize/src/Service/DatabaseProvider.php',
        '/administrator/components/com_jchoptimize/src/Service/LoggerProvider.php',
        '/administrator/components/com_jchoptimize/src/Service/ModeSwitcherProvider.php',
        '/administrator/components/com_jchoptimize/src/Service/MVCContainerFactory.php',
        '/administrator/components/com_jchoptimize/src/Service/MvcProvider.php',
        '/administrator/components/com_jchoptimize/src/Service/PlatformProvider.php',
        '/administrator/components/com_jchoptimize/src/Service/PlatformUtilsProvider.php',
        '/administrator/components/com_jchoptimize/src/Service/Provider/MVCContainerFactory.php',
        '/administrator/components/com_jchoptimize/src/Service/ReCacheProvider.php',
        '/administrator/components/com_jchoptimize/src/View/ControlPanelHtml.php',
        '/administrator/components/com_jchoptimize/src/View/OptimizeImagesHtml.php',
        '/administrator/components/com_jchoptimize/src/View/PageCacheHtml.php',
        '/administrator/components/com_jchoptimize/src/GetApplicationTrait.php',
        '/administrator/components/com_jchoptimize/tmpl/control_panel.php',
        '/administrator/components/com_jchoptimize/tmpl/control_panel_bulk_settings.php',
        '/administrator/components/com_jchoptimize/tmpl/critical_js_table_body.php',
        '/administrator/components/com_jchoptimize/tmpl/navigation.php',
        '/administrator/components/com_jchoptimize/tmpl/optimize_images.php',
        '/administrator/components/com_jchoptimize/tmpl/page_cache.php',
        '/administrator/components/com_jchoptimize/tmpl/page_cache_filters.php',
        '/administrator/components/com_jchoptimize/tmpl/page_cache_norecords.php',
        '/administrator/components/com_jchoptimize/tmpl/page_cache_table_footer.php',
        '/administrator/components/com_jchoptimize/tmpl/page_cache_table_header.php',
        '/administrator/components/com_jchoptimize/tmpl/page_cache_withrecords.php',
        '/media/com_jchoptimize/js/core/dynamic_css_elements.js',
        '/media/com_jchoptimize/js/core/ls.loader.effects.js',
        '/media/com_jchoptimize/js/core/ls.loader.js',
        '/media/com_jchoptimize/js/core/num_elements.js',
        '/media/com_jchoptimize/js/core/reduce_dom.js',
        '/media/com_jchoptimize/js/core/reduce_unused_css.js',
        '/media/com_jchoptimize/js/core/reduce_unused_js.js',
        '/media/com_jchoptimize/js/core/resize-sensor.js',
        '/media/com_jchoptimize/js/core/smart-combine.js',
        '/media/com_jchoptimize/js/core/user-interact-callback.js',
    ];

    /**
     * Runs after install, update or discover_update
     *
     * @param string $type install, update or discover_update
     * @param ComponentAdapter $parent
     *
     * @return void
     */
    public function postflight(string $type, $parent): void
    {
        if ($type == 'uninstall') {
            return;
        }

        if (!$this->isModuleInDashboard()) {
            try {
                $this->addDashboardMenu('com-jchoptimize-cpanel', 'jchoptimize');
            } catch (Exception $e) {
            }
        }

        if ($type == 'update') {
            $tmpComponentFolder = $parent->getParent()->getPath('source');
            $this->autoloadNewFiles($tmpComponentFolder);
            $this->removeFiles();
        }
    }

    public function uninstall(): void
    {
        $this->removeDashboardSubmenu();

        try {
            //Boot the component to register autoload files
            $component = Factory::getApplication()->bootComponent('com_jchoptimize');
            if ($component instanceof JchOptimizeComponent) {
                $container = $component->getContainer();
                $container->get(CacheMaintainer::class)->cleanCache();
                $container->get(AdminTasks::class)->cleanHtaccess();
            }
        } catch (Exception | ContainerExceptionInterface $e) {
        }
    }

    /**
     * Gets parameter value in the extensions row of the extension table
     *
     * @param string $name The name of the parameter to be retrieved
     * @param int $id The id of the item in the Param Table
     *
     * @return  string  The parameter desired
     *
     * @since   3.6
     */
    public function getParam($name, $id = 0)
    {
        if (!\is_int($id) || $id == 0) {
            // Return false if there is no item given
            return false;
        }

        $params = $this->getItemArray('params', $this->paramTable, $this->primaryKey, $id);

        return $params[$name];
    }

    /**
     * Sets parameter values in the extensions row of the extension table. Note that the
     * this must be called separately for deleting and editing. Note if edit is called as a
     * type then if the param doesn't exist it will be created
     *
     * @param array $paramArray The array of parameters to be added/edited/removed
     * @param string $type The type of change to be made to the param (edit/remove)
     * @param int $id The id of the item in the relevant table
     *
     * @return  bool  True on success
     *
     * @since   3.6
     */
    public function setParams($paramArray = null, $type = 'edit', $id = 0)
    {
        if (!\is_int($id) || $id == 0) {
            // Return false if there is no valid item given
            return false;
        }

        $params = $this->getItemArray('params', $this->paramTable, $this->primaryKey, $id);

        if ($paramArray) {
            foreach ($paramArray as $name => $value) {
                if ($type === 'edit') {
                    // Add or edit the new variable(s) to the existing params
                    if (\is_array($value)) {
                        // Convert an array into a json encoded string
                        $params[(string)$name] = array_values($value);
                    } else {
                        $params[(string)$name] = (string)$value;
                    }
                } elseif ($type === 'remove') {
                    // Unset the parameter from the array
                    unset($params[(string)$name]);
                }
            }
        }

        // Store the combined new and existing values back as a JSON string
        $paramsString = json_encode($params);

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName($this->paramTable))
            ->set($db->quoteName('params') . ' = ' . $db->quote($paramsString))
            ->where($db->quoteName($this->primaryKey) . ' = ' . $db->quote($id));

        // Update table
        $db->setQuery($query)->execute();

        return true;
    }

    /**
     * Gets each instance of a module in the #__modules table or extension in the #__extensions table
     *
     * @param bool $isModule True if the extension is a module as this can have multiple instances
     * @param string $extension Name of extension to find instance of
     *
     * @return  array  An array of ID's of the extension
     *
     * @since   3.6
     */
    public function getInstances($isModule, $extension = null)
    {
        $extension = $extension ?? $this->extension;

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        // Select the item(s) and retrieve the id
        if ($isModule) {
            $query->select($db->quoteName('id'));
            $query->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' = ' . $db->quote($extension));
        } else {
            $query->select($db->quoteName('extension_id'));
            $query->from($db->quoteName('#__extensions'));
            //Special handling for plugins, we extract the element and folder from the extension name
            $parts = explode('_', $extension, 3);

            if (count($parts) == 3 && $parts[0] == 'plg') {
                $extension = $parts[2];
                $folder = $parts[1];

                $query->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
            }

            $query->where($db->quoteName('element') . ' = ' . $db->quote($extension));
        }

        // Set the query and obtain an array of id's
        return $db->setQuery($query)->loadColumn();
    }

    private function autoloadNewFiles($tmpCompFolder): void
    {
        $dir = $tmpCompFolder . '/backend';

        $vendorClassMapPath = $dir . '/lib/vendor/composer/autoload_classmap.php';
        $vendorClassMap = include($vendorClassMapPath);

        $loader = include JPATH_LIBRARIES . '/vendor/autoload.php';
        $loader->addClassMap($vendorClassMap);

        include_once($dir . '/class_map.php');
    }

    private function isModuleInDashboard(): bool
    {
        $db       = Factory::getContainer()->get(DatabaseInterface::class);
        $query    = $db->getQuery(true)
                   ->select('COUNT(*)')
                   ->from($db->quoteName('#__modules'))
                   ->where([
                       $db->quoteName('module') . ' = ' . $db->quote('mod_submenu'),
                       $db->quoteName('client_id') . ' = ' . $db->quote(1),
                       $db->quoteName('position') . ' = ' . $db->quote('cpanel-com-jchoptimize-cpanel'),
                   ]);

        $modules = $db->setQuery($query)->loadResult() ?: 0;

        return $modules > 0;
    }

    private function removeDashboardSubmenu(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__modules'))
                ->where([
                    $db->quoteName('module') . ' = ' . $db->quote('mod_submenu'),
                    $db->quoteName('client_id') . ' = ' . $db->quote(1),
                    $db->quoteName('position') . ' = ' . $db->quote('cpanel-com-jchoptimize-cpanel'),
                ]);
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
        }
    }
}
