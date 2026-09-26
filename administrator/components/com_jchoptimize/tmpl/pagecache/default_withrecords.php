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
?>
<?php foreach ($this->items as $i => $item) : ?>
    <tr class="row<?= $i ?>">
        <td>
           <?= HTMLHelper::_('grid.id', $i, $item['id']); ?>
        </td>
        <td>
            <?= date('l, F d, Y h:i:s A', $item['mtime']); ?> GMT
        </td>
        <td>
            <a title="<?= $item['url'] ?>" href="<?= $item['url']; ?>" class="page-cache-url"
               target="_blank"><?= $item['url']; ?></a>
        </td>
        <td style="text-align: center;">
            <?php if ($item['device'] == 'Desktop') : ?>
                <span class="fa fa-desktop" data-bs-toggle="tooltip"
                      title="<?= $item['device']; ?>"></span>
            <?php else : ?>
                <span class="fa fa-mobile-alt" data-bs-toggle="tooltip"
                      title="<?= $item['device']; ?>"></span>
            <?php endif; ?>
        </td>
        <td>
            <?= $item['adapter']; ?>
        </td>
        <td style="text-align: center;">
            <?php if ($item['http-request'] == 'yes') : ?>
                <span class="fa fa-check-circle" style="color: green;"></span>
            <?php else : ?>
                <span class="fa fa-times-circle" style="color: firebrick;"></span>
            <?php endif; ?>
        </td>
        <td class="d-none d-lg-table-cell d-xl-table-cell d-xxl-table-cell">
            <span class="page-cache-id"><?= $item['id']; ?></span>
        </td>
    </tr>
<?php endforeach; ?>