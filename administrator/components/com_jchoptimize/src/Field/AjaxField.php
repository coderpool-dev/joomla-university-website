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

namespace CodeAlfa\Component\JchOptimize\Administrator\Field;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use SimpleXMLElement;

use const JPATH_ADMINISTRATOR;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class AjaxField extends FormField
{
    protected $type = 'ajax';

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        include_once(JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php');
        HTMLHelper::_('jquery.framework', true, null, false);

        /** @var Document $document */
        $document = Factory::getApplication()->getDocument();
        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_jchoptimize');
        $wa->useStyle('com_jchoptimize.core.admin-styles')
            ->useScript('com_jchoptimize.core.admin-utility')
            ->useScript('com_jchoptimize.platform-joomla')
            ->useScript('com_jchoptimize.core.multiselect')
            ->useStyle('com_jchoptimize.js-excludes')
            ->useStyle('com_jchoptimize.core.multiselect-css')
            ->useScript('bootstrap.modal');

        $ajax_url = Route::_('index.php?option=com_jchoptimize&view=Ajax', false, Route::TLS_IGNORE, true);

        $script = <<<JS
var jch_observers = [];        
var jch_ajax_url = '$ajax_url';

JS;

        $wa->addInlineScript($script);

        return false;
    }

    protected function getInput(): string
    {
        return '';
    }
}
