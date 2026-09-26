<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Field;

use CodeAlfa\Component\JchOptimize\Administrator\Model\ModeSwitcherModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

FormHelper::loadFieldClass('list');

class JchpagecacheField extends ListField
{
    protected $type = 'jchpagecache';

    protected function getOptions(): array
    {
        $mvcFactory = Factory::getApplication()->bootComponent('com_jchoptimize')->getMVCFactory();
        /** @var ModeSwitcherModel $modeSwitcher */
        $modeSwitcher = $mvcFactory->createModel('ModeSwitcher', 'Administrator');
        $availablePlugins = $modeSwitcher->getAvailablePageCachePlugins();
        $options = [];

        foreach ($modeSwitcher->pageCachePlugins as $pageCache => $title) {
            if (in_array($pageCache, $availablePlugins)) {
                $options[] = HTMLHelper::_(
                    'select.option',
                    $pageCache,
                    Text::_($title),
                    'value',
                    'text',
                    false
                );
            } else {
                $options[] = HTMLHelper::_(
                    'select.option',
                    $pageCache,
                    Text::_($title),
                    'value',
                    'text',
                    true
                );
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
