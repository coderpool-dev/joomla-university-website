<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\View\ToggleSetting;

use CodeAlfa\Component\JchOptimize\Administrator\Model\ConfigureModel;

use function defined;
use function json_encode;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class JsonView extends \Joomla\CMS\MVC\View\JsonView
{
    public function display($tpl = null): void
    {
        /** @var ConfigureModel $model */
        $model = $this->getModel('Configure');

        echo json_encode($model->getOutput());
    }
}
