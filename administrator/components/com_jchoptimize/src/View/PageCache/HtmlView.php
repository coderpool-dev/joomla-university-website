<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\View\PageCache;

use CodeAlfa\Component\JchOptimize\Administrator\Model\PageCacheModel;
use JchOptimize\Core\Laminas\ArrayPaginator;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri as JUri;

use function defined;

use const JPATH_ADMINISTRATOR;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
    protected ArrayPaginator $paginator;

    protected $state;

    protected $items;

    protected $pagination;

    protected $adapter;

    protected $httpRequest;

    public $filterForm;

    public $activeFilters;

    protected $_name = 'PageCache';

    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_jchoptimize/tmpl/pagecache';

        parent::__construct($config);
    }

    public function display($tpl = null): void
    {
        /** @var PageCacheModel $model */
        $model = $this->getModel();

        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->adapter = $model->getAdapterName();
        $this->httpRequest = $model->isCaptureCacheEnabled();

        $this->loadResources();
        $this->loadToolBar();

        parent::display($tpl);
    }

    public function loadResources(): void
    {
        HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'right']);

        $document = $this->getDocument();
        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_jchoptimize');
        $wa->useStyle('com_jchoptimize.admin-joomla');
    }

    public function loadToolBar(): void
    {
        ToolbarHelper::title(Text::_(JCH_PRO ? 'COM_JCHOPTIMIZE_PRO' : 'COM_JCHOPTIMIZE'), 'dashboard');

        ToolbarHelper::link(
            Route::_('index.php?option=com_cpanel&view=cpanel&dashboard=com_jchoptimize.cpanel'),
            Text::_('COM_JCHOPTIMIZE_TOOLBAR_LABEL_CONTROLPANEL'),
            'home'
        );
        ToolbarHelper::deleteList();
        ToolbarHelper::custom('deleteAll', 'remove', '', 'JTOOLBAR_DELETE_ALL', false);

        if (JCH_PRO) {
            $alt = 'COM_JCHOPTIMIZE_RECACHE';
        } else {
            $alt = 'COM_JCHOPTIMIZE_RECACHE_PROONLY';
        }

        ToolbarHelper::custom('recache', 'share', '', $alt, false);
        ToolbarHelper::link(
            Route::_('index.php?option=com_jchoptimize&view=OptimizeImages'),
            Text::_('COM_JCHOPTIMIZE_TOOLBAR_LABEL_OPTIMIZEIMAGE'),
            'images'
        );
        ToolbarHelper::preferences('com_jchoptimize');
    }
}
