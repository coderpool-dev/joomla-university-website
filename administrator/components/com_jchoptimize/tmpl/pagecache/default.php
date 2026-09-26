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
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die('Restricted Access');

/** @var HtmlView $this */

?>
<?php
if (!JCH_PRO) : ?>
    <script>
        document.querySelector('#toolbar-share button.button-share').disabled = true;
    </script>
    <?php
endif; ?>

<!-- Administrator form for browse views -->
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <!-- Filters and ordering -->
        <?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <?php
        if (!count($this->items)) : ?>
            <!-- No records -->
            <?= $this->loadTemplate('norecords');        ?>
            <?php
        else : ?>
            <div style="overflow-x:auto">
                <table class="table table-hover" id="itemsList">
                    <thead>
                    <!-- Table header -->
                    <?= $this->loadTemplate('table_header');         ?>
                    </thead>
                    <tbody>
                    <!--Table body when records are present -->
                    <?= $this->loadTemplate('withrecords'); ?>
                    </tbody>
                </table>
                <?= $this->pagination->getListFooter(); ?>
            </div>
            <?php
        endif; ?>

        <!-- Hidden form fields -->
        <div>
            <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
            <input type="hidden" name="option" id="option" value="com_jchoptimize"/>
            <input type="hidden" name="view" id="view" value="PageCache"/>
            <input type="hidden" name="task" id="task" value=""/>
            <?= HTMLHelper::_('form.token'); ?>
        </div>
    </div>
</form>