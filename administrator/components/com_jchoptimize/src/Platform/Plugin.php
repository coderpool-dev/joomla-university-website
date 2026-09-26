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

namespace CodeAlfa\Component\JchOptimize\Administrator\Platform;

use CodeAlfa\Component\JchOptimize\Administrator\Container\ContainerFactory;
use CodeAlfa\Component\JchOptimize\Administrator\Helper\CacheCleaner;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Platform\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use JchOptimize\Core\Registry;

use Joomla\Database\DatabaseInterface;

use function in_array;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

final class Plugin implements PluginInterface
{
    protected static $plugin = null;

    /**
     *
     * @return int
     * @psalm-suppress NullableReturnStatement
     */
    public function getPluginId()
    {
        $plugin = static::loadjch();

        return $plugin->extension_id;
    }

    /**
     *
     * @return mixed|null
     */
    private function loadjch()
    {
        if (self::$plugin !== null) {
            return self::$plugin;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('folder AS type, element AS name, params, extension_id')
            ->from('#__extensions')
            ->where('type = ' . $db->quote('component'))
            ->where('element = ' . $db->quote('com_jchoptimize'));

        self::$plugin = $db->setQuery($query)->loadObject();

        return self::$plugin;
    }

    /**
     *
     * @return mixed|null
     */
    public function getPlugin()
    {
        return static::loadjch();
    }

    /**
     * @deprecated
     */
    public function getPluginParams()
    {
        return ContainerFactory::getInstance()->get(Registry::class);
    }

    /**
     * @param Registry $params
     *
     */
    public function saveSettings(Registry $params): void
    {
        $table = Table::getInstance(('extension'));
        $context = 'com_jchoptimize.plugin';
        $data = ['params' => $params->toString()];
        PluginHelper::importPlugin('extension');

        if (
            !$table->load([
            'element' => 'com_jchoptimize',
            'type' => 'component'
            ])
        ) {
            throw new Exception\RuntimeException($table->getError());
        }

        if (!$table->bind($data)) {
            throw new Exception\RuntimeException($table->getError());
        }

        if (!$table->check()) {
            throw new Exception\RuntimeException($table->getError());
        }

        /** @var array<array-key, mixed> $result */
        $result = [];

        try {
            $result = Factory::getApplication()->triggerEvent('onExtensionBeforeSave', [$context, $table, false]);
        } catch (\Exception $e) {
        }

        // Store the data.
        if (in_array(false, $result, true) || !$table->store()) {
            throw new Exception\RuntimeException($table->getError());
        }

        try {
            Factory::getApplication()->triggerEvent('onExtensionAfterSave', [$context, $table, false]);
            CacheCleaner::clearCacheGroups(['_system'], [0, 1]);
        } catch (\Exception $e) {
        }
    }
}
