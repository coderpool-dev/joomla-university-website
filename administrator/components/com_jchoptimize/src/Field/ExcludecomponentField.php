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

use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class ExcludecomponentField extends JchMultiSelectField
{
    protected $type = 'excludecomponent';

    protected function getOptions(): array
    {
        $options = [];

        $params = $this->container->get('params');

        $installedComponents = Folder::folders(JPATH_SITE . '/components');
        $excludedComponents = $params->get('cache_exclude_component', ['com_ajax']);

        $components = array_unique(array_merge($installedComponents, $excludedComponents));

        foreach ($components as $component) {
            $options[$component] = $component;
        }

        return $options;
    }
}
