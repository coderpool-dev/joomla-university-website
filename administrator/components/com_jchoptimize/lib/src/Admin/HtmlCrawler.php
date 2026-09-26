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

namespace JchOptimize\Core\Admin;

use _JchOptimizeVendor\V91\GuzzleHttp\Client;
use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\V91\Psr\Http\Client\ClientInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareTrait;
use _JchOptimizeVendor\V91\Psr\Log\NullLogger;
use _JchOptimizeVendor\V91\Spatie\Crawler\Crawler;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Exception;
use JchOptimize\Core\Admin\API\MessageEventInterface;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Spatie\Crawlers\HtmlCollector;
use JchOptimize\Core\Spatie\CrawlQueues\NonOptimizedCacheCrawlQueue;
use JchOptimize\Core\SystemUri;
use JchOptimize\Core\Uri\Uri;
use JchOptimize\Core\Uri\UriComparator;

use function defined;

defined('_JCH_EXEC') or die('Restricted access');

class HtmlCrawler implements LoggerAwareInterface, ContainerAwareInterface
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;

    private HtmlCollector $htmlCollector;

    public function __construct(
        protected Registry $params,
        Container $container,
        /**
         * @var Client&ClientInterface
         */
        protected $http,
        protected PathsInterface $pathsUtils
    ) {
        $this->setContainer($container);
        $this->htmlCollector = new HtmlCollector();
    }

    /**
     * @param array{base_url?:string, crawl_limit?:int} $options
     * @return list<array{url:string, html:string}>
     * @throws Exception
     */
    public function getCrawledHtmls(array $options = []): array
    {
        $defaultOptions = [
            'crawl_limit' => 10,
            'base_url' => SystemUri::homePageAbsolute($this->pathsUtils)
        ];

        $options = array_merge($defaultOptions, $options);

        if (UriComparator::isCrossOrigin(new Uri($options['base_url']))) {
            throw new Exception('Cross origin URLs not allowed');
        }

        Crawler::create(\JchOptimize\Core\Spatie\Crawler::crawlClientOptions())
            ->setCrawlObserver($this->htmlCollector)
            ->setParseableMimeTypes(['text/html'])
            ->ignoreRobots()
            ->setTotalCrawlLimit($options['crawl_limit'])
            ->setCrawlQueue($this->container->get(NonOptimizedCacheCrawlQueue::class))
            ->setCrawlProfile(new CrawlInternalUrls($options['base_url']))
            ->startCrawling($options['base_url']);

        return $this->htmlCollector->getHtmls();
    }

    public function setEventLogging(bool $logging = true, ?MessageEventInterface $messageEventObj = null): void
    {
        if ($logging && $this->logger !== null) {
            $this->htmlCollector->setEventLogging(true);
            $this->htmlCollector->setLogger($this->logger);
        } else {
            $this->htmlCollector->setLogger(new NullLogger());
        }

        $this->htmlCollector->setMessageEventObj($messageEventObj);
    }
}
