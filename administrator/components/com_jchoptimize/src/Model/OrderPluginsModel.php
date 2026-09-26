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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Plugins\Administrator\Model\PluginModel;
use Joomla\Utilities\ArrayHelper;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class OrderPluginsModel extends BaseDatabaseModel
{
    public function orderPlugins(): bool
    {
        //These plugins must be ordered last in this order; array of plugin elements
        $aOrder = [
            'jscsscontrol',
            'eorisis_jquery',
            'jqueryeasy',
            'quix',
            'jchoptimize',
            'setcanonical',
            'canonical',
            'plugin_googlemap3',
            'jomcdn',
            'cdnforjoomla',
            'bigshotgoogleanalytics',
            'GoogleAnalytics',
            'pixanalytic',
            'ykhoonhtmlprotector',
            'jat3',
            'cache',
            'plg_gkcache',
            'pagecacheextended',
            'homepagecache',
            'jSGCache',
            'j2pagecache',
            'jotcache',
            'lscache',
            'vmcache_last',
            'pixcookiesrestrict',
            'speedcache',
            'speedcache_last',
            'jchpagecache',
        ];

        //Get an associative array of all installed system plugins with their extension id, ordering, and element
        /** @psalm-var array<string, array{extension_id: int, ordering: int, element: string}> $aPlugins */
        $aPlugins = self::getPlugins();

        //Get an array of all the plugins that are installed that are in the array of specified plugin order above
        $aLowerPlugins = array_values(
            array_filter(
                $aOrder,
                function ($aVal) use ($aPlugins) {
                    return (array_key_exists($aVal, $aPlugins));
                }
            )
        );

        //Number of installed plugins
        $iNoPlugins = count($aPlugins);

        $cid = [];
        $order = [];

        //Iterate through list of installed system plugins
        foreach ($aPlugins as $key => $value) {
            if (in_array($key, $aLowerPlugins)) {
                $value['ordering'] = $iNoPlugins + 1 + (int)array_search($key, $aLowerPlugins);
            }

            $cid[] = $value['extension_id'];
            $order[] = $value['ordering'];
        }

        ArrayHelper::toInteger($cid);
        ArrayHelper::toInteger($order);

        /** @var PluginModel $pluginModel */
        $pluginModel = Factory::getApplication()->bootComponent('com_plugins')
            ->getMVCFactory()
            ->createModel('Plugin', 'Administrator');

        /** @psalm-suppress InvalidArgument */
        return $pluginModel->saveorder($cid, $order);
    }

    private function getPlugins(): array
    {
        $db = $this->getDatabase();

        $oQuery = $db->getQuery(true);
        $oQuery->select($db->quoteName(['extension_id', 'ordering', 'element']))
            ->from($db->quoteName('#__extensions'))
            ->where([
                $db->quoteName('type') . ' = ' . $db->quote('plugin'),
                $db->quoteName('folder') . ' = ' . $db->quote('system')
            ], 'AND');

        $db->setQuery($oQuery);

        return $db->loadAssocList('element');
    }
}
