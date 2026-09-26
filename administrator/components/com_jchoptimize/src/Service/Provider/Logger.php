<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/wordpress-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Service\Provider;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\DI\ServiceProviderInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Log\JoomlaLogger;
use JchOptimize\Core\Platform\MvcLoggerInterface;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

class Logger implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->alias(MvcLoggerInterface::class, LoggerInterface::class)
            ->share(LoggerInterface::class, function () {
                return new JoomlaLogger();
            });
    }
}
