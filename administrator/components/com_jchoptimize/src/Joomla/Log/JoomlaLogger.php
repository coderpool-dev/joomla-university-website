<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Joomla\Log;

use _JchOptimizeVendor\V91\Psr\Log\AbstractLogger;
use Joomla\CMS\Log\Log;
use Psr\Log\LoggerInterface;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class JoomlaLogger extends AbstractLogger
{
    private LoggerInterface $joomlaPsrLogger;

    private const CATEGORY = 'com_jchoptimize';

    public function __construct()
    {
        $this->joomlaPsrLogger = Log::createDelegatedLogger();

        Log::addLogger(
            [
                'text_file' => 'com_jchoptimize.logs.php'
            ],
            Log::ALL,
            [self::CATEGORY]
        );
    }

    public function log($level, $message, array $context = []): void
    {
        $context['category'] = self::CATEGORY;

        $this->joomlaPsrLogger->log($level, $message, $context);
    }
}
