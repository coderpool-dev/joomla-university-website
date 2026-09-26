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

namespace CodeAlfa\Component\JchOptimize\Administrator\Container;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use CodeAlfa\Component\JchOptimize\Administrator\Service\Provider\Logger;
use CodeAlfa\Component\JchOptimize\Administrator\Service\Provider\Params;
use CodeAlfa\Component\JchOptimize\Administrator\Service\Provider\PlatformUtils;
use JchOptimize\Core\AbstractContainerFactory;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

/**
 * A class to easily fetch a Joomla\DI\Container with all dependencies registered
 */
class ContainerFactory extends AbstractContainerFactory
{
    public function registerPlatformServiceProviders(Container $container): void
    {
        $container->registerServiceProvider(new PlatformUtils())
            ->registerServiceProvider(new Params())
            ->registerServiceProvider(new Logger());
    }
}
