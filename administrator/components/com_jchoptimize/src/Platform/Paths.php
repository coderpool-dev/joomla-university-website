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

namespace CodeAlfa\Component\JchOptimize\Administrator\Platform;

use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Uri;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\SystemUri;
use JchOptimize\Core\Uri\Utils;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;

use function defined;
use function dirname;
use function str_replace;

use const DIRECTORY_SEPARATOR;
use const JPATH_ROOT;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

/**
 * @package     JchOptimize\Platform
 *
 * @since       version
 *
 * A $path variable is considered an absolute path on the local filesystem without any trailing slashes.
 * Relative $paths will be indicated in their names or parameters.
 * A $folder is a representation of a directory with front and trailing slashes.
 * A $directory is the filesystem path to a directory with a trailing slash.
 */
class Paths implements PathsInterface
{
    private CMSApplication|null|ConsoleApplication $app;

    public function __construct(ConsoleApplication|CMSApplication|null $app)
    {
        $this->app = $app;
    }

    /**
     * Returns root relative path to the /assets/ folder
     *
     * @param bool $pathonly
     *
     * @return string
     */
    public function relAssetPath(bool $pathonly = false): string
    {
        return $this->baseFolder() . 'media/com_jchoptimize/assets';
    }

    private function baseFolder(): string
    {
        return str_replace('administrator/', '', SystemUri::currentBasePath());
    }

    public function iconsUrl(): string
    {
        return $this->baseFolder() . 'media/com_jchoptimize/icons';
    }

    /**
     * Returns path to the directory where static combined css/js files are saved.
     *
     * @param bool $isRootRelative If true, returns root relative path, otherwise, the absolute path
     *
     * @return string
     */
    public function cachePath(bool $isRootRelative = true): string
    {
        $sCache = 'media/com_jchoptimize/cache';

        if ($isRootRelative) {
            //Returns the root relative url to the cache directory
            return $this->baseFolder() . $sCache;
        } else {
            //Returns the absolute path to the cache directory
            return $this->rootPath() . '/' . $sCache;
        }
    }

    /**
     * @return string Absolute path to root of site
     */
    public function rootPath(): string
    {
        /** @var string */
        return JPATH_ROOT;
    }

    public function basePath(): string
    {
        return JPATH_BASE;
    }

    /**
     * Path to the directory where generated sprite images are saved
     *
     * @param bool $isRootRelative If true, return the root relative path with trailing slash;
     *                                 if false, return the absolute path without trailing slash.
     *
     * @return string
     */
    public function spritePath(bool $isRootRelative = false): string
    {
        return ($isRootRelative ? $this->baseFolder() : $this->rootPath() . '/') . 'images/jch-optimize';
    }

    /**
     * Find the absolute path to a resource given a root relative path
     *
     * @param string $url Root relative path of resource on the site
     *
     * @return string
     */
    public function absolutePath(string $url): string
    {
        return $this->rootPath() . DIRECTORY_SEPARATOR . trim(str_replace('/', DIRECTORY_SEPARATOR, $url), '\\/');
    }

    /**
     * The base folder for rewrites when the combined files are delivered with PHP using mod_rewrite.
     * Generally the parent directory for the
     * /media/ folder with a root relative path
     *
     * @return string
     */
    public function rewriteBaseFolder(): string
    {
        return Helper::getBaseFolder();
    }

    /**
     * Convert the absolute filepath of a resource to a url
     *
     * @param string $path Absolute path of resource
     *
     * @return string
     */
    public function path2Url(string $path): string
    {
        $oUri = clone JUri::getInstance();

        return $oUri->toString(['scheme', 'user', 'pass', 'host', 'port']) . $this->baseFolder() .
            Helper::strReplace($this->rootPath() . DIRECTORY_SEPARATOR, '', $path);
    }

    /**
     * Url to access Ajax functionality
     *
     * @param string $function Action to be performed by Ajax function
     *
     * @return string
     */
    public function ajaxUrl(string $function): string
    {
        $url = JUri::getInstance()->toString(['scheme', 'user', 'pass', 'host', 'port']);
        $url .= $this->baseFolder();
        $url .= 'index.php?option=com_ajax&plugin=' . $function . '&format=raw';

        return $url;
    }

    /**
     * Url used in administrator settings page to perform certain tasks
     *
     * @param string $name
     *
     * @return string
     */
    public function adminController(string $name): string
    {
        return JRoute::_('index.php?option=com_jchoptimize&view=Utility&task=' . $name, false);
    }

    /**
     * Parent directory of the folder where the original images are backed up in the Optimize Image Feature
     *
     * @return string
     */
    public function backupImagesParentDir(): string
    {
        return $this->rootPath() . '/images/';
    }

    public function nextGenImagesPath($isRootRelative = false): string
    {
        return ($isRootRelative ? $this->baseFolder() : $this->rootPath() . '/') . 'images/jch-optimize/ng';
    }

    public function getLogsPath(): string
    {
        /** @var string $logsPath */
        $logsPath = $this->app->get('log_path');

        return $logsPath;
    }

    public function mediaUrl(): string
    {
        return $this->baseFolder() . 'media/com_jchoptimize';
    }

    public function homeBasePath(): string
    {
        return JUri::root(true);
    }

    /**
     * @inheritDoc
     */
    public function homeBaseFullPath(): string
    {
        return JUri::root();
    }

    /**
     * @inheritDoc
     */
    public function captureCacheDir(bool $isRootRelative = false): string
    {
        return $this->rootRelativePath($isRootRelative) . 'media/com_jchoptimize/cache/html';
    }

    private function rootRelativePath(bool $isRootRelative): string
    {
        return ($isRootRelative ? $this->baseFolder() : $this->rootPath() . '/');
    }

    public function cacheDir(): string
    {
        return $this->cacheBase() . '/com_jchoptimize';
    }

    private function cacheBase(): string
    {
        $cachePath = JPATH_CACHE;
        /** @var string $cacheBase */
        $cacheBase = $this->app->get('cache_path', $cachePath);

        if (Uri::isRelativePathReference(Utils::uriFor($cacheBase))) {
            $cacheBase = $this->rootPath() . DIRECTORY_SEPARATOR . $cacheBase;
        }

        return $cacheBase;
    }

    public function templatePath(): string
    {
        return dirname(__FILE__, 3) . '/tmpl';
    }

    public function templateCachePath(): string
    {
        return $this->cacheBase() . '/com_jchoptimize/compiled_templates';
    }

    public function responsiveImagePath($isRootRelative = false): string
    {
        return ($isRootRelative ? $this->baseFolder() : $this->rootPath() . '/') . 'images/jch-optimize/rs';
    }

    public function liveSite(): string
    {
        return $this->app->get('live_site');
    }
}
