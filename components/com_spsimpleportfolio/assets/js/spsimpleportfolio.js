/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
jQuery(window).on("load",(function(){var e=jQuery(".sp-simpleportfolio-items"),i=e.find(".shuffle__sizer"),s=0;jQuery(".sp-simpleportfolio-items .sp-simpleportfolio-item").each((function(){var e=jQuery(this).find(".sp-simpleportfolio-info").height();e>s&&(s=e)})),jQuery(".sp-simpleportfolio-info").height(s),e.shuffle({itemSelector:".sp-simpleportfolio-item",sequentialFadeDelay:150,sizer:i}),jQuery(".sp-simpleportfolio-filter li a").on("click",(function(e){e.preventDefault();var i=jQuery(this),s=jQuery(this).parent();s.hasClass("active")||(i.closest("ul").children().removeClass("active"),i.parent().addClass("active"),i.closest(".sp-simpleportfolio").children(".sp-simpleportfolio-items").shuffle("shuffle",s.data("group")))}))}));