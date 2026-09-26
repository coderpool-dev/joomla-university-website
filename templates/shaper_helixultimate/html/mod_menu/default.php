<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

// require HelixUltimate\Framework\Platform\HTMLOverride::loadTemplate();
?>

<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$attributes = array();

if ($params->get('bootstrap_version', '5') == '5') {
    // Меняем ВНЕШНИЙ КОНТЕЙНЕР: вместо <ul class="nav menu">
    // Сделаем кастомный <nav> и внутренний <ul>
    
    // ВАШ КОД: Начинаем вывод
    echo '<nav class="my-super-navigation">';
    echo '<ul class="my-custom-ul">';
    
    foreach ($list as $i => &$item)
    {
        $itemClasses = array();
        
        // ВАШ КОД: Меняем класс у li
        $itemClasses[] = 'my-menu-item';
        
        if ($item->id == $active_id) {
            $itemClasses[] = 'my-current';
        }
        
        $class = 'class="' . implode(' ', $itemClasses) . '"';
        
        echo '<li ' . $class . '>';
        
        // Вывод ссылки
        switch ($item->type) :
            case 'heading':
                // ... ваш код для заголовка
                break;
            default:
                $linkClasses = 'my-nav-link';
                echo '<a class="' . $linkClasses . '" href="' . $item->flink . '">' . $item->title . '</a>';
                // echo '<a class="' . $linkClasses . '" href="' . str_replace(".html", "", $item->flink) . '">' . $item->title . '</a>';
                break;
        endswitch;
        
        if ($item->deeper) {
            echo '<ul class="my-submenu">'; // меняем класс подменю
        }
        
        if ($item->shallower) {
            echo '</ul>';
        }
        
        echo '</li>';
    }
    
    echo '</ul>';
    echo '</nav>';
}

