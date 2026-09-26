<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 * Helix Ultimate - Responsive Carousel Layout
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$moduleclass_sfx = $params->get('moduleclass_sfx');
$moduleclass_sfx = !empty($moduleclass_sfx) ? htmlspecialchars((string)$moduleclass_sfx, ENT_COMPAT, 'UTF-8') : '';

$items = $list;
$module_id = $module->id;

// Настройка количества новостей на разных устройствах
$itemsPerSlide = [
    'xs' => 1,  // мобильные (до 576px) - 1 новость
    'sm' => 2,  // планшеты (576px - 768px) - 2 новости
    'md' => 3,  // десктопы (768px - 992px) - 3 новости
    'lg' => 3,  // большие экраны (992px - 1200px) - 3 новости
    'xl' => 4   // очень большие (1200px+) - 4 новости
];

// Получаем максимальное количество на слайд (для десктопа)
$maxPerSlide = $itemsPerSlide['xl'];

// Разбиваем новости на группы
$chunks = array_chunk($items, $maxPerSlide);
?>

<div class="mod-articles-latest-carousel mod-articles-latest-carousel-<?php echo $moduleclass_sfx; ?>">
    <?php if (!empty($items)) : ?>
        <div id="newsCarousel-<?php echo $module_id; ?>" class="carousel slide" data-bs-ride="carousel">
            
            <!-- Индикаторы -->
            <div class="carousel-indicators">
                <?php foreach ($chunks as $index => $chunk) : ?>
                    <button type="button" 
                            data-bs-target="#newsCarousel-<?php echo $module_id; ?>" 
                            data-bs-slide-to="<?php echo $index; ?>" 
                            <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                            aria-label="Slide <?php echo $index + 1; ?>">
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Слайды -->
            <div class="carousel-inner">
                <?php foreach ($chunks as $index => $chunk) : ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row g-4">
                            <?php foreach ($chunk as $item) : 
                                // Получаем картинку
                                $image_intro = '';
                                $image_intro_alt = $item->title;
                                
                                // Пробуем получить картинку из стандартного поля
                                if (!empty($item->images)) {
                                    $images = json_decode($item->images, true);
                                    if (is_array($images) && !empty($images['image_intro'])) {
                                        $image_intro = $images['image_intro'];
                                        $image_intro_alt = !empty($images['image_intro_alt']) ? $images['image_intro_alt'] : $item->title;
                                    }
                                }
                                
                                // Если нет, ищем в Helix полях
                                if (empty($image_intro) && isset($item->jcfields)) {
                                    foreach ($item->jcfields as $field) {
                                        if (strpos($field->name, 'image') !== false || strpos($field->name, 'picture') !== false) {
                                            if (!empty($field->value)) {
                                                $value = json_decode($field->value, true);
                                                if (is_array($value) && !empty($value['image'])) {
                                                    $image_intro = $value['image'];
                                                    break;
                                                } elseif (is_string($field->value) && !empty($field->value)) {
                                                    $image_intro = $field->value;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                $article_url = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
                            ?>
                                <div class="col-<?php echo 12 / $itemsPerSlide['xs']; ?> 
                                            col-sm-<?php echo 12 / $itemsPerSlide['sm']; ?> 
                                            col-md-<?php echo 12 / $itemsPerSlide['md']; ?> 
                                            col-lg-<?php echo 12 / $itemsPerSlide['lg']; ?> 
                                            col-xl-<?php echo 12 / $itemsPerSlide['xl']; ?>">
                                    <div class="news-card">
                                        <?php if (!empty($image_intro)) : ?>
                                            <div class="news-card-image">
                                                <a href="<?php echo $article_url; ?>">
                                                    <img src="<?php echo htmlspecialchars((string)$image_intro, ENT_QUOTES, 'UTF-8'); ?>" 
                                                         alt="<?php echo htmlspecialchars((string)$image_intro_alt, ENT_QUOTES, 'UTF-8'); ?>"
                                                         class="w-100">
                                                </a>
                                            </div>
                                        <?php else : ?>
                                            <div class="news-card-image no-image">
                                                <div class="no-image-text">
                                                    <?php echo Text::_('MOD_ARTICLES_LATEST_NO_IMAGE'); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="news-card-content">
                                            <div class="news-date">
                                                <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC3')); ?>
                                            </div>
                                            <h3 class="news-title">
                                                <a href="<?php echo $article_url; ?>">
                                                    <?php echo htmlspecialchars((string)$item->title, ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Кнопки навигации -->
            <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel-<?php echo $module_id; ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel-<?php echo $module_id; ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <?php else : ?>
        <div class="alert alert-info">
            <?php echo Text::_('MOD_ARTICLES_LATEST_NO_ARTICLES'); ?>
        </div>
    <?php endif; ?>
</div>