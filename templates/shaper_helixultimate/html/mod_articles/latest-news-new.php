<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 * Carousel version with Swiper
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$moduleclass_sfx = $params->get('moduleclass_sfx');
$moduleclass_sfx = !empty($moduleclass_sfx) ? htmlspecialchars((string)$moduleclass_sfx, ENT_COMPAT, 'UTF-8') : '';

$items = $list;
$module_id = $module->id; // Уникальный ID для каждого модуля
?>

<!-- Подключаем Swiper CSS и JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<div class="mod-articles-carousel mod-articles-carousel-<?php echo $moduleclass_sfx; ?>">
    <?php if (!empty($items)) : ?>
        <div class="swiper-container" id="newsSwiper-<?php echo $module_id; ?>">
            <div class="swiper-wrapper">
                <?php foreach ($items as $item) : 
                    // Получение картинок
                    $image_intro = '';
                    $image_intro_alt = $item->title;
                    
                    if (!empty($item->images)) {
                        $images = json_decode($item->images);
                        if ($images && isset($images->image_intro) && !empty($images->image_intro)) {
                            $image_intro = $images->image_intro;
                            $image_intro_alt = isset($images->image_intro_alt) ? $images->image_intro_alt : $item->title;
                        }
                    }
                    
                    if (empty($image_intro) && !empty($item->image)) {
                        $image_intro = $item->image;
                    }
                    
                    if (empty($image_intro) && !empty($item->intro_image)) {
                        $image_intro = $item->intro_image;
                    }
                    
                    $article_url = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
                ?>
                    <div class="swiper-slide">
                        <div class="news-item h-100">
                            <?php if (!empty($image_intro)) : ?>
                                <div class="news-image">
                                    <a href="<?php echo $article_url; ?>">
                                        <img src="<?php echo htmlspecialchars((string)$image_intro, ENT_QUOTES, 'UTF-8'); ?>" 
                                             alt="<?php echo htmlspecialchars((string)$image_intro_alt, ENT_QUOTES, 'UTF-8'); ?>"
                                             loading="lazy">
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="news-image no-image">
                                    <a href="<?php echo $article_url; ?>">
                                        <div class="placeholder-image">
                                            📷 Нет фото
                                        </div>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="news-content">
                                <div class="news-date">
                                    <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC3')); ?>
                                </div>
                                
                                <h4 class="news-title">
                                    <a href="<?php echo $article_url; ?>">
                                        <?php echo htmlspecialchars((string)$item->title, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h4>
                                
                                <?php if (!empty($item->introtext)) : ?>
                                    <div class="news-intro">
                                        <?php echo HTMLHelper::_('string.truncate', strip_tags($item->introtext), 100); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Навигация -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            
            <!-- Пагинация (точки) -->
            <div class="swiper-pagination"></div>
        </div>
    <?php else : ?>
        <div><?php echo Text::_('MOD_ARTICLES_LATEST_NO_ARTICLES'); ?></div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('#newsSwiper-<?php echo $module_id; ?>', {
        // Основные настройки
        slidesPerView: 1,          // Базово 1 слайд
        spaceBetween: 20,          // Расстояние между слайдами
        slidesPerGroup: 1,         // 🔥 ПРОКРУТКА ПО 1 ШТУКЕ 🔥
        
        // Адаптивность (динамическая)
        breakpoints: {
            // Когда ширина экрана >= 640px
            640: {
                slidesPerView: 1.5,
                spaceBetween: 15
            },
            // Когда ширина экрана >= 768px (планшеты)
            768: {
                slidesPerView: 2,
                spaceBetween: 20
            },
            // Когда ширина экрана >= 1024px (ноутбуки)
            1024: {
                slidesPerView: 3,
                spaceBetween: 25
            },
            // Когда ширина экрана >= 1280px (ПК)
            1280: {
                slidesPerView: 4,
                spaceBetween: 30
            }
        },
        
        // Навигация
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        
        // Пагинация
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        
        // Дополнительные фишки
        loop: <?php echo count($items) >= 5 ? 'true' : 'false'; ?>, // Бесконечная прокрутка, если >=5 новостей
        autoplay: {
            delay: 5000,
            disableOnInteraction: false, // Не отключать автопрокрутку после взаимодействия
        },
        speed: 800,                     // Скорость анимации
        grabCursor: true,               // Курсор-рука
        mousewheel: {
            forceToAxis: true,          // Скролл мышью по горизонтали
        },
        keyboard: {
            enabled: true,              // Управление с клавиатуры
            onlyInViewport: true,
        },
        
        // Адаптивная высота
        autoHeight: true,
        
        // Эффекты (опционально)
        effect: 'slide', // или 'fade', 'coverflow', 'creative'
        
        // Отзывчивость на ресайз окна (динамическая адаптация)
        resizeObserver: true,
        updateOnWindowResize: true,
    });
});
</script>