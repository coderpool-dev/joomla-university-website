<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Session\Session;
use Joomla\Session\SessionInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects


$DIR = dirname(__FILE__, 4);

if (! file_exists($DIR . '/includes/defines.php')) {
    //Try using $_SERVER, PHP automatically resolves symlinks, and it seems there's no way to prevent that.
    $DIR = dirname($_SERVER['SCRIPT_FILENAME'], 4);
}

define('JPATH_BASE', $DIR);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Boot the DI container
$container = Factory::getContainer();

/*
 * Alias the session service keys to the web session service as that is the primary session backend for this application
 *
 * In addition to aliasing "common" service keys, we also create aliases for the PHP classes to ensure autowiring objects
 * is supported.  This includes aliases for aliased class names, and the keys for aliased class names should be considered
 * deprecated to be removed when the class name alias is removed as well.
 */
$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(Session::class, 'session.web.site')
    ->alias(SessionInterface::class, 'session.web.site');

// Instantiate the application.
$app = $container->get(SiteApplication::class);
$app->createExtensionNamespaceMap();

// Set the application as global app
Factory::$application = $app;

require_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';
