<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

extract($displayData);

/**
 * @var FormField $field The form field
 * @var Form $form
 * @var string $name Name of field
 */

$field->layout = 'joomla.form.field.list';
?>

<div id="div-<?= $field->fieldname ?>">
    <?= $field->input; ?>
        <button type="button"
                class="btn btn-sm btn-secondary jch-multiselect-add-button">
            Add item
        </button>
</div>