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

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use JchOptimize\Core\Laminas\ArrayPaginator;
use JchOptimize\Core\PageCache\CaptureCache;
use JchOptimize\Core\PageCache\PageCache;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');

// phpcs:enable PSR1.Files.SideEffects

class PageCacheModel extends ListModel implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private PageCache $pageCache;

    private ?ArrayPaginator $arrayPaginator = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'mtime',
                'url',
                'time-1',
                'time-2',
                'device',
                'adapter',
                'http-request',
                'id'
            ];
        }
        parent::__construct($config, $factory);
    }

    public function setPageCache(PageCache $pageCache): void
    {
        $this->pageCache = $pageCache;
    }

    public function initialize(): void
    {
        if (JCH_PRO) {
            $this->container->get(CaptureCache::class)->updateHtaccess();
        }
    }

    protected function getAllItems(): array
    {
        $filters = array_merge($this->filter_fields, ['search']);

        foreach ($filters as $filter) {
            /** @var string $filterState */
            $filterState = $this->state->get("filter.{$filter}");

            if (!empty($filterState)) {
                $this->pageCache->setFilter("filter_{$filter}", $filterState);
            }
        }

        //ordering
        /** @var string $fullOrderingList */
        $fullOrderingList = $this->state->get('list.fullordering');

        if (!empty($fullOrderingList)) {
            $this->pageCache->setList('list_fullordering', $fullOrderingList);
        }

        return $this->pageCache->getItems();
    }

    public function getItems(): ArrayPaginator
    {
        if ($this->arrayPaginator === null) {
            $this->setArrayPaginator();
        }

        return $this->arrayPaginator;
    }

    public function getTotal()
    {
        if ($this->arrayPaginator === null) {
            $this->setArrayPaginator();
        }

        return $this->arrayPaginator->getTotalItemCount();
    }

    public function getStart()
    {
        if ($this->arrayPaginator === null) {
            $this->setArrayPaginator();
        }

        return $this->arrayPaginator->getAbsoluteItemNumber(0, $this->arrayPaginator->getCurrentPageNumber());
    }

    protected function setArrayPaginator(): void
    {
        $this->arrayPaginator = new ArrayPaginator($this->getAllItems());

        $start = (int)$this->getState('list.start');
        $limit = (int)$this->getState('list.limit');

        if ($start == 0) {
            $currentPageNumber = 1;
        } else {
            $currentPageNumber = ($start / $limit) + 1;
        }

        $this->arrayPaginator->setCurrentPageNumber($currentPageNumber)
            ->setItemCountPerPage($limit);
    }

    public function delete(array $ids): bool
    {
        return $this->pageCache->deleteItemsByIds($ids);
    }

    public function deleteAll(): bool
    {
        return $this->pageCache->deleteAllItems();
    }

    public function getAdapterName(): string
    {
        return $this->pageCache->getAdapterName();
    }

    public function isCaptureCacheEnabled(): bool
    {
        return $this->pageCache->isCaptureCacheEnabled();
    }
}
