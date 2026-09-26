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

namespace CodeAlfa\Component\JchOptimize\Administrator\CrawlObservers;

use _JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlObservers\CrawlObserver;
use Joomla\CMS\Language\Text;
use Symfony\Component\Console\Style\SymfonyStyle;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ReCacheCli extends CrawlObserver
{
    private int $numCrawled = 0;

    public function __construct(private SymfonyStyle $symfonyStyle)
    {
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null): void
    {
        $this->symfonyStyle->writeln(Text::sprintf('COM_JCHOPTIMIZE_CLI_URL_CRAWLED', $url));
        $this->numCrawled++;
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null): void
    {
        $this->symfonyStyle->comment(Text::sprintf('COM_JCHOPTIMIZE_CLI_URL_CRAWL_FAILED', $url));
    }

    public function getNumCrawled(): int
    {
        return $this->numCrawled;
    }
}
