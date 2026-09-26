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

use _JchOptimizeVendor\V91\GuzzleHttp\Exception\GuzzleException;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareTrait;
use JchOptimize\Core\Admin\AbstractHtml;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Platform\HtmlInterface;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Platform\ProfilerInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Uri\Utils;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Router\Route;
use Joomla\DI\Container;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

class Html implements HtmlInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private $http,
        private ProfilerInterface $profiler,
        private ConsoleApplication|CMSApplication|null $app
    ) {
    }

    /**
     * @throws Exception\RuntimeException|\Exception
     */
    public function getHomePageHtml(): string
    {
        try {
            JCH_DEBUG ? $this->profiler->mark('beforeGetHtml') : null;

            $response = $this->getHtml($this->getSiteUrl());

            JCH_DEBUG ? $this->profiler->mark('afterGetHtml') : null;

            return $response;
        } catch (GuzzleException $e) {
            $this->logger->error($this->getSiteUrl() . ': ' . $e->getMessage());

            JCH_DEBUG ? $this->profiler->mark('afterGetHtml') : null;

            throw new Exception\RuntimeException('Try reloading the front page to populate the Exclude options');
        }
    }

    /**
     * @throws GuzzleException
     */
    protected function getHtml(string $sUrl): string
    {
        $uri = Utils::uriFor($sUrl);
        $unOptimizedUri = Uri::withQueryValues($uri, ['jchnooptimize' => '1']);

        try {
            $response = $this->http->get($unOptimizedUri);
        } catch (\Exception $e) {
            throw new Exception\RuntimeException(
                'Exception fetching HTML: ' . $sUrl . ' - Message: ' . $e->getMessage()
            );
        }

        if ($response->getStatusCode() != 200) {
            throw new Exception\RuntimeException(
                'Failed fetching HTML: ' . $sUrl . ' - Message: '
                . $response->getStatusCode() . ': ' . $response->getReasonPhrase()
            );
        }

        //Get body and set pointer to beginning of stream
        $body = $response->getBody();
        $body->rewind();

        return $body->getContents();
    }

    /**
     *
     * @return string
     * @throws \Exception
     * @psalm-suppress TooManyArguments
     */
    protected function getSiteUrl(): string
    {
        $oSiteMenu = $this->getSiteMenu();
        $oDefaultMenu = $oSiteMenu->getDefault();

        if (is_null($oDefaultMenu)) {
            $oCompParams = ComponentHelper::getParams('com_languages');
            $sLanguage = $this->app instanceof CMSApplication ? $oCompParams->get(
                'site',
                $this->app->get('language', 'en-GB')
            ) : 'en-GB';
            $oDefaultMenu = $oSiteMenu->getItems(['home', 'language'], [
                '1',
                $sLanguage
            ], true);
        }

        return $this->getMenuUrl($oDefaultMenu);
    }

    /**
     * @throws \Exception
     * @psalm-suppress TooManyArguments
     */
    protected function getSiteMenu(): AbstractMenu
    {
        return $this->app->getMenu('site');
    }

    /**
     * @param MenuItem $oMenuItem
     * @return string
     * @psalm-suppress UndefinedMethod
     * @psalm-suppress UndefinedConstant
     */
    protected function getMenuUrl(MenuItem $oMenuItem): string
    {
        $sMenuUrl = $oMenuItem->link . '&Itemid=' . $oMenuItem->id;

        return Route::link('site', $sMenuUrl, true, 0, true);
    }

    /**
     * @throws \Exception
     */
    public function getMainMenuItemsHtmls($iLimit = 5, $bIncludeUrls = false): array
    {
        $oSiteMenu = $this->getSiteMenu();
        $oDefaultMenu = $oSiteMenu->getDefault();

        $aAttributes = [
            'menutype',
            'type',
            'product',
            'access',
            'home'
        ];

        $aValues = [
            $oDefaultMenu->menutype,
            'component',
            '1',
            '1',
            '0'
        ];

        //Only need 5 menu items including the home menu
        $aMenus = array_slice(
            array_merge([$oDefaultMenu], $oSiteMenu->getItems($aAttributes, $aValues)),
            0,
            $iLimit
        );

        $aHtmls = [];
        //Gonna limit the time spent on this
        $iTimerStart = microtime(true);
        /** @var MenuItem $oMenuItem */
        foreach ($aMenus as $oMenuItem) {
            $oMenuItem->link = $this->getMenuUrl($oMenuItem);

            try {
                if ($bIncludeUrls) {
                    $aHtmls[] = [
                        'url' => $oMenuItem->link,
                        'html' => $this->getHtml($oMenuItem->link)
                    ];
                } else {
                    $aHtmls[] = $this->getHtml($oMenuItem->link);
                }
            } catch (GuzzleException $e) {
                $this->logger->error($e->getMessage());
            }

            if (microtime(true) > $iTimerStart + 10.0) {
                break;
            }
        }

        return $aHtmls;
    }
}
