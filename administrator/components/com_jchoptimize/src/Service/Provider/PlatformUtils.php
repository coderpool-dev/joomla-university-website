<?php

namespace CodeAlfa\Component\JchOptimize\Administrator\Service\Provider;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\DI\ServiceProviderInterface;
use _JchOptimizeVendor\V91\Psr\Http\Client\ClientInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Cache;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Excludes;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Hooks;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Html;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Paths;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Plugin;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Profiler;
use CodeAlfa\Component\JchOptimize\Administrator\Platform\Utility;
use Exception;
use JchOptimize\Core\Platform\CacheInterface;
use JchOptimize\Core\Platform\ExcludesInterface;
use JchOptimize\Core\Platform\HooksInterface;
use JchOptimize\Core\Platform\HtmlInterface;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Platform\PluginInterface;
use JchOptimize\Core\Platform\ProfilerInterface;
use JchOptimize\Core\Platform\UtilityInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Factory;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

class PlatformUtils implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->share(CacheInterface::class, function (Container $container): CacheInterface {
            return new Cache(
                $container->get('app')
            );
        });

        $container->share(ExcludesInterface::class, function (): ExcludesInterface {
            return new Excludes();
        });

        $container->share(HooksInterface::class, function (Container $container): HooksInterface {
            return new Hooks(
                $container->get('app')
            );
        });

        $container->share(PathsInterface::class, function (Container $container): PathsInterface {
            return new Paths(
                $container->get('app')
            );
        });

        $container->share(PluginInterface::class, function (): PluginInterface {
            return new Plugin();
        });

        $container->share(ProfilerInterface::class, function (): ProfilerInterface {
            return new Profiler();
        });

        $container->share(UtilityInterface::class, function (Container $container): UtilityInterface {
            return new Utility(
                $container->get('app')
            );
        });

        $container->share(HtmlInterface::class, function (Container $container): HtmlInterface {
                $html = new Html(
                    $container->get(ClientInterface::class),
                    $container->get(ProfilerInterface::class),
                    $container->get('app')
                );
                $html->setLogger($container->get(LoggerInterface::class));

                return $html;
        });

        $container->share('app', function (): ConsoleApplication|CMSApplication|null {
            try {
                $app = Factory::getApplication();
            } catch (Exception) {
                $app = null;
            }

            return $app;
        });
    }
}
