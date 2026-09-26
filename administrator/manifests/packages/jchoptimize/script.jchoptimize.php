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
use _JchOptimizeVendor\V91\Psr\Container\ContainerInterface;
use _JchOptimizeVendor\V91\Psr\Container\NotFoundExceptionInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Container\ContainerFactory;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use JchOptimize\Core\Admin\AdminHelper;
use JchOptimize\Core\Admin\AdminTasks;
use JchOptimize\Core\Model\CacheMaintainer;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Modules\Administrator\Model\ModulesModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');

// phpcs:enable PSR1.Files.SideEffects

class Pkg_JchoptimizeInstallerScript extends InstallerScript
{
    /**
     * The primary field of the paramTable
     *
     * @var string
     */
    protected $primaryKey = 'extension_id';
    /**
     * The minimum PHP version required to install this extension
     *
     * @var   string
     */
    protected $minimumPhp = '8.0';

    /**
     * The minimum Joomla! version required to install this extension
     *
     * @var   string
     */
    protected $minimumJoomla = '4.4';

    /**
     * The maximum Joomla! version this extension can be installed on
     *
     * @var   string
     */
    protected $allowDowngrades = true;


    /**
     * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
     * type (plugin, module, ...), name (of the extension), client (0=site, 1=admin), group (for plugins).
     *
     * @var array
     */
    protected $extensionsToEnable = [
        'plg_system_jchoptimize',
        'plg_console_jchoptimize',
        'plg_user_jchuserstate',
        'mod_jchmodeswitcher',
        'mod_dashicons'
    ];

    /**
     * Joomla! pre-flight event. This runs before Joomla! installs or updates the package. This is our last chance to
     * tell Joomla! if it should abort the installation.
     *
     * In here we'll try to install FOF. We have to do that before installing the component since it's using an
     * installation script extending FOF's InstallScript class. We can't use a <file> tag in the manifest to install FOF
     * since the FOF installation is expected to fail if a newer version of FOF is already installed on the site.
     *
     * @param string $type Installation type (install, update, discover_install)
     * @param PackageAdapter $parent Parent object
     *
     * @return  boolean  True to let the installation proceed, false to halt the installation
     */
    public function preflight($type, $parent): bool
    {
        if (!parent::preflight($type, $parent)) {
            return false;
        }

        if ($type === 'uninstall') {
            return true;
        }

        $this->dontInstallFreeOnPro($parent);

        return true;
    }

    private function dontInstallFreeOnPro($parent): void
    {
        $manifest = $parent->getManifest();
        $newVariant = (string)$manifest->variant;

        $files = [];
        $files[] = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_jchoptimize.xml';
        $files[] = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_jch_optimize.xml';

        foreach ($files as $file) {
            if (file_exists($file)) {
                $xml = simplexml_load_file($file);
                $oldVariant = (string)$xml->variant;

                if ($oldVariant == 'PRO' && $newVariant == 'FREE') {
                    $msg = '<p>You are trying to install the FREE version of JCH Optimize, but you currently have the PRO version installed. You must uninstall the PRO version first before you can install the FREE version.</p>';
                    Log::add($msg, Log::WARNING, 'jerror');

                    return;
                }

                break;
            }
        }
    }

    /**
     * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
     * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
     * database updates and similar housekeeping functions.
     *
     * @param string $type install, update or discover_update
     * @throws Exception
     */
    public function postflight(string $type)
    {
        if ($type == 'uninstall') {
            return;
        }

        //Remove old plugins before trying to load new files
        if ($type == 'update') {
            $this->removeObsoletePlugins();
        }

        try {
            $this->orderDashboardModules();
            $this->invalidateFiles();
            $this->forceLoadExtensionNamespaceMap();
            $component = Factory::getApplication()->bootComponent('com_jchoptimize');
            if ($component instanceof JchOptimizeComponent) {
                if (!class_exists('\JchOptimize\Container\ContainerFactory', false)) {
                    class_alias(ContainerFactory::class, '\\JchOptimize\\Container\\ContainerFactory');
                }

                $factory = $component->getMVCFactory();
                $container = $component->getContainer();

                if ($type == 'update') {
                    $this->cleanCache($container);
                    $this->fixMetaFileSecurityIssue($container);
                }

                $this->leverageBrowserCaching($container);
                $this->orderPlugins($factory);
                $this->writeNginxInclude();
            }
        } catch (Exception $e) {
        }
    }

    private function cleanCache(ContainerInterface $container): void
    {
        try {
            $container->get(CacheMaintainer::class)->cleanCache();
            $staticCacheFolder = JPATH_ROOT . '/media/com_jchoptimize/cache';

            if (file_exists($staticCacheFolder)) {
                Folder::delete($staticCacheFolder);
            }
        } catch (Throwable $e) {
            //Don't cry
        }
    }

    private function orderPlugins(MVCFactoryInterface $factory): void
    {
        try {
            $factory->createModel('OrderPlugins', 'Administrator')
                ->orderPlugins();
        } catch (Exception) {
            //It's ok
        }
    }


    private function removeObsoletePlugins(): void
    {
        $plugins = [];
        $plugins = array_merge($plugins, $this->getInstances(false, 'plg_system_jchoptimizepagecache'));
        $plugins = array_merge($plugins, $this->getInstances(false, 'plg_user_jchoptimizeuserstate'));

        if (empty($plugins)) {
            return;
        }

        $installer = new Installer();
        foreach ($plugins as $plugin) {
            try {
                $installer->uninstall('plugin', (int)$plugin);
            } catch (Exception $e) {
                $msg = "<p>We weren't able to uninstall the obsolete plugin."
                    . " You'll need to do that from the Extensions Manager.</p>";
                Log::add($msg, Log::WARNING, 'jerror');
            }
        }
    }

    /**
     * Returns the update site IDs for the specified Joomla Extension ID.
     *
     * @param int $eid Extension ID for which to retrieve update sites
     *
     * @return  array  The IDs of the update sites
     */
    private function getUpdateSitesFor($eid = null)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->qn('s.update_site_id'))
            ->from($db->qn('#__update_sites', 's'))
            ->innerJoin(
                $db->qn('#__update_sites_extensions', 'e') . 'ON(' . $db->qn('e.update_site_id') .
                ' = ' . $db->qn('s.update_site_id') . ')'
            )
            ->where($db->qn('e.extension_id') . ' = ' . $db->q($eid));

        try {
            $ret = $db->setQuery($query)->loadColumn();
        } catch (Exception $e) {
            return [];
        }

        return empty($ret) ? [] : $ret;
    }

    /**
     * Runs on installation (but not on upgrade). This happens in install and discover_install installation routes.
     *
     *
     * @return  bool
     */
    public function install(): bool
    {
        //Enable the extensions we need to install
        $this->enableExtensions();

        return true;
    }

    public function uninstall(): void
    {
        Folder::delete(JPATH_ROOT . '/images/jch-optimize');
        Folder::delete(JPATH_ROOT . '/jchoptimizecapturecache');
        Folder::delete(JPATH_ROOT . '/.jch');
    }

    /**
     * Enable modules and plugins after installing them
     */
    private function enableExtensions(): void
    {
        foreach ($this->extensionsToEnable as $ext) {
            $this->enableExtension($ext);
        }
    }

    /**
     * Enable an extension
     *
     * @param null $extension
     */
    private function enableExtension($extension = null): void
    {
        $extension = $extension ?? $this->extension;

        $ids = $this->getInstances(false, $extension);

        if (empty($ids)) {
            return;
        }

        $id = (int)$ids[0];

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->update('#__extensions')
                ->set($db->quoteName('enabled') . ' = ' . $db->quote(1))
                ->where($db->quoteName('extension_id') . ' = ' . $db->quote($id));
            $db->setQuery($query)->execute();
        } catch (\Exception $e) {
        }
    }

    /**
     * Gets each instance of a module in the #__modules table or extension in the #__extensions table
     *
     * @param boolean $isModule True if the extension is a module as this can have multiple instances
     * @param string $extension Name of extension to find instance of
     *
     * @return  array  An array of ID's of the extension
     *
     * @since   3.6
     */
    public function getInstances($isModule, $extension = null): array
    {
        $extension = $extension ?? $this->extension;

        $db = Factory::getDbo();
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


    /**
     * Sets parameter values in the extensions row of the extension table. Note that the
     * this must be called separately for deleting and editing. Note if edit is called as a
     * type then if the param doesn't exist it will be created
     *
     * @param array $paramArray The array of parameters to be added/edited/removed
     * @param string $type The type of change to be made to the param (edit/remove)
     * @param integer $id The id of the item in the relevant table
     *
     * @return  boolean  True on success
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
     * The full paths to optimized files were added to the metafile, that was available from the internet. this
     * function corrects that.
     *
     * @return void
     */
    private function fixMetaFileSecurityIssue(ContainerInterface $container): void
    {
        try {
            /** @var AdminHelper $adminHelper */
            $adminHelper = $container->get(AdminHelper::class);
            $metaFile = $adminHelper->getMetaFile();
            $metaFileDir = dirname($metaFile);
            if (
                file_exists($metaFile)
                && (!file_exists($metaFileDir . '/index.html')
                    || !file_exists($metaFileDir . '/.htaccess'))
            ) {
                /** @var string[] $optimizedFiles */
                $optimizedFiles = $adminHelper->getOptimizedFiles();
                File::delete($metaFile);

                foreach ($optimizedFiles as $files) {
                    $adminHelper->markOptimized($files);
                }
            }
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
    }

    private function leverageBrowserCaching(ContainerInterface $container): void
    {
        try {
            $container->get(AdminTasks::class)->leverageBrowserCaching();
        } catch (Exception|ContainerExceptionInterface $e) {
        }
    }

    private function forceLoadExtensionNamespaceMap(): void
    {
        $namespaceMap = [
            'CodeAlfa\\Component\\JchOptimize\\Administrator\\' => [JPATH_ADMINISTRATOR . '/components/com_jchoptimize/src'],
            'CodeAlfa\\Module\\JchDashIcons\\Administrator\\' => [JPATH_ADMINISTRATOR . '/modules/mod_jchdashicons/src'],
            'CodeAlfa\\Module\\JchModeSwitcher\\Administrator\\' => [JPATH_ADMINISTRATOR . '/modules/mod_jchmodeswitcher/src'],
            'CodeAlfa\\Plugin\\Console\\JchOptimize\\' => [JPATH_PLUGINS . '/console/jchoptimize/src'],
            'CodeAlfa\\Plugin\\Content\\JchControl\\' => [JPATH_PLUGINS . '/content/jchcontrol/src'],
            'CodeAlfa\\Plugin\\System\\JchOptimize\\' => [JPATH_PLUGINS . '/system/jchoptimize/src'],
            'CodeAlfa\\Plugin\\System\\JchPageCache\\' => [JPATH_PLUGINS . '/system/jchpagecache/src'],
            'CodeAlfa\\Plugin\\User\\JchUserState\\' => [JPATH_PLUGINS . '/user/jchuserstate/src'],
        ];

        $loader = include JPATH_LIBRARIES . '/vendor/autoload.php';

        foreach ($namespaceMap as $namespace => $paths) {
            $loader->setPsr4($namespace, $paths);
        }
    }

    /**
     * 'Borrowed' from akeeba.com
     */
    private function invalidateFiles(): void
    {
        $extensionsFromPackage = $this->getExtensionsFromManifest($this->getManifestXML(__CLASS__));

        foreach ($extensionsFromPackage as $element) {
            $paths = [];

            if (str_starts_with($element, 'plg_')) {
                [$dummy, $folder, $plugin] = explode('_', $element);

                $paths = [
                    sprintf('%s/%s/%s', JPATH_PLUGINS, $folder, $plugin),
                ];
            } elseif (str_starts_with($element, 'com_')) {
                $paths = [
                    sprintf('%s/components/%s', JPATH_ADMINISTRATOR, $element),
                    sprintf('%s/components/%s', JPATH_SITE, $element),
                    sprintf('%s/components/%s', JPATH_API, $element),
                ];
            } elseif (str_starts_with($element, 'mod_')) {
                $paths = [
                    sprintf('%s/modules/%s', JPATH_ADMINISTRATOR, $element),
                    sprintf('%s/modules/%s', JPATH_SITE, $element),
                ];
            } else {
                continue;
            }

            foreach ($paths as $path) {
                $this->recursiveClearCache($path);
            }
        }

        $this->clearFileInOPCache(JPATH_CACHE . '/autoload_psr4.php');
    }

    private function getManifestXML($class): ?SimpleXMLElement
    {
        // Get the package element name
        $myPackage = strtolower(str_replace('InstallerScript', '', $class));

        // Get the package's manifest file
        $filePath = JPATH_MANIFESTS . '/packages/' . $myPackage . '.xml';

        if (!@file_exists($filePath) || !@is_readable($filePath)) {
            return null;
        }

        $xmlContent = @file_get_contents($filePath);

        if (empty($xmlContent)) {
            return null;
        }

        return new SimpleXMLElement($xmlContent);
    }

    private function xmlNodeToExtensionName(SimpleXMLElement $fileField): ?string
    {
        $type = (string)$fileField->attributes()->type;
        $id = (string)$fileField->attributes()->id;

        switch ($type) {
            case 'component':
            case 'file':
            case 'library':
                $extension = $id;
                break;

            case 'plugin':
                $group = (string)$fileField->attributes()->group ?? 'system';
                $extension = 'plg_' . $group . '_' . $id;
                break;

            case 'module':
                $client = (string)$fileField->attributes()->client ?? 'site';
                $extension = (($client != 'site') ? 'a' : '') . $id;
                break;

            default:
                $extension = null;
                break;
        }

        return $extension;
    }

    private function getExtensionsFromManifest(?SimpleXMLElement $xml): array
    {
        if (empty($xml)) {
            return [];
        }

        $extensions = [];

        foreach ($xml->xpath('//files/file') as $fileField) {
            $extensions[] = $this->xmlNodeToExtensionName($fileField);
        }

        return array_filter($extensions);
    }

    private function clearFileInOPCache(string $file): void
    {
        static $hasOpCache = null;

        if (is_null($hasOpCache)) {
            $hasOpCache = ini_get('opcache.enable')
                && function_exists('opcache_invalidate')
                && (!ini_get('opcache.restrict_api')
                    || stripos(
                        realpath($_SERVER['SCRIPT_FILENAME']),
                        ini_get('opcache.restrict_api')
                    ) === 0);
        }

        if ($hasOpCache && (strtolower(substr($file, -4)) === '.php')) {
            opcache_invalidate($file, true);
            clearstatcache(true, $file);
        }
    }

    private function recursiveClearCache(string $path): void
    {
        if (!@is_dir($path)) {
            return;
        }

        /** @var DirectoryIterator $file */
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot() || $file->isLink()) {
                continue;
            }

            if ($file->isDir()) {
                $this->recursiveClearCache($file->getPathname());

                continue;
            }

            if (!$file->isFile()) {
                continue;
            }

            $this->clearFileInOPCache($file->getPathname());
        }
    }

    private function orderDashboardModules(): void
    {
        $modulesModel = Factory::getApplication()
            ->bootComponent('com_modules')
            ->getMVCFactory()
            ->createModel('Modules', 'Administrator', ['ignore_request' => true]);
        if ($modulesModel instanceof ModulesModel) {
            $modulesModel->setState('filter.position', 'cpanel-com-jchoptimize-cpanel');
            $modulesModel->setState('client_id', 1);
            $modules = $modulesModel->getItems();
            $foreignModuleOrder = 9;

            $moduleIdOrderMap = [];
            foreach ($modules as $module) {
                if (
                    $module->module == 'mod_submenu'
                    && (
                        $module->title == 'Com-jchoptimize-cpanel Dashboard'
                        || $module->title == 'JCH Optimize Submenus'
                    )
                ) {
                    $moduleIdOrderMap[$module->id] = 2;
                } elseif ($module->module == 'mod_jchdashicons' && $module->title == 'Optimize Files') {
                    $moduleIdOrderMap[$module->id] = 1;
                } elseif ($module->module == 'mod_jchdashicons' && $module->title == 'Notifications') {
                    $moduleIdOrderMap[$module->id] = 3;
                } elseif ($module->module == 'mod_jchdashicons' && $module->title == 'Image/CDN Features') {
                    $moduleIdOrderMap[$module->id] = 4;
                } elseif ($module->module == 'mod_jchdashicons' && $module->title == 'Utility Tasks') {
                    $moduleIdOrderMap[$module->id] = 5;
                } elseif ($module->module == 'mod_jchdashicons' && $module->title == 'CSS Features') {
                    $moduleIdOrderMap[$module->id] = 6;
                } elseif ($module->module == 'mod_jchdashicons' && $module->title == 'Advanced Features') {
                    $moduleIdOrderMap[$module->id] = 7;
                } elseif ($module->module == 'mod_jchsupportinfo') {
                    $moduleIdOrderMap[$module->id] = 8;
                } else {
                    $moduleIdOrderMap[$module->id] = $foreignModuleOrder++;
                }
            }

            $pks = array_keys($moduleIdOrderMap);
            $order = array_values($moduleIdOrderMap);

            if (count($pks) && count($pks) == count($order)) {
                /**
                 * Joomla 4.4 doesn't order this accurately. When we drop support for Joomla 4, we can use the
                 * Model's saveorder function instead.
                 */
                $this->saveorder($pks, $order);
            }
        }
    }

    private function saveorder($pks, $order): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $table = new Joomla\CMS\Table\Module($db);
        $orderingField = $table->getColumnAlias('ordering');

        // Update ordering values
        foreach ($pks as $i => $pk) {
            $table->load((int)$pk);
            // We don't want to modify tags on reorder, not removing the tagsHelper removes all associated tags
            if ($table instanceof TaggableTableInterface) {
                $table->clearTagsHelper();
            }

            if ($table->$orderingField != $order[$i]) {
                $table->$orderingField = $order[$i];
                $table->store();
            }
        }
    }

    private function writeNginxInclude(): void
    {
        $target = JPATH_ADMINISTRATOR . '/components/com_jchoptimize/etc/jch_optimize_cache.conf';
        if (!is_file($target)) {
            return;
        }
        $base = rtrim(Uri::root(true), '/');
        if ($base !== '') {
            $base = '/' . ltrim($base, '/');
        }

        $contents = (string)file_get_contents($target);
        $contents = str_replace(['__JCH_BASE__', '__JCH_VERSION__'], [$base, JCH_VERSION], $contents);
        @file_put_contents($target, $contents);
        @chmod($target, 0644);
    }
}
