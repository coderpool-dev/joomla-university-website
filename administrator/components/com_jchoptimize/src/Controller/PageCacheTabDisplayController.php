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

namespace CodeAlfa\Component\JchOptimize\Administrator\Controller;

use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use CodeAlfa\Component\JchOptimize\Administrator\Joomla\Plugin\PluginHelper;
use CodeAlfa\Component\JchOptimize\Administrator\Model\ModeSwitcherModel;
use CodeAlfa\Component\JchOptimize\Administrator\Model\PageCacheModel as PageCacheModel;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use UnexpectedValueException;

use function base64_encode;
use function defined;

use const JCH_PRO;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');

// phpcs:enable PSR1.Files.SideEffects

class PageCacheTabDisplayController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    private string $pageCacheUrl;

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);

        $this->pageCacheUrl = Route::_('index.php?option=com_jchoptimize&view=PageCache', false, 0, true);
    }

    public function remove(): void
    {
        $this->handleRedirect($this->getPageCacheModel()->delete((array)$this->input->get('cid', [])));
    }

    public function deleteAll(): void
    {
        $this->handleRedirect($this->getPageCacheModel()->deleteAll());
    }

    private function handleRedirect($success = false): void
    {
        if ($success) {
            $this->setMessage(Text::_('COM_JCHOPTIMIZE_PAGECACHE_DELETED_SUCCESSFULLY'), 'success');
        } else {
            $this->setMessage(Text::_('COM_JCHOPTIMIZE_PAGECACHE_DELETE_ERROR'), 'error');
        }

        $this->setRedirect($this->pageCacheUrl);
        $this->redirect();
    }

    public function recache(): void
    {
        if (JCH_PRO) {
            $utility = $this->factory->createController('Utility', 'Administrator', [], $this->app, $this->input);
            if ($utility instanceof UtilityController) {
                $utility->recache($this->pageCacheUrl);
            }
        }
    }

    public function display($cachable = false, $urlparams = []): PageCacheTabDisplayController
    {
        /** @var ModeSwitcherModel $modeSwitcher */
        $modeSwitcher = $this->getModel('ModeSwitcher', 'Administrator');
        $integratedPageCache = $modeSwitcher->getIntegratedPageCachePlugin();

        //For backwards compatibility
        if ($integratedPageCache == 'jchoptimizepagecache') {
            $integratedPageCache = 'jchpagecache';
        }

        if ($integratedPageCache == 'jchpagecache') {
            if (!PluginHelper::isEnabled('system', 'jchpagecache')) {
                $editUrl = Route::_(
                    'index.php?option=com_jchoptimize&view=Utility&task=togglepagecache&return='
                    . base64_encode(Uri::getInstance()->toString()),
                    false
                );

                $this->app->enqueueMessage(
                    Text::sprintf('COM_JCHOPTIMIZE_PAGECACHE_NOT_ENABLED', $editUrl),
                    'warning'
                );
            }
        } else {
            /** @var ModeSwitcherModel $modeSwitcher */
            $modeSwitcher = $this->getModel('ModeSwitcher', 'Administrator');
            $this->app->enqueueMessage(
                Text::sprintf(
                    'COM_JCHOPTIMIZE_INTEGRATED_PAGE_CACHE_NOT_JCHOPTIMIZE',
                    Text::_($modeSwitcher->pageCachePlugins[$integratedPageCache])
                ),
                'info'
            );
        }

        return parent::display($cachable, $urlparams);
    }

    private function getPageCacheModel(): PageCacheModel
    {
        $model = $this->getModel('PageCache', 'Administrator');

        if ($model instanceof PageCacheModel) {
            return $model;
        }

        throw new UnexpectedValueException('PageCacheMode not returned');
    }
}
