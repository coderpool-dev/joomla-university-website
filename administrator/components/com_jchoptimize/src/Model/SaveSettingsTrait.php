<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use CodeAlfa\Component\JchOptimize\Administrator\Helper\CacheCleaner;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;

use function defined;
use function in_array;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

/**
 * Used in Models that are Database and State aware to save the state to the database
 */
trait SaveSettingsTrait
{
    private Registry $params;

    /**
     * @return void
     * @throws Exception\ExceptionInterface
     */
    private function saveSettings(): void
    {
        $table = Table::getInstance(('extension'), 'JTable', ['dbo' => $this->getDatabase()]);
        $context = 'com_jchoptimize.' . $this->name;
        $data = ['params' => $this->state->get('params')->toString()];
        PluginHelper::importPlugin('extension');

        if ($table === false) {
            throw new Exception\RuntimeException('Table not found');
        }

        assert($table instanceof TableInterface);

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

        try {
            $result = Factory::getApplication()->triggerEvent('onExtensionBeforeSave', [$context, $table, false]);
        } catch (\Exception) {
            $result = [];
        }

        // Store the data.
        if (in_array(false, $result, true) || !$table->store()) {
            throw new Exception\RuntimeException($table->getError());
        }

        try {
            Factory::getApplication()->triggerEvent('onExtensionAfterSave', [$context, $table, false]);
            CacheCleaner::clearCacheGroups(['_system']);
        } catch (\Exception) {
        }

        PluginHelper::reload();
    }

    abstract public function setParams(Registry $params);

    protected function populateState(): void
    {
        $this->setState('params', $this->params);
    }
}
