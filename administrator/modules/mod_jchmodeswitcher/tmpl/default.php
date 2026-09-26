<?php

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

defined('_JEXEC') or die('Restricted Access');

/**
 * @var string $mode
 * @var string $url
 * @var string $pageCacheStatus
 * @var string $pageCachePluginTitle
 * @var string $task
 * @var string $statusClass
 * @var CMSApplication $app
 * @var Input $input
 */
$uri = Uri::getInstance();

// Load the Bootstrap Dropdown
HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

if ($input->getBool('hidemainmenu')) {
    return;
}

$options = [
    'version' => JCH_VERSION
];

$wa = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_jchoptimize')
    ->addExtensionRegistryFile('mod_jchmodeswitcher');
$wa->useStyle('mod_jchmodeswitcher.modeswitcher')
    ->useScript('com_jchoptimize.platform-joomla');
$script = <<<JS

window.addEventListener('DOMContentLoaded', event => {
    const modeSwitcherButton = document.getElementById('jch-modeswitcher-toggle')
    if (modeSwitcherButton !== null){
        modeSwitcherButton.addEventListener('show.bs.dropdown', event => {
            jchPlatform.getCacheInfo();
        });
    }
});
JS;
$wa->addInlineScript($script);
?>
<div class="header-item-content dropdown header-profile jch-modeswitcher">
    <button id="jch-modeswitcher-toggle" class="dropdown-toggle d-flex align-items-center ps-0 py-0"
            data-bs-toggle="dropdown" type="button"
            title="<?php
            echo Text::_('MOD_JCHMODESWITCHER'); ?>">
        <div class="header-item-icon">
                <span id="mode-switcher-indicator"
                      class="fa-dot-circle fas d-flex notification-icon <?= $statusClass; ?>"
                      aria-hidden="true"></span>
        </div>
        <div class="header-item-text">
            <?= Text::_('MOD_JCHMODESWITCHER_TITLE'); ?>
        </div>
        <span class="icon-angle-down" aria-hidden="true"></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        <?php
        $route = 'index.php?option=com_jchoptimize&view=ModeSwitcher&task=' . $task . '&return=' . base64_encode(
            (string)$uri
        ); ?>
        <a class="dropdown-item" href="<?= Route::_($route); ?>">
            <span class="icon-cog icon-fw" aria-hidden="true"></span>
            <?= Text::sprintf('MOD_JCHMODESWITCHER_MODE', $mode); ?>
        </a>
        <?php
        $route = 'index.php?option=com_jchoptimize&view=Utility&task=togglepagecache&return=' . base64_encode(
            (string)$uri
        ); ?>
        <a class="dropdown-item" href="<?= Route::_($route); ?>">
            <span class="icon-archive icon-fw" aria-hidden="true"></span>
            <span id="page-cache-status">
                    <?= Text::sprintf('MOD_JCHMODESWITCHER_PAGECACHE_STATUS', $pageCacheStatus); ?>
                </span>
        </a>
        <div class="dropdown-item-text text-nowrap jch-card-wrapper">
            <div class="jch-cache-card p-3">
                <div class="jch-cache-card-header d-flex align-items-center mb-2">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <span class="icon-info-circle icon-fw me-2" aria-hidden="true"></span>
                            <div class="jch-cache-card-title">
                                <em><?= Text::_('MOD_JCHMODESWITCHER_CACHE_INFO'); ?></em>
                            </div>
                        </div>
                        <div class="jch-cache-card-subtitle small text-white-50">
                            <?= $pageCachePluginTitle ?>
                        </div>
                    </div>
                </div>

                <div class="jch-cache-card-body d-flex justify-content-between gap-3">
                    <div class="jch-cache-metric flex-fill">
                        <div class="jch-cache-metric-label text-white-50 small">
                            <?= Text::_('MOD_JCHMODESWITCHER_FILES'); ?>
                        </div>
                        <div class="jch-cache-metric-value numFiles-container fw-semibold">
                            <img src="<?= Uri::root(true) . '/media/com_jchoptimize/core/images/loader.gif'; ?>"
                                 alt=""
                            >
                        </div>
                    </div>

                    <div class="jch-cache-metric flex-fill">
                        <div class="jch-cache-metric-label text-white-50 small">
                            <?= Text::_('MOD_JCHMODESWITCHER_SIZE'); ?>
                        </div>
                        <div class="jch-cache-metric-value fileSize-container fw-semibold">
                            <img src="<?= Uri::root(true) . '/media/com_jchoptimize/core/images/loader.gif'; ?>"
                                 alt=""
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $route = 'index.php?option=com_jchoptimize&view=Utility&task=cleancache&return=' . base64_encode(
            $uri
        ); ?>
        <a class="dropdown-item" href="<?= Route::_($route); ?>">
            <span class="fa-trash-alt fas icon-fw" aria-hidden="true"></span>
            <?= Text::_('MOD_JCHMODESWITCHER_DELETE_CACHE'); ?>
        </a>
    </div>
</div>
