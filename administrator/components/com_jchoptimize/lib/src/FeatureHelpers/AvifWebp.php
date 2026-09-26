<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\Filesystem\Folder;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use JchOptimize\Core\Admin\AdminHelper as AdminHelper;
use JchOptimize\Core\Browser;
use JchOptimize\Core\Cdn\Cdn;
use JchOptimize\Core\Css\Components\CssUrl;
use JchOptimize\Core\Css\Parser;
use JchOptimize\Core\Exception\InvalidArgumentException;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\Elements\Img;
use JchOptimize\Core\Html\Elements\Input;
use JchOptimize\Core\Html\Elements\Source;
use JchOptimize\Core\Html\HtmlElementInterface;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Platform\UtilityInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Settings;
use JchOptimize\Core\Traits\TestableFileExistsTrait;
use JchOptimize\Core\Uri\UriConverter;
use JchOptimize\Core\Uri\Utils;

use function array_map;
use function defined;
use function file_exists;
use function pathinfo;
use function preg_replace_callback;
use function rawurldecode;
use function str_replace;

defined('_JCH_EXEC') or die('Restricted access');

class AvifWebp extends AbstractFeatureHelper
{
    use TestableFileExistsTrait;

    private bool $canIUse = true;

    public function __construct(
        Container $container,
        Registry $params,
        private Cdn $cdn,
        private PathsInterface $pathsUtils,
        private AdminHelper $adminHelper,
        private UtilityInterface $utility
    ) {
        parent::__construct($container, $params);

        $this->setCanIUse();
    }

    public function convert(HtmlElementInterface $element): void
    {
        if ($element instanceof Img || $element instanceof Input) {
            if ($element->getSrc() instanceof UriInterface) {
                $this->processSrcAttribute($element);
            }

            if ($element instanceof Img && $element->hasAttribute('srcset')) {
                $this->processSrcSetAttribute($element);
            }
        } elseif (
            $element instanceof Source
            && $element->hasAttribute('srcset')
            && !$element->hasAttribute('type')
        ) {
            $this->processSrcSetAttribute($element);
        } elseif ($element->getStyle() !== false) {
            $this->processStyleAttribute($element);
        }
    }

    private function processSrcAttribute(Img|Input $element): void
    {
        $src = $element->getSrc();

        if ($src instanceof UriInterface) {
            $srcAvifWebpValue = $this->getAvifWebpImages($src);
            $element->src($srcAvifWebpValue);
        }
    }

    private function processStyleAttribute(HtmlElementInterface $element): void
    {
        $style = $element->getStyle();

        if ($style !== false) {
            $style = preg_replace_callback("#" . Parser::cssUrlToken() . '#i', function ($matches) {
                try {
                    $cssUrl = CssUrl::load($matches[0]);
                } catch (InvalidArgumentException) {
                    return $matches[0];
                }

                $avifWebpImage = $this->getAvifWebpImages($cssUrl->getUri());

                return $cssUrl->setUri($avifWebpImage)->render();
            }, $style);

            $element->style($style);
        }
    }

    private function processSrcSetAttribute(Img|Source $element): void
    {
        $srcSet = $element->getSrcset();

        if ($srcSet !== false) {
            $urls = Helper::extractUrlsFromSrcset($srcSet);
            $avifWebpUrls = array_map(function (UriInterface $v) {
                return (string)($this->getAvifWebpImages($v));
            }, $urls);

            if ($urls != $avifWebpUrls) {
                $avifWebpSrcSet = str_replace($urls, $avifWebpUrls, $srcSet);
                $element->srcset($avifWebpSrcSet);
            }
        }
    }

    public function getAvifWebpImages(UriInterface $imageUri): UriInterface
    {
        if (
            $imageUri->getScheme() == 'data'
            || Utils::fileExtension($imageUri) == 'webp'
            || Utils::fileExtension($imageUri) == 'avif'
            || !$this->canIUse
        ) {
            return $imageUri;
        }

        $imagePath = UriConverter::uriToFilePath($imageUri, $this->pathsUtils, $this->cdn);

        $potentialPaths = [];

        if ($this->params->isEnabled(Settings::LOAD_AVIF_IMAGES)) {
            $potentialPaths[] = $this->getAvifPath($imagePath);
        }

        if ($this->params->isEnabled(Settings::LOAD_WEBP_IMAGES)) {
            $potentialPaths[] = $this->getWebpPath($imagePath);
        }

        foreach ($potentialPaths as $potentialWebpPath) {
            if ($this->fileExists($potentialWebpPath)) {
                $webpImageUri = UriConverter::filePathToUri($potentialWebpPath, $this->pathsUtils, $this->cdn);

                return $webpImageUri->withQuery($imageUri->getQuery())
                    ->withFragment($imageUri->getFragment());
            }
        }

        return $imageUri;
    }

    public function getBaseNextGenPath(string $originalImagePath): string
    {
        if (!file_exists($this->pathsUtils->nextGenImagesPath())) {
            Folder::create($this->pathsUtils->nextGenImagesPath());
        }

        $fileParts = pathinfo($this->adminHelper->contractFileName($originalImagePath));

        return $this->pathsUtils->nextGenImagesPath() . '/' . rawurldecode($fileParts['filename']);
    }

    public function getWebpPath(string $imagePath): string
    {
        return $this->getBaseNextGenPath($imagePath) . '.webp';
    }

    public function getAvifPath(string $imagePath): string
    {
        return $this->getBaseNextGenPath($imagePath) . '.avif';
    }

    protected function setCanIUse(): void
    {
        if ((int)$this->params->get('unsupported_browsers_policy', '0') > 0) {
            $browser = Browser::getInstance($this->utility);
            $browserName = $browser->getBrowser();
            $version = $browser->getVersion();

            if ($browserName == 'Internet Explorer') {
                $this->canIUse = false;
            } elseif ($browserName == 'Safari' && $version < 16.0) {
                $this->canIUse = false;
            } else {
                $this->canIUse = true;
            }
        }
    }

    public function getCanIUse(): bool
    {
        return $this->canIUse;
    }
}
