<?php

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

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use JchOptimize\Core\Exception\ExceptionInterface;
use JchOptimize\Core\Registry;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ConfigureHelperModel extends BaseDatabaseModel
{
    use SaveSettingsTrait;

    /**
     * @throws ExceptionInterface
     */
    public function applySettings(array $settings): void
    {
        $params = $this->getState('params');

        foreach ($settings as $key => $value) {
            $params->set($key, $value);
        }

        $this->setState('params', $params);
        $this->saveSettings();
    }

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }
}
