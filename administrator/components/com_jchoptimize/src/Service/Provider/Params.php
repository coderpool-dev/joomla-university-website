<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Service\Provider;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\DI\ServiceProviderInterface;
use JchOptimize\Core\Registry;
use Joomla\CMS\Component\ComponentHelper;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

class Params implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->alias('params', Registry::class)
            ->share(
                Registry::class,
                function (): Registry {
                    //Get a clone so when we get a new instance of the container we get a different object
                    $params = clone ComponentHelper::getParams('com_jchoptimize');

                    if (!defined('JCH_DEBUG')) {
                        define('JCH_DEBUG', ($params->get('debug', 0)));
                    }

                    return new Registry($params);
                }
            );
    }
}
