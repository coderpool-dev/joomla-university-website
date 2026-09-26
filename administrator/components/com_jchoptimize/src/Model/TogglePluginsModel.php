<?php

/**
 * @package     JchOptimize\Model
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use CodeAlfa\Component\JchOptimize\Administrator\Helper\CacheCleaner;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use Exception;
use JchOptimize\Core\PageCache\CaptureCache;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

use function defined;
use function is_null;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');

// phpcs:enable PSR1.Files.SideEffects

class TogglePluginsModel extends BaseDatabaseModel implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function togglePageCacheState(string $plugin, ?int $state = null): bool
    {
        //If state was not set then we toggle the existing state
        if (is_null($state)) {
            $state = PluginHelper::isEnabled('system', $plugin) ? 0 : 1;
        }

        $result = $this->setPluginState($plugin, $state);

        CacheCleaner::clearPluginsCache();
        PluginHelper::reload();

        $this->updateHtaccess();

        return $result;
    }


    public function setPluginState(string $element, int $state, string $folder = 'system'): bool
    {
        try {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->update('#__extensions')
                ->set($db->quoteName('enabled') . ' = :state')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = :folder')
                ->where($db->quoteName('element') . ' = :element')
                ->bind(':state', $state, ParameterType::INTEGER)
                ->bind(':folder', $folder)
                ->bind(':element', $element);
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            return false;
        }

        if ($element == 'jchpagecache' && $folder == 'system') {
            $this->updateHtaccess();
        }

        return true;
    }

    private function updateHtaccess(): void
    {
        if (JCH_PRO) {
            $this->getContainer()->get(CaptureCache::class)->updateHtaccess();
        }
    }

    public function toggleRecachePluginState(): bool
    {
        $state = PluginHelper::isEnabled('console', 'jchoptimize') ? 1 : 0;

        return $this->setPluginState('jchoptimize', 1 - $state, 'console');
    }
}
