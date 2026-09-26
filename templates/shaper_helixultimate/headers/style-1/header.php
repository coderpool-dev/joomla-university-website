<?php

/**
 * @package Helix_Ultimate_Framework
 * @author JoomShaper <support@joomshaper.com>
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

defined('_JEXEC') or die('Restricted Access');

use HelixUltimate\Framework\Platform\Helper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

$data               = $displayData;
$offcanvas_position = $data->params->get('offcanvas_position', 'right');
$menu_type          = $data->params->get('menu_type');

$feature_folder_path = JPATH_THEMES . '/' . $data->template->template . '/features';

include_once $feature_folder_path . '/social.php';
include_once $feature_folder_path . '/contact.php';
include_once $feature_folder_path . '/logo.php';
include_once $feature_folder_path . '/menu.php';

/**
 * Helper classes for-
 * social icons, contact info, site logo, Menu header.
 *
 */
$social 	= new HelixUltimateFeatureSocial($data->params);
$contact 	= new HelixUltimateFeatureContact($data->params);
$logo    	= new HelixUltimateFeatureLogo($data->params);
$menu    	= new HelixUltimateFeatureMenu($data->params);

/** Logo and menu html classes */
$logoClass = 'col-auto';
$menuClass = 'col-auto flex-auto';

/**
 * Get related modules
 * The modules are mod_search
 */
$searchModule    = Helper::getSearchModule('-header');
$visibilityClass = ($menu_type === 'mega') ? 'd-flex d-lg-none' : 'd-flex';
$sideClass       = ($offcanvas_position === 'left') ? 'offcanvas-toggler-left' : 'offcanvas-toggler-right';
$togglerHtml     = '
  <a id="offcanvas-toggler"
     class="offcanvas-toggler-secondary ' . $sideClass . ' ' . $visibilityClass . ' align-items-center"
     href="#"
     aria-label="' . Text::_('HELIX_ULTIMATE_NAVIGATION') . '"
     title="' . Text::_('HELIX_ULTIMATE_NAVIGATION') . '">
     <div class="burger-icon" aria-hidden="true"><span></span><span></span><span></span></div>
  </a>';
?>

<?php if ($data->params->get('sticky_header')): ?>
	<div class="sticky-header-placeholder"></div>
<?php endif; ?>

<div id="sp-top-bar">
	<div class="container">
		<div class="container-inner">
			<div class="row">
              <div class="col-lg-3">
<div id="vision-panel" class="" style="position: relative; top: 0px; left: 0px; z-index: 10000; display: flex; gap: 10px;">
    <button id="vision-on" style="padding: 0px 0px; background: #004685; color: #fff; border: 0px solid #ff0; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: bold; box-shadow: 0 2px 10px rgba(0,0,0,0.0);">
        ВЕРСИЯ ДЛЯ СЛАБОВИДЯЩИХ
    </button>
    <button id="vision-off" style="padding: 10px 10px; background: #444; color: #fff; border: 2px solid #ff0; border-radius: 8px; cursor: pointer; font-size: 16px; display: none; font-weight: bold;">
        🔄 ОБЫЧНАЯ ВЕРСИЯ
    </button>
</div>

<style>
/* РЕЖИМ СЛАБОВИДЯЩИХ — ПРИНУДИТЕЛЬНОЕ ПЕРЕОПРЕДЕЛЕНИЕ */
body.vision-mode,
body.vision-mode * {
    background-color: #000000 !important;
    background-image: none !important;
    color: #FFFF00 !important;
    border-color: #FFFF00 !important;
    box-shadow: none !important;
    text-shadow: none !important;
}

/* Отдельно для ссылок */
body.vision-mode a,
body.vision-mode a * {
    color: #00FFFF !important;
    text-decoration: underline !important;
}

/* Отдельно для инпутов, кнопок, селектов */
body.vision-mode button,
body.vision-mode input,
body.vision-mode select,
body.vision-mode textarea,
body.vision-mode .btn,
body.vision-mode .button {
    background-color: #000000 !important;
    color: #FFFF00 !important;
    border: 1px solid #FFFF00 !important;
}

/* Увеличенный шрифт для всего */
body.vision-mode,
body.vision-mode * {
    font-size: 18px !important;
    line-height: 1.6 !important;
}

body.vision-mode h1 { font-size: 34px !important; }
body.vision-mode h2 { font-size: 30px !important; }
body.vision-mode h3 { font-size: 26px !important; }
body.vision-mode h4 { font-size: 22px !important; }
body.vision-mode h5 { font-size: 20px !important; }
body.vision-mode h6 { font-size: 18px !important; }

/* Отключаем тени, градиенты, прозрачности */
body.vision-mode * {
    opacity: 1 !important;
    filter: none !important;
    backdrop-filter: none !important;
}

/* Картинки делаем контрастнее */
body.vision-mode img {
    filter: contrast(150%) brightness(0.9) !important;
    opacity: 0.95 !important;
}
</style>

<script>
(function() {
    // Включение режима
    function enableVisionMode() {
        document.body.classList.add('vision-mode');
        var onBtn = document.getElementById('vision-on');
        var offBtn = document.getElementById('vision-off');
        if (onBtn) onBtn.style.display = 'none';
        if (offBtn) offBtn.style.display = 'inline-block';
        localStorage.setItem('visionMode', 'enabled');
    }

    // Выключение режима
    function disableVisionMode() {
        document.body.classList.remove('vision-mode');
        var onBtn = document.getElementById('vision-on');
        var offBtn = document.getElementById('vision-off');
        if (onBtn) onBtn.style.display = 'inline-block';
        if (offBtn) offBtn.style.display = 'none';
        localStorage.setItem('visionMode', 'disabled');
    }

    // Инициализация кнопок
    function init() {
        var onBtn = document.getElementById('vision-on');
        var offBtn = document.getElementById('vision-off');
        
        if (onBtn && offBtn) {
            onBtn.onclick = enableVisionMode;
            offBtn.onclick = disableVisionMode;
            
            if (localStorage.getItem('visionMode') === 'enabled') {
                enableVisionMode();
            }
        } else {
            setTimeout(init, 100);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
              </div>
				<div id="sp-top1" class="col-lg-3">
					<div class="sp-column text-center text-lg-start">
						<?php if ($data->params->get('social_position') === 'top1'): ?>
							<?php echo $social->renderFeature(); ?>
						<?php endif ?>

						<?php if ($data->params->get('contact_position') === 'top1'): ?>
							<?php echo $contact->renderFeature(); ?>
						<?php endif ?>
						<jdoc:include type="modules" name="top1" style="sp_xhtml"/>
					</div>
				</div>

				<div id="sp-top2" class="col-lg-6">
					<div class="sp-column text-center text-lg-end">
						<?php if ($data->params->get('social_position') === 'top2'): ?>
							<?php echo $social->renderFeature(); ?>
						<?php endif ?>

						<?php if ($data->params->get('contact_position') === 'top2'): ?>
							<?php echo $contact->renderFeature(); ?>
						<?php endif ?>
						<jdoc:include type="modules" name="top2" style="sp_xhtml" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<header id="sp-header">
	<div class="container">
		<div class="container-inner">
			<div class="row align-items-center">

				<!-- Left toggler if left/offcanvas -->
				<?php if ($offcanvas_position === 'left' ): ?>
					<div class="col-auto d-flex align-items-center">
						<?php echo $togglerHtml; ?>
					</div>
				<?php endif; ?>

				<!-- Logo -->
				<div id="sp-logo" class="<?php echo $logoClass; ?>">
					<div class="sp-column">
						<?php echo $logo->renderFeature(); ?>
						<jdoc:include type="modules" name="logo" style="sp_xhtml" />
					</div>
				</div>

				<!-- Menu -->
				<div id="sp-menu" class="<?php echo $menuClass; ?>">
					<div class="sp-column d-flex justify-content-end align-items-center">
						<?php echo $menu->renderFeature(); ?>
						<jdoc:include type="modules" name="menu" style="sp_xhtml" />

						<!-- Related Modules -->
						<div class="d-none d-lg-flex header-modules align-items-center">
							<?php if ($data->params->get('enable_search', 0)): ?>
								<?php echo ModuleHelper::renderModule($searchModule, ['style' => 'sp_xhtml']); ?>
							<?php endif ?>

							<?php if ($data->params->get('enable_login', 0)): ?>
								<?php echo $menu->renderLogin(); ?>
							<?php endif ?>
						</div>

						<!-- Right toggler  -->
						<?php if ($offcanvas_position === 'right'): ?>
							<?php echo $togglerHtml; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>
