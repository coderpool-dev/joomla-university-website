<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2024 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted Access');

if (!class_exists('_JchOptimizeVendor\V91\Joomla\Input\Input', false)) {
    class_alias(Input::class, '\\_JchOptimizeVendor\\V91\\Joomla\\Input\\Input');
}

if (!class_exists('_JchOptimizeVendor\V91\Joomla\Filesystem\File', false)) {
    class_alias(File::class, '\\_JchOptimizeVendor\\V91\\Joomla\\Filesystem\\File');
}

if (!class_exists('_JchOptimizeVendor\V91\Joomla\Registry\Registry', false)) {
    class_alias(Registry::class, '\\_JchOptimizeVendor\\V91\\Joomla\\Registry\\Registry');
}

if (!class_exists('_JchOptimizeVendor\V91\Joomla\Filesystem\Folder', false)) {
    class_alias(Folder::class, '\\_JchOptimizeVendor\\V91\\Joomla\\Filesystem\\Folder');
}

if (!class_exists('_JchOptimizeVendor\V91\Joomla\Filesystem\Path', false)) {
    class_alias(Path::class, '\\_JchOptimizeVendor\\V91\\Joomla\\Filesystem\\Path');
}

if (!class_exists('_JchOptimizeVendor\V91\Joomla\Filesystem\Exception\FilesystemException', false)) {
    class_alias(
        FilesystemException::class,
        '\\_JchOptimizeVendor\\V91\\Joomla\\Filesystem\\Exception\\FilesystemException'
    );
}
