<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2024 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Model;

use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use CodeAlfa\Minify\Js;
use Exception;
use JchOptimize\Core\Admin\AbstractHtml;
use JchOptimize\Core\Admin\HtmlCrawler;
use JchOptimize\Core\FileUtils;
use JchOptimize\Core\Html\Elements\Script;
use JchOptimize\Core\Html\HtmlProcessor;
use stdClass;

trait PopulateModalBodyTrait
{
    protected HtmlCrawler $htmlCrawler;

    protected HtmlProcessor $htmlProcessor;

    public function getDynamicJavaScriptData(UriInterface $uri): array
    {
        try {
            $jsArray = $this->getDynamicJavaScript($uri);
        } catch (Exception $e) {
            $jsArray = [$e];
        }
        $data = [];

        /** @var Script|Exception $scriptObj */
        foreach ($jsArray as $scriptObj) {
            $row = new stdClass();

            if ($scriptObj instanceof Script) {
                $row->type = $scriptObj->getType() == 'module'
                    ? 'module'
                    : ($scriptObj->getAsync() ? 'async' : ($scriptObj->getDefer() ? 'defer' : ''));
                if (($src = $scriptObj->getSrc()) !== false) {
                    $row->value = FileUtils::prepareUrlValue($src);
                    $row->displayValue = FileUtils::prepareFileForDisplay($src);
                    $row->name = $row->type == 'module'
                        ? 'criticalModules_configure_helper' : 'criticalJs_configure_helper';
                } else {
                    $content = (string)$scriptObj->getChildren()[0];
                    if (trim($content) === '') {
                        continue;
                    }
                    $row->value = FileUtils::prepareContentValue(Js::optimize($content));
                    $row->displayValue = FileUtils::prepareContentForDisplay(Js::optimize($content));
                    $row->name = $row->type == 'module'
                        ? 'criticalModulesScripts_configure_helper' : 'criticalScripts_configure_helper';
                }
            } else {
                $row->displayValue = $scriptObj->getMessage();
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    private function getDynamicJavaScript(UriInterface $uri): array
    {
        $htmlArray = $this->htmlCrawler->getCrawledHtmls(['crawl_limit' => 1, 'base_url' => (string) $uri]);

        if (empty($htmlArray) || !isset($htmlArray[0]['html'])) {
            throw new Exception('No HTML returned');
        }

        $this->htmlProcessor->setHtml($htmlArray[0]['html']);

        return $this->htmlProcessor->processJavaScriptForConfigureHelper();
    }
}
