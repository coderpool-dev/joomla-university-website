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

use JchOptimize\Core\Platform\ExcludesInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted access');
// phpcs:enable PSR1.Files.SideEffects

class Excludes implements ExcludesInterface
{
    public function body(string $type, string $section = 'file'): array
    {
        if ($type == 'js') {
            if ($section == 'script') {
                return [
                    ['script' => 'var mapconfig90'],
                    ['script' => 'var addy']
                ];
            } else {
                return [
                    ['url' => 'assets.pinterest.com/js/pinit.js']
                ];
            }
        }

        if ($type == 'css') {
            return [];
        }

        return [];
    }

    public function extensions(): string
    {
        //language=RegExp
        return '(?>components|modules|plugins/[^/]+|media(?!/system|/jui|/cms|/media|/css|/js|/images|/vendor|/templates)(?:/vendor)?)/';
    }

    public function head(string $type, string $section = 'file'): array
    {
        if ($type == 'js') {
            if ($section == 'script') {
                return [];
            } else {
                return [
                    ['url' => 'plugin_googlemap3'],
                    ['url' => '/jw_allvideos/'],
                    ['url' => '/tinymce/']
                ];
            }
        }

        return [];
    }

    public function editors(string $url): bool
    {
        return (bool)preg_match('#/editors/#i', $url);
    }

    public function smartCombine(): array
    {
        return [
            'media/(?:jui|system|cms)/',
            '/templates/',
            '.'
        ];
    }
}
