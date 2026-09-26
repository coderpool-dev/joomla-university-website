<?php

/**
 * @var $app CMSApplicationInterface
 */

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_jchoptimize');
$wa->useStyle('com_jchoptimize.core.dashicons')
    ->useStyle('com_jchoptimize.dashicons-joomla')
    ->useStyle('fontawesome')
    ->useScript('com_jchoptimize.platform-joomla');

$options = [
    'trigger' => 'hover focus',
    'placement' => 'right',
    'html' => true
];

HTMLHelper::_('bootstrap.popover', '.hasPopover', $options);
/**
 * @var array $buttons
 * @var Registry $params
 * @var $module
 */


if ($params->get('context') == 'automatic') :
    $button = array_pop($buttons);
    ?>
    <div class="jch-dash-icons-switcher ms-3 mb-3 px-1">
        <div id="<?= $button['id'] ?>" <?= $button['script']; ?>>
            <div class="form-check form-switch">
                <label class="form-check-label fs-6" for="flexSwitchCheckDefault">
                    <?= $button['enabled'] ? 'Enabled' : 'Disabled'; ?>
                </label>
                <input class="form-check-input" type="checkbox"
                    <?= $button['enabled'] ? 'checked' : ''; ?> id="flexSwitchCheckDefault">
            </div>
        </div>
    </div>
    <?php
endif;
$html = HTMLHelper::_('dashicons.buttons', $buttons);
?>
    <nav class="quick-icons px-3 pb-3">
        <ul class="nav flex-wrap">
            <?= $html; ?>
        </ul>
    </nav>
<?php
if ($params->get('context') == 'utility') :
    $wa->useStyle('com_jchoptimize.bulksettings')
        ->useScript('com_jchoptimize.core.file-upload');
    require ModuleHelper::getLayoutPath($module->module, 'bulk_settings');
endif;

