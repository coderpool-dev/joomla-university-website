<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Platform;

use Exception;
use JchOptimize\Core\Platform\UtilityInterface;
use JchOptimize\Core\Registry;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use stdClass;

use function defined;
use function strpos;
use function wordwrap;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class Utility implements UtilityInterface
{
    private CMSApplication|null|ConsoleApplication $app;

    public function __construct(CMSApplication|ConsoleApplication|null $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function translate(string $text): string
    {
        if (strlen($text) > 20) {
            $strpos = strpos(wordwrap($text, 20), "\n");

            if ($strpos !== false) {
                $text = substr($text, 0, $strpos);
            }
        }

        $text = 'COM_JCHOPTIMIZE_' . strtoupper(str_replace([' ', '\''], ['_', ''], $text));

        return Text::_($text);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isGuest(): bool
    {
        return (bool)$this->app->getIdentity()->guest;
    }


    /**
     * @param array $headers
     *
     * @return void
     */
    public function sendHeaders(array $headers): void
    {
        /** @psalm-var array{name:string} $headers */

        if ($this->app instanceof CMSApplication) {
            foreach ($headers as $header => $value) {
                $this->app->setHeader($header, $value, true);
            }
        }
    }

    public function userAgent(string $userAgent): stdClass
    {
        $oWebClient = new WebClient($userAgent);

        $oUA = new stdClass();

        $oUA->browser = match ($oWebClient->browser) {
            $oWebClient::CHROME => 'Chrome',
            $oWebClient::FIREFOX => 'Firefox',
            $oWebClient::SAFARI => 'Safari',
            $oWebClient::EDGE => 'Edge',
            $oWebClient::IE => 'Internet Explorer',
            $oWebClient::OPERA => 'Opera',
            default => 'Unknown',
        };

        $oUA->os = match ($oWebClient->platform) {
            $oWebClient::ANDROID, $oWebClient::ANDROIDTABLET => 'Android',
            $oWebClient::IPAD, $oWebClient::IPHONE, $oWebClient::IPOD => 'iOS',
            $oWebClient::MAC => 'Mac',
            $oWebClient::WINDOWS, $oWebClient::WINDOWS_CE, $oWebClient::WINDOWS_PHONE => 'Windows',
            $oWebClient::LINUX => 'Linux',
            default => 'Unknown',
        };

        $oUA->browserVersion = $oWebClient->browserVersion;

        if (!$oUA->browserVersion) {
            $oUA->browserVersion = '0';
            $oUA->browser = 'Unknown';
            $oUA->os = 'Unknown';
        }

        return $oUA;
    }

    /**
     * Should return the attribute used to store content values for popover that the version of Bootstrap
     * is using
     *
     * @return string
     */
    public function bsTooltipContentAttribute(): string
    {
        return 'data-bs-content';
    }

    /**
     * @deprecated Use Cache::isPageCacheEnabled()
     */
    public function isPageCacheEnabled(Registry $params, bool $nativeCache = false): bool
    {
        return PluginHelper::isEnabled('system', 'jchpagecache');
    }

    public function isMobile(): bool
    {
        $webClient = new WebClient();

        return $webClient->mobile;
    }

    /**
     * @deprecated Use Cache::getCacheStorage()
     */
    public function getCacheStorage(Registry $params): string
    {
        switch ($params->get('pro_cache_storage_adapter', 'filesystem')) {
            //Used in Unit testing.
            case 'blackhole':
                return 'blackhole';

            case 'global':
                $storageMap = [
                    'file' => 'filesystem',
                    'redis' => 'redis',
                    'apcu' => 'apcu',
                    'memcached' => 'memcached',
                ];

                /** @var string $handler */
                $handler = $this->app->get('cache_handler', 'file');

                if (in_array($handler, array_keys($storageMap))) {
                    return $storageMap[$handler];
                }

            // no break
            case 'filesystem':
            default:
                return 'filesystem';
        }
    }

    /**
     * @return array<array{header:string, value:string}>
     */
    public function getHeaders(): array
    {
        if ($this->app instanceof CMSApplication) {
            /** @var array<array{header:string, value:string}> $headers */
            $headers = $this->app->getHeaders();

            return $headers;
        }

        return [];
    }

    /**
     * @param string $message
     * @param string $messageType
     * @return void
     */
    public function publishAdminMessages(string $message, string $messageType): void
    {
        $this->app->enqueueMessage($message, $messageType);
    }

    public function getLogsPath(): string
    {
        /** @var string $logPath */
        $logPath = $this->app->get('log_path');

        return $logPath;
    }

    public function isSiteGzipEnabled(): bool
    {
        return $this->app->get('gzip')
            && !ini_get('zlib.output_compression')
            && (ini_get('output_handler') !== 'ob_gzhandler');
    }

    public function isAdmin(): bool
    {
        return $this->app->isClient('administrator');
    }

    public function getNonce(string $id): string
    {
        return '';
    }
}
