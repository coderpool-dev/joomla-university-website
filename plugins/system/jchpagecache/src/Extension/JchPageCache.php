<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Plugin\System\JchPageCache\Extension;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\V91\Laminas\Cache\Exception\ExceptionInterface;
use CodeAlfa\Minify\Js;
use Exception;
use JchOptimize\Core\FeatureHelpers\AvifWebp;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\HtmlElementBuilder;
use JchOptimize\Core\PageCache\PageCache;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\SystemUri;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

use function defined;
use function http_response_code;
use function str_replace;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class JchPageCache extends CMSPlugin implements SubscriberInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public bool $enabled = true;

    public function __construct(DispatcherInterface $dispatcher, array $config = [])
    {
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
            'onAfterRender' => ['onAfterRender', Priority::LOW],
            'onAfterRespond' => ['onAfterRespond', Priority::LOW],
            'onPageCacheSetCaching' => 'onPageCacheSetCaching',
            'onPageCacheGetKey' => 'onPageCacheGetKey',
            'onAjaxGetformtoken' => 'onAjaxGetformtoken',
            'onAjaxUpdatehits' => 'onAjaxUpdatehits',
        ];
    }

    public function onAfterInitialise(): void
    {
        if (!$this->enabled) {
            return;
        }

        $app = $this->getApplication();
        //Boot the component to register our autoloader
        $app->bootComponent('com_jchoptimize');
        $input = $app->getInput();

        if (!$app->isClient('site')) {
            $this->enabled = false;

            return;
        }

        if ($app->get('offline', '0')) {
            $this->enabled = false;

            return;
        }

        if ($app->getMessageQueue()) {
            $this->enabled = false;

            return;
        }

        $container = $this->getContainer();

        //Disable if jchnooptimize set
        if (
            $input->get('jchnooptimize') == '1'
            || $input->get('jchbackend') == '1'
        ) {
            $this->enabled = false;

            return;
        }

        //Disable if we couldn't get cache object
        try {
            /** @var PageCache $pageCache */
            $pageCache = $container->get(PageCache::class);
        } catch (Exception) {
            $this->enabled = false;

            return;
        }

        if (JDEBUG) {
            $pageCache->disableCaptureCache();
        }

        try {
            $pageCache->initialize();
        } catch (Exception $e) {
            $this->enabled = false;

            return;
        }
    }

    public function onAfterRoute(): void
    {
        if (!$this->enabled) {
            return;
        }

        $app = $this->getApplication();
        $container = $this->getContainer();
        /** @var PageCache $pageCache */
        $pageCache = $container->get(PageCache::class);

        try {
            $pageCache->outputPageCache();
        } catch (ExceptionInterface $e) {
        }
        //If we're forcing ssl on the front end but not serving https, disable caching
        if ($app->get('force_ssl') === 2 && SystemUri::currentUri()->getScheme() !== 'https') {
            $this->enabled = false;
            return;
        }
        try {
            $excludedMenus = $this->params->get('cache_exclude_menu', []);
            $excludedComponents = $this->params->get('cache_exclude_component', ['com_ajax']);

            if (
                in_array($app->getInput()->get('Itemid', '', 'int'), $excludedMenus)
                || in_array($app->getInput()->get('option', ''), $excludedComponents)
            ) {
                $this->enabled = false;
                $pageCache->disableCaching();

                return;
            }
            //Now may be a good time to set Caching
            $pageCache->setCaching();
        } catch (Exception $e) {
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

        $pageCache = $this->getContainer()->get(PageCache::class);

        if (!Helper::validateHtml($app->getBody())) {
            $pageCache->disableCaching();

            return;
        }

        //Disable gzip so the HTML can be cached later
        $app->set('gzip', false);
    }

    public function onAfterRespond(): void
    {
        if (!$this->enabled) {
            return;
        }

        $app = $this->getApplication();

        if (!$app instanceof CMSApplication) {
            return;
        }

        $container = $this->getContainer();
        $pageCache = $container->get(PageCache::class);

        //Page cache could be disabled at runtime so check again here
        if ($pageCache->getCachingEnabled()) {
            $body = $app->getBody();
            //Still need to validate the HTMl here. We may be on a redirect.
            $httpResponse = http_response_code();
            if (Helper::validateHtml($body) && $httpResponse === 200) {
                $pageCache->store($this->addUpdateHitScript(
                    $container,
                    $this->addUpdateFormTokenAjax($container, $body)
                ));
            }
        }
    }

    /**
     * If Page Cache plugin is already disabled then this will disable the Page Cache object when it is constructed
     */
    public function onPageCacheSetCaching($event): void
    {
        $event->addArgument('result', [$this->enabled]);
    }

    public function onPageCacheGetKey($event): void
    {
        $result = [$this->getApplication()->getLanguage()->getTag()];
        if (JCH_PRO) {
            /** @see Webp::getCanIUse() */
            $result[] = $this->getContainer()->get(AvifWebp::class)->getCanIUse();
        }
        $event->addArgument('result', $result);
    }

    private function addUpdateFormTokenAjax(Container $container, string $html): string
    {
        if (!$container->get(PageCache::class)->isCaptureCacheEnabled()) {
            return $html;
        }

        $url = SystemUri::homePageAbsolute(
            $container->get(PathsInterface::class)
        ) . 'index.php?option=com_ajax&format=json&plugin=getformtoken';

        /** @see JchPageCache::onAjaxGetformtoken() */
        $script = <<<JS
let jchCsrfToken;

const updateFormToken = async() => {
    const response = await fetch('$url');
    
    if (response.ok) {
        const jsonValue = await response.json();
            
        return Promise.resolve(jsonValue);
    }
}

updateFormToken().then(data => {
    const formRegex = new RegExp('[0-9a-f]{32}');
    jchCsrfToken = data.data[0];
    
    for (let formToken of document.querySelectorAll('input[type=hidden]')){
        if (formToken.value == '1' && formRegex.test(formToken.name)){
            formToken.name = jchCsrfToken;
        }
    }
    
    const jsonRegex = new RegExp('"csrf\.token":"[^"]+"');
    
    for(let scriptToken of document.querySelectorAll('script[type="application/json"]')){
        if(scriptToken.classList.contains('joomla-script-options')){
            let json = scriptToken.textContent;
            if(jsonRegex.test(json)){
                scriptToken.textContent = json.replace(jsonRegex, '"csrf.token":"' + jchCsrfToken + '"');
            }
        }
    }
    
    updateJoomlaOption();
});

function updateJoomlaOption(){
    if (typeof Joomla !== "undefined" ){
        Joomla.loadOptions({"csrf.token": null});
        Joomla.loadOptions({"csrf.token": jchCsrfToken});
    }
}

document.addEventListener('jch:dynamicJsLoaded', (event) => {
    updateJoomlaOption();
});
JS;
        $htmlScript = HtmlElementBuilder::script()->addChild(Js::optimize($script))->type('module');

        return str_replace('</body>', $htmlScript->render() . "\n" . '</body>', $html);
    }

    public function onAjaxGetformtoken($event): void
    {
        $event->addArgument('result', [Session::getFormToken()]);
    }

    private function addUpdateHitScript(Container $container, string $body): string
    {
        $input = $this->getApplication()->getInput();
        $option = $input->getCmd('option');
        $view = $input->getCmd('view');
        $id = $input->getCmd('id');

        if (
            $id
            && in_array($option, ['com_content', 'com_contact', 'com_tags', 'com_newsfeed'])
            && in_array($view, ['category', 'article', 'tags', 'contacts'])
            && $this->recordHitsOptionSet($option)
        ) {
            $script = $this->getUpdateHitsScript($container, $option, $view, $id);
            $body = str_replace('</body>', "{$script}\n</body>", $body);
        }

        return $body;
    }

    private function getUpdateHitsScript(Container $container, string $option, string $view, string $id): string
    {
        $baseUrl = SystemUri::homePageAbsolute($container->get(PathsInterface::class));
        /** @see self::onAjaxUpdatehits() */
        $ajaxPath = 'index.php?option=com_ajax&format=json&plugin=updatehits';
        $url = "{$baseUrl}{$ajaxPath}&hitoption={$option}&hitview={$view}&hitid={$id}";

        return <<<JS
<script type='module'>
const updateHits=async()=>{const response=await fetch('{$url}');
if(response.ok){const result=await response.json();return Promise.resolve(result);}}
updateHits().then(data=>{console.log(data.data[0]);});
</script>
JS;
    }

    public function onAjaxUpdatehits($event): void
    {
        $app = $this->getApplication();
        $input = $app->getInput();
        $input->set('hitcount', 1);
        $view = rtrim($input->getCmd('hitview'));
        try {
            $model = $app->bootComponent($input->get('hitoption'))
                ->getMVCFactory()
                ->createModel(ucfirst($view), 'Site');
            $model->hit((int)$input->getCmd('hitid'));
            $event->addArgument('result', ['Hit Updated']);
        } catch (Exception $e) {
            $event->addArgument('result', [$e->getMessage()]);
        }
    }

    private function recordHitsOptionSet(string $option): bool
    {
        $recordHits = ComponentHelper::getParams($option)->get('record_hits');

        return $recordHits === null || $recordHits;
    }
}
