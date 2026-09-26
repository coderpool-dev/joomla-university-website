<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2024 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted Access');

if (!empty($this->items)) :
    foreach ($this->items as $row) :
        ?>
<tr>
        <?php
        if (!empty($row->name) && !empty($row->value)) :
            ?>
    <th scope="row"><input type="checkbox" name="<?= $row->name; ?>" value="<?= $row->value; ?>"
                           class="jchoptimize-criticalJs-configure-helper"/></th>
                           <?php
        endif;
        ?>
    <td><?= $row->displayValue; ?></td>
        <?php
        if (isset($row->type)) :
            ?>
    <td><?= $row->type; ?></td>
            <?php
        endif;
        ?>
</tr>
        <?php
    endforeach;
else :
    ?>
<tr>
    <td colspan="3"><?= Text::_('JCHOPTIMIZE_CRITICALJS_CONFIGURE_HELPER_NO_SCRIPTS'); ?></td>
</tr>
    <?php
endif;