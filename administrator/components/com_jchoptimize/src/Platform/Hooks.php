<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Platform;

use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use JchOptimize\Core\Platform\HooksInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Event\AbstractImmutableEvent;
use SplObjectStorage;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class Hooks implements HooksInterface
{
    private ConsoleApplication|CMSApplication|null $app;

    public function __construct(ConsoleApplication|CMSApplication|null $app)
    {
        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    public function onPageCacheSetCaching(): bool
    {
        /** @var array<array-key, mixed> $results */
        $results = $this->app->getDispatcher()
            ->dispatch('onPageCacheSetCaching', AbstractImmutableEvent::create(
                'onPageCacheSetCaching',
                [
                    'subject' => $this->app
                ]
            ))->getArgument('result', []);

        return !in_array(false, $results, true);
    }

    /**
     * @inheritDoc
     */
    public function onPageCacheGetKey(array $parts): array
    {
        $results = $this->app->getDispatcher()
            ->dispatch('onPageCacheGetKey', AbstractImmutableEvent::create(
                'onPageCacheGetKey',
                [
                    'subject' => $this->app
                ]
            ))->getArgument('result', []);

        if (!empty($results)) {
            $parts = array_merge($parts, $results);
        }

        return $parts;
    }

    public function onUserPostForm(): void
    {
        // Import the user plugin group.
        PluginHelper::importPlugin('user');
        $this->app->getDispatcher()->dispatch('onUserPostForm', AbstractImmutableEvent::create(
            'onUserPostForm',
            [
                'subject' => $this->app,
            ]
        ));
    }

    public function onUserPostFormDeleteCookie(): void
    {
        // Import the user plugin group.
        PluginHelper::importPlugin('user');
        $this->app->getDispatcher()->dispatch('onUserPostFormDeleteCookie', AbstractImmutableEvent::create(
            'onUserPostFormDeleteCookie',
            [
                'subject' => $this->app,
            ]
        ));
    }

    public function onHttp2GetPreloads(SplObjectStorage $preloads): SplObjectStorage
    {
        return $preloads;
    }
}
