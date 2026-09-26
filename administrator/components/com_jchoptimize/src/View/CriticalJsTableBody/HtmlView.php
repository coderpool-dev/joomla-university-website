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

namespace CodeAlfa\Component\JchOptimize\Administrator\View\CriticalJsTableBody;

use CodeAlfa\Component\JchOptimize\Administrator\Model\PopulateModalBodyModel;

use function defined;

use const JPATH_ADMINISTRATOR;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects

class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
    protected array $items;

    protected $_name = 'CriticalJsTableBody';

    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_jchoptimize/tmpl/criticaljstablebody';

        parent::__construct($config);
    }

    public function display($tpl = null): void
    {
        /** @var PopulateModalBodyModel $model */
        $model = $this->getModel('PopulateModalBody');
        $this->items = $model->getDynamicJavaScriptData($model->getState('jchoptimize.base_url'));

        parent::display($tpl);
    }
}
