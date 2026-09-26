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

namespace JchOptimize\Core\Html\Callbacks;

use JchOptimize\Core\Exception\PregErrorException;
use JchOptimize\Core\Html\HtmlElementBuilder;
use JchOptimize\Core\Html\HtmlElementInterface;
use JchOptimize\Core\Html\Parser;

use function preg_match;

class ReduceDom extends AbstractCallback
{
    protected string $content = '';

    public function processMatches(array $matches): string
    {
        if (empty($matches[0])) {
            return $matches[0];
        }

        $elementRegex = self::htmlNestedElementWithCaptureValueToken();

        preg_match("#^{$elementRegex}$#s", $matches[0], $matches);
        $startTag = $matches['starttag'];
        $endTag = $matches['endtag'];

        $this->content = $matches['content'];

        try {
            $element = HtmlElementBuilder::load($startTag . $endTag);
        } catch (PregErrorException) {
            return $matches[0];
        }

        return $this->internalProcessMatches($element);
    }

    protected function internalProcessMatches(HtmlElementInterface $element): string
    {
        if (
            ($classes = $element->getClass()) === false
            || $classes !== false && !in_array('jchoptimize-reduce-dom', $classes)
        ) {
            $element->class('jchoptimize-reduce-dom');

            $template = HtmlElementBuilder::template();
            $template->class('jchoptimize-reduce-dom__template');

            $template->addChild($this->content);
            $element->addChild($template);
        } else {
            $element->addChild($this->content);
        }

        return $element->render();
    }

    private static function htmlNestedElementWithCaptureValueToken(): string
    {
        $startTag = Parser::htmlStartTagToken();
        $endTag = Parser::htmlEndTagToken();

        return "(?<starttag>{$startTag})(?<content>.*)(?<endtag>{$endTag})";
    }
}
