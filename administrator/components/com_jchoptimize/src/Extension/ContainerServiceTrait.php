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

namespace CodeAlfa\Component\JchOptimize\Administrator\Extension;

use _JchOptimizeVendor\V91\Psr\Container\ContainerInterface;
use LogicException;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

trait ContainerServiceTrait
{
    private ?ContainerInterface $container = null;

    public function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            throw new LogicException('ContainerService not initialized.');
        }

        return $this->container;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
