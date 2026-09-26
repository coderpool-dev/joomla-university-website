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

defined('_JEXEC') or die('Restricted access');

extract($displayData);

?>
<div>
<button type="button" class="btn btn-primary" id="criticalJsModalLaunchButton">
Open
</button>
</div>
<style>
.modal{
--modal-width: 75% !important;
}
</style>

<?php
include  __DIR__ . '/critical_js_configure_helper.php';
