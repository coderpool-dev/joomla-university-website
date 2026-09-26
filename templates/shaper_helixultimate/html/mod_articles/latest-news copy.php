<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$moduleclass_sfx = $params->get('moduleclass_sfx');
$moduleclass_sfx = !empty($moduleclass_sfx) ? htmlspecialchars((string)$moduleclass_sfx, ENT_COMPAT, 'UTF-8') : '';

$items = $list;
?>

<div class="mod-articles-latest-custom mod-articles-latest-custom-<?php echo $moduleclass_sfx; ?>">
    <?php if (!empty($items)) : ?>
        <div class="news-list" style="display: flex; flex-direction: row; align-items: flex-start; justify-content: space-between; flex-wrap: wrap;">
            <?php foreach ($items as $item) : 
                // ПРАВИЛЬНОЕ ПОЛУЧЕНИЕ КАРТИНОК для Joomla 6
                $image_intro = '';
                $image_intro_alt = $item->title;
                
                // Способ 1: через images поле
                if (!empty($item->images)) {
                    $images = json_decode($item->images);
                    if ($images && isset($images->image_intro) && !empty($images->image_intro)) {
                        $image_intro = $images->image_intro;
                        $image_intro_alt = isset($images->image_intro_alt) ? $images->image_intro_alt : $item->title;
                    }
                }
                
                // Способ 2: если есть поле image (альтернативный вариант)
                if (empty($image_intro) && !empty($item->image)) {
                    $image_intro = $item->image;
                }
                
                // Способ 3: через поле intro_image
                if (empty($image_intro) && !empty($item->intro_image)) {
                    $image_intro = $item->intro_image;
                }

                // if (empty($image_intro)) {
                //     $reg = $item->params;
                //     $image_intro = $reg->data->helix_iltimate_image;
                // }
                
                $article_url = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
            ?>
                <div class="news-item" style="min-width: 300px;">
                    <?php if (!empty($image_intro)) : ?>
                        <div class="news-image">
                            <a href="<?php echo $article_url; ?>">
                                <div style="width: 100%; height: 150px; background-image: url('<?php echo htmlspecialchars((string)$image_intro, ENT_QUOTES, 'UTF-8'); ?>');">
                                </div>
                            </a>
                        </div>
                    <?php else : ?>
                        <!-- Временно для отладки - покажет, если картинки нет -->
                        <div class="news-image" style="background:#eee; width:100px; height:100px; display:flex; align-items:center; justify-content:center; font-size:12px; color:#999;">
                            Нет фото
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
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div><?php echo Text::_('MOD_ARTICLES_LATEST_NO_ARTICLES'); ?></div>
    <?php endif; ?>
</div>