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

use JchOptimize\Core\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ModelInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ApiParamsModel extends BaseDatabaseModel
{
    private ModelInterface|UpdatesModel $updates;

    private Registry $params;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->updates = $this->getMVCFactory()->createModel('Updates');
    }


    public function getCompParams(): array
    {
        if ($this->updates instanceof UpdatesModel) {
            return [
                'auth' => [
                    'dlid' => $this->updates->getLicenseKey(),
                    'secret' => '0aad0284',
                ],
                'resize_mode' => $this->params->get('pro_api_resize_mode', 'manual'),
                'webp' => (bool)$this->params->get('pro_next_gen_images', '1'),
                'avif' => (bool)$this->params->get('gen_avif_images', '1'),
                'lossy' => (bool)$this->params->get('lossy', '1'),
                'save_metadata' => (bool)$this->params->get('save_metadata', '0'),
                'quality' => $this->params->get('quality', '85'),
                'cropgravity' => $this->params->get('cropgravity', []),
                'responsive' => (bool)$this->params->get('pro_gen_responsive_images', '1')
            ];
        }

        return [];
    }

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }
}
