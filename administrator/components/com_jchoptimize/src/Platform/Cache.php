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

namespace CodeAlfa\Component\JchOptimize\Administrator\Platform;

use CodeAlfa\Component\JchOptimize\Administrator\Extension\JchOptimizeComponent;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use JchOptimize\ContainerFactory;
use JchOptimize\Core\Html\HtmlProcessor;
use JchOptimize\Core\Platform\CacheInterface;
use JchOptimize\Core\Registry;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Event\Event;

use function defined;
use function file_exists;
use function preg_replace;

use const JPATH_PLUGINS;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class Cache implements CacheInterface
{
    private CMSApplication|null|ConsoleApplication $app;

    public function __construct(CMSApplication|ConsoleApplication|null $app)
    {
        $this->app = $app;
    }

    public function cleanThirdPartyPageCache(): void
    {
        //Clean Joomla Cache
        $cache = Factory::getCache();
        $groups = ['page', 'pce'];

        foreach ($groups as $group) {
            $cache->clean($group);
        }

        if ($this->app instanceof CMSApplication) {
            //Clean LiteSpeed Cache
            if (file_exists(JPATH_PLUGINS . '/system/lscache/lscache.php')) {
                $this->app->getDispatcher()->dispatch('onLSCacheExpired', new Event('onLSCacheExpired'));
            } else {
                //This cleans the entire server
                $this->app->setHeader('X-LiteSpeed-Purge', '*');
            }
        }
    }

    /**
     * @param array|null $data
     *
     * @return array{headers: array{array-key: array{name:string, value:string}}, body:string}|null
     */
    public function prepareDataFromCache(?array $data): ?array
    {
        // The following code searches for a token in the cached page and replaces it with the proper token.
        /** @var array{headers: array{array-key: array{name:string, value:string}}, body:string}|null $data */
        if (isset($data['body'])) {
            $token = Session::getFormToken();
            $search = '#<input type="?hidden"? name="?[\da-f]{32}"? value="?1"?\s*/?>#';
            $replacement = '<input type="hidden" name="' . $token . '" value="1">';
            $data['body'] = preg_replace($search, $replacement, $data['body']);

            /** @var JchOptimizeComponent $component */
            $component = Factory::getApplication()->bootComponent('com_jchoptimize');
            $container = $component->getContainer();
            /** @var HtmlProcessor $htmlProcessor */
            $htmlProcessor = $container->get(HtmlProcessor::class);
            $htmlProcessor->setHtml($data['body']);
            $htmlProcessor->processDataFromCacheScriptToken($token);

            $data['body'] = $htmlProcessor->getHtml();
        }

        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function outputData(array $data): void
    {
        if ($this->app instanceof CMSApplication) {
            /** @var array{headers:array<array-key, array{name:string, value:string}>, body:string} $data */
            if (!empty($data['headers'])) {
                foreach ($data['headers'] as $header) {
                    $this->app->setHeader($header['name'], $header['value']);
                }
            }

            $this->app->setBody($data['body']);

            echo $this->app->toString((bool)$this->app->get('gzip'));

            $this->app->getDispatcher()->dispatch('onAfterRespond', AbstractImmutableEvent::create(
                'onAfterRespond',
                [
                    'subject' => $this->app,
                ]
            ));

            $this->app->close();
        }
    }

    /**
     * @param Registry $params
     * @param bool $nativeCache
     *
     * @return bool
     */
    public function isPageCacheEnabled(Registry $params, bool $nativeCache = false): bool
    {
        $integratedPageCache = 'jchpagecache';

        if (!$nativeCache) {
            /** @var string $integratedPageCache */
            $integratedPageCache = $params->get('pro_page_cache_integration', 'jchpagecache');

            if ($integratedPageCache == 'jchoptimizepagecache') {
                $integratedPageCache = 'jchpagecache';
            }
        }

        return PluginHelper::isEnabled('system', $integratedPageCache);
    }

    public function getCacheNamespace(bool $pageCache = false): string
    {
        if ($pageCache) {
            return 'jchpagecache';
        }

        return 'jchoptimizecache';
    }

    public function isCaptureCacheIncompatible(): bool
    {
        return false;
    }

    public function getPageCacheNamespace(): string
    {
        return 'jchpagecache';
    }

    public function getGlobalCacheNamespace(): string
    {
        return 'jchoptimizecache';
    }

    public function getTaggableCacheNamespace(): string
    {
        return 'jchoptimizetags';
    }
}
