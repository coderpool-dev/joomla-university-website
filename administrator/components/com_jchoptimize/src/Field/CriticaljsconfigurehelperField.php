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

use _JchOptimizeVendor\V91\Psr\Container\ContainerExceptionInterface;
use _JchOptimizeVendor\V91\Psr\Container\NotFoundExceptionInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use JchOptimize\ContainerFactory;
use JchOptimize\Core\Admin\AdminHelper;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\SystemUri;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\LayoutHelper;

use function array_merge;

use const JCH_PRO;
use const JPATH_ADMINISTRATOR;

class CriticaljsconfigurehelperField extends FormField
{
    protected $type = 'criticaljsconfigurehelper';

    protected $layout = 'configure_helper.critical_js_joomla_layout';


    protected function getInput(): string
    {
        if (!JCH_PRO) {
            return AdminHelper::proOnlyField();
        } else {
            return parent::getInput();
        }
    }

    protected function getLayoutData(): array
    {
        $component = Factory::getApplication()->bootComponent('com_jchoptimize');

        if (JCH_PRO && $component instanceof JchOptimizeComponent) {
            $container = $component->getContainer();
            try {
                $pathsUtils = $container->get(PathsInterface::class);
                $data = [
                    'baseUrl' => SystemUri::homePageAbsolute($pathsUtils),
                    'loadingImageUrl' => $pathsUtils->mediaUrl() . '/core/images/loader.gif',
                    'tableBodyAjaxUrl' => 'index.php?option=com_jchoptimize&view=CriticalJsTableBody',
                    'autoSaveAjaxUrl' => 'index.php?option=com_jchoptimize&view=CriticalJsAutoSave&format=json'
                ];
                return array_merge(parent::getLayoutData(), $data);
            } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            }
        }

        return parent::getLayoutData();
    }

    protected function getLayoutPaths(): array
    {
        return array_merge(
            parent::getLayoutPaths(),
            [
                JPATH_ADMINISTRATOR . '/components/com_jchoptimize/layouts'
            ]
        );
    }
}
