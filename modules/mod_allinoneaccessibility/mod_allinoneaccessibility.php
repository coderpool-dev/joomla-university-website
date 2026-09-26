<?php
/**
 * @package     All in One Accessibility®
 * @author      Skynet Technologies USA LLC.
 * @copyright   (C) 2025 - Skynet Technologies USA LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 **/


// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// Render module layout
require ModuleHelper::getLayoutPath('mod_allinoneaccessibility', $params->get('layout', 'output'));

?>



