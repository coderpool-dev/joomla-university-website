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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('No direct access');
// phpcs:enable PSR1.Files.SideEffects

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/version.php';

class JFormFieldJchdescription extends JFormField
{
    public $type = 'jchdescription';

    protected function getLabel()
    {
        return '';
    }

    protected function getInput()
    {
        $attributes = $this->element->attributes();

        $html = '';

        switch ($attributes['section']) {
            case 'features':
                $header = Text::_('JCH_HEADER_MAJOR_FEATURES');
                $pro_only = ' <small class="label label-important small" style="padding: 1px 3px;"><em>' . Text::_('JCH_FEATURES_PRO_ONLY') . '</em></small>';
                $description = '<ul>'
                    . '<li>' . Text::_('JCH_FEATURES_COMBINE_FILES') . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_AUTO_SETTINGS') . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_SPRITE_GENERATOR') . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_IMAGE_ATTRIBUTES') . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_CRITICAL_CSS') . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_LAZY_LOAD') . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_CDN') . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_PRO_CDN') . $pro_only . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_PRO_HTTP2') . $pro_only . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_PRO_REMOVE_UNUSED_CSS') . $pro_only . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_PRO_LAZY_LOAD') . $pro_only . '</li>'
                    . '<li>' . Text::_('JCH_FEATURES_PRO_OPTIMIZE_IMAGES') . $pro_only . '</li>'
                    . '</ul>';

                break;
            case 'support':
                $header = Text::_('JCH_HEADER_SUPPORT');
                $description = '<p>' . Text::sprintf(
                    'JCH_SUPPORT_DOCUMENTATION',
                    'https://www.jch-optimize.net/documentation.html'
                ) . '</p>'
                    . '<p>' . Text::sprintf(
                        'JCH_SUPPORT_REQUESTS',
                        'https://www.jch-optimize.net/subscribe/levels.html'
                    ) . '</p>';

                break;

            case 'feedback':
                $header = Text::_('JCH_HEADER_FEEDBACK');
                $description = '<p>' . Text::sprintf(
                    'JCH_FEEDBACK_DESCRIPTION',
                    'https://extensions.joomla.org/extension/core-enhancements/performance/jch-optimize/'
                ) . '</p>';
                break;

            case 'version':
                $header = '';
                $description = '<h4>(Version ' . JCH_VERSION . ')</h4>'/* ##<freecode>##
                . '<br />'
                . '<p class="alert alert-info alert-block">Upgrade to the pro version now using coupon code JCHGOPRO20 for a 20% discount!!</p>' ##<freecode>## */
                ;
                break;

            default:
                break;
        }

        $html .= '</div></div>';

        $html .= '<div>';
        $html .= $header == '' ? '' : '<h3>' . $header . '</h3>';
        $html .= $description;
        $html .= '</div>';

        $html .= '<div><div>';

        return $html;
    }
}
