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

use CodeAlfa\Component\JchOptimize\Administrator\View\PageCache\HtmlView;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted Access');

/** @var HtmlView $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDir = $this->escape($this->state->get('list.direction'));

$webAsset = $this->document->getWebAssetManager();
$webAsset->useScript('keepalive')
    ->useScript('table.columns')
    ->useScript('multiselect');
?>
<!-- Header row -->
<tr>
    <td class="w-1 text-center">
        <?= HTMLHelper::_('grid.checkall') ?>
    </td>
    <th scope="col">
        <?= HTMLHelper::_('searchtools.sort', 'COM_JCHOPTIMIZE_PAGECACHE_MTIME', 'mtime', $listDir, $listOrder); ?>
    </th>
    <th>
        <?= HTMLHelper::_('searchtools.sort', 'COM_JCHOPTIMIZE_PAGECACHE_URL', 'url', $listDir, $listOrder); ?>
    </th>
    <th class="text-center">
        <?= HTMLHelper::_('searchtools.sort', 'COM_JCHOPTIMIZE_PAGECACHE_DEVICE', 'device', $listDir, $listOrder); ?>
    </th>
    <th>
        <?= HTMLHelper::_('searchtools.sort', 'COM_JCHOPTIMIZE_PAGECACHE_ADAPTER', 'adapter', $listDir, $listOrder); ?>
    </th>
    <th>
        <?= HTMLHelper::_(
            'searchtools.sort',
            'COM_JCHOPTIMIZE_PAGECACHE_HTTP_REQUEST',
            'http-request',
            $listDir,
            $listOrder
        ); ?>
    </th>
    <th class="d-none d-lg-table-cell d-xl-table-cell d-xxl-table-cell">
        <?= HTMLHelper::_('searchtools.sort', 'COM_JCHOPTIMIZE_PAGECACHE_ID', 'id', $listDir, $listOrder); ?>
    </th>
</tr>
