<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

// require HelixUltimate\Framework\Platform\HTMLOverride::loadTemplate();

// =============================
// Получаем изображения безопасно
$images = null;
$imageIntro = '';
$imageFull = '';

// В Joomla 6 images может быть: объектом, массивом или JSON-строкой
if (isset($this->item->images)) {
    if (is_object($this->item->images)) {
        // Если это объект
        $images = $this->item->images;
        $imageIntro = $images->image_intro ?? '';
        $imageFull = $images->image_fulltext ?? '';
    } elseif (is_array($this->item->images)) {
        // Если это массив
        $imageIntro = $this->item->images['image_intro'] ?? '';
        $imageFull = $this->item->images['image_fulltext'] ?? '';
    } elseif (is_string($this->item->images) && !empty($this->item->images)) {
        // Если это JSON-строка (старый формат)
        $images = json_decode($this->item->images);
        if ($images) {
            $imageIntro = $images->image_intro ?? '';
            $imageFull = $images->image_fulltext ?? '';
        }
    }
}

// Получаем остальные параметры безопасно
$date = JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC3'));
$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
?>

<div class="custom-item news-item h-100">
    <!-- Интро-изображение -->
    <?php if ($imageIntro) : ?>
        <div class="item-image news-image">
            <a href="<?php echo $link; ?>">
                <img src="<?php echo $imageIntro; ?>" 
                alt="<?php echo $this->item->title; ?>"
                loading="lazy">
            </a>
        </div>
    <?php endif; ?>
        
    
    <div class="news-content">
        <!-- Дата -->
        <a href="<?php echo $link; ?>">
            <div class="item-meta news-date">
                <span class="date"><?php echo $date; ?></span>
            </div>
            <!-- Заголовок -->
            <h2 class="news-title">
                    <?php echo $this->item->title; ?>
                
            </h2>
            <!-- Интротекст -->
            <div class="intro news-intro">
                <?php echo $this->item->introtext; ?>
            </div>
        </a>
        
        <!-- Ссылка "Подробнее" -->
        <!-- <a href="<?php echo $link; ?>" class="read-more">
            Подробнее →
        </a> -->
    </div>
    
    <!-- Полное изображение (опционально) -->
    <?php if ($imageFull) : ?>
        <!-- <div class="item-full-image" style="display:none;">
            <img src="<?php echo $imageFull; ?>" 
                 alt="<?php echo $this->item->title; ?>">
        </div> -->
    <?php endif; ?>
</div>
