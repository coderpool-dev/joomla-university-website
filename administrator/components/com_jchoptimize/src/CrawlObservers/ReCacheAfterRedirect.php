<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\CrawlObservers;

use _JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareTrait;
use _JchOptimizeVendor\V91\Psr\Log\LoggerInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlObservers\CrawlObserver;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');//Joomla guard
// phpcs:enable PSR1.Files.SideEffects

class ReCacheAfterRedirect extends CrawlObserver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(?LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null): void
    {
        $this->logger?->info('Crawled: ' . (string) $url);
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ): void {
        $this->logger?->error('Crawl Failed: ' . (string) $url . ' - ' . $requestException->getMessage());
    }
}
