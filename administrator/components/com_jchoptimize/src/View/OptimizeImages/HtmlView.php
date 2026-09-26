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

namespace CodeAlfa\Component\JchOptimize\Administrator\View\OptimizeImages;

use CodeAlfa\Component\JchOptimize\Administrator\Helper\OptimizeImage;
use CodeAlfa\Component\JchOptimize\Administrator\Model\ApiParamsModel;
use JchOptimize\Core\Admin\Icons;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri as JUri;

use function defined;

use const JPATH_ADMINISTRATOR;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects

class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
{
    protected Icons $icons;

    protected $_name = 'OptimizeImages';

    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_jchoptimize/tmpl/optimizeimages';

        parent::__construct($config);
    }

    public function display($tpl = null): void
    {
        $this->loadResources();
        $this->loadToolBar();

        parent::display($tpl);
    }

    public function setIcons(Icons $icons): void
    {
        $this->icons = $icons;
    }

    public function loadResources(): void
    {
        HTMLHelper::_('jquery.framework');

        $document = $this->getDocument();
        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_jchoptimize');
        $wa->useStyle('com_jchoptimize.core.admin-styles')
            ->useStyle('com_jchoptimize.admin-joomla')
            ->useScript('com_jchoptimize.platform-joomla')
            ->usePreset('com_jchoptimize.filetree');

        $ajax_filetree = Route::_('index.php?option=com_jchoptimize&view=Ajax&task=filetree', false);

        $script = <<<JS
		
jQuery(document).ready( function() {
	jQuery("#file-tree-container").fileTree({
		root: "",
		script: "$ajax_filetree",
		expandSpeed: 100,
		collapseSpeed: 100,
		multiFolder: false
	}, function(file) {});
});
JS;

        $wa->addInlineScript($script);

        if (JCH_PRO) {
            /** @see ApiParamsModel::getCompParams() */
            OptimizeImage::loadResources($wa, json_encode($this->get('CompParams', 'apiParams')));

            HTMLHelper::_('bootstrap.modal');
        }

        $options = [
            'trigger' => 'hover focus',
            'placement' => 'right',
            'html' => true
        ];

        HTMLHelper::_('bootstrap.popover', '.hasPopover', $options);
    }

    public function loadToolBar(): void
    {
        ToolbarHelper::title(Text::_(JCH_PRO ? 'COM_JCHOPTIMIZE_PRO' : 'COM_JCHOPTIMIZE'), 'dashboard');

        ToolbarHelper::link(
            Route::_('index.php?option=com_cpanel&view=cpanel&dashboard=com_jchoptimize.cpanel'),
            Text::_('COM_JCHOPTIMIZE_TOOLBAR_LABEL_CONTROLPANEL'),
            'home'
        );
        ToolbarHelper::link(
            Route::_('index.php?option=com_jchoptimize&view=PageCache'),
            Text::_('COM_JCHOPTIMIZE_TOOLBAR_LABEL_PAGECACHE'),
            'list'
        );
        ToolbarHelper::preferences('com_jchoptimize');
    }
}
