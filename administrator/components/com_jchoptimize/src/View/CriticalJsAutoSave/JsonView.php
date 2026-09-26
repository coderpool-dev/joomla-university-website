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

namespace CodeAlfa\Component\JchOptimize\Administrator\View\CriticalJsAutoSave;

use CodeAlfa\Component\JchOptimize\Administrator\Model\ConfigureHelperModel;
use JchOptimize\Core\Exception\ExceptionInterface;

use function defined;
use function json_encode;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects

class JsonView extends \Joomla\CMS\MVC\View\JsonView
{
    public function display($tpl = null): void
    {
        try {
            /** @var ConfigureHelperModel $model */
            $model = $this->getModel('ConfigureHelper');
            $model->applySettings($model->getState('jchoptimize.settings'));
            echo json_encode(['success' => true]);
        } catch (ExceptionInterface $e) {
            echo json_encode(['success' => false]);
        }
    }
}
