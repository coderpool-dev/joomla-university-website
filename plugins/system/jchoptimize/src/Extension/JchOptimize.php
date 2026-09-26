<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Plugin\System\JchOptimize\Extension;

use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\V91\Psr\Log\LoggerInterface;
use Exception;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Model\CacheMaintainer;
use JchOptimize\Core\Optimize;
use JchOptimize\Core\SystemUri;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;

use function defined;
use function error_reporting;
use function in_array;
use function ob_end_flush;

use const E_ALL;
use const E_NOTICE;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access'); //Joomla guard
// phpcs:enable PSR1.Files.SideEffects

class JchOptimize extends CMSPlugin implements SubscriberInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public bool $enabled = true;

    public function __construct(DispatcherInterface $dispatcher, array $config = [])
    {
        // Disable if the component is not installed or disabled
        if (!ComponentHelper::isEnabled('com_jchoptimize')) {
            $this->enabled = false;
        }

        parent::__construct($dispatcher, $config);
    }

    public static function getSubscribedEvents(): array
    {
        if (!ComponentHelper::isEnabled('com_jchoptimize')) {
            return [];
        }

        return [
            'onAfterInitialise' => 'onAfterInitialise',
            'onAfterRoute' => 'onAfterRoute',
            'onAfterRender' => 'onAfterRender',
            'onJchCacheExpired' => 'onJchCacheExpired'
        ];
    }

    public function onAfterInitialise(): void
    {
        $app = $this->getApplication();

        if (!$app instanceof CMSApplication) {
            $this->enabled = false;

            return;
        }

        $user = $app->getIdentity();

        if (!$app->isClient('site')) {
            $this->enabled = false;

            return;
        }

        if ($app->get('offline', '0') && $user->guest) {
            $this->enabled = false;

            return;
        }

        //Disable if jchnooptimize set
        if ($app->getInput()->get('jchnooptimize', '', 'int') == 1) {
            $this->enabled = false;

            return;
        }

        if ($this->params->get('disable_logged_in_users', '1') && !$user->guest) {
            $this->enabled = false;
        }
    }

    public function onAfterRoute(): void
    {
        //If not enabled, return
        if (!$this->enabled) {
            return;
        }

        $app = $this->getApplication();

        if (!$app instanceof CMSApplication) {
            return;
        }

        //Disable if in iframe
        if (strtolower((string)$app->getInput()->server->get('HTTP_SEC_FETCH_DEST')) === 'iframe') {
            $this->enabled = false;

            return;
        }

        //Disable if menu or page excluded
        $menuexcluded = $this->params->get('menuexcluded', []);
        $menuexcludedurl = $this->params->get('menuexcludedurl', []);

        if (
            in_array($app->getInput()->get('Itemid', '', 'int'), $menuexcluded) ||
            Helper::findExcludes($menuexcludedurl, SystemUri::toString())
        ) {
            $this->enabled = false;

            return;
        }

        //Disable if page being edited
        if ($app->getInput()->get('layout') == 'edit') {
            $this->enabled = false;
        }
    }

    public function onAfterRender(): void
    {
        if (!$this->enabled) {
            return;
        }

        $app = $this->getApplication();

        if (!$app instanceof CMSApplication) {
            return;
        }

        if ($this->params->get('debug', 0)) {
            error_reporting(E_ALL & ~E_NOTICE);
        }

        $html = $app->getBody();

        //Html invalid
        if (!Helper::validateHtml($html)) {
            return;
        }

        if (
            $app->getInput()->get('jchbackend') == '1'
            || $app->getInput()->get('jchnooptimize' == '1')
        ) {
            return;
        }

        if ($app->getInput()->get('jchbackend') == '2') {
            echo $html;
            while (@ob_end_flush()) {
                //no-op
            }
            exit;
        }

        $container = $this->getContainer();

        try {
            /** @var Optimize $optimize */
            $optimize = $container->get(Optimize::class);
            $optimizedHtml = $optimize->process($html);
        } catch (Exception $e) {
            $logger = $container->get(LoggerInterface::class);
            $logger->error($e->getMessage());

            $optimizedHtml = $html;
        }

        $app->setBody($optimizedHtml);
    }

    public function onJchCacheExpired(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        return $this->getContainer()->get(CacheMaintainer::class)->cleanCache();
    }
}
