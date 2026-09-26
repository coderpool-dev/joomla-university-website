<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Model;

use _JchOptimizeVendor\V91\Psr\Http\Message\UploadedFileInterface;
use JchOptimize\Core\Exception\ExceptionInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\SystemUri;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\File;

use function array_unique;
use function defined;
use function file_exists;
use function tempnam;

use const JPATH_ROOT;
use const JPATH_SITE;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class BulkSettingsModel extends BaseDatabaseModel
{
    use SaveSettingsTrait;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }

    /**
     * @throws ExceptionInterface
     */
    public function importSettings(UploadedFileInterface $file): void
    {
        $tmpDir = JPATH_ROOT . '/tmp';
        $fileName = $file->getClientFilename() ?? tempnam($tmpDir, 'jchoptimize_');
        $targetPath = $tmpDir . '/' . $fileName;

        //if file not already at target path move it
        if (!file_exists($targetPath)) {
            $file->moveTo($targetPath);
        }

        $uploadedSettings = (new Registry())->loadFile($targetPath);

        File::delete($targetPath);

        if ($uploadedSettings->get('merge')) {
            $this->mergeSettings($uploadedSettings);
        } else {
            $this->setState('params', $uploadedSettings);
            $this->saveSettings();
        }
    }

    public function exportSettings(): string
    {
        $file = JPATH_SITE . '/tmp/' . SystemUri::currentUri()->getHost() . '_jchoptimize_settings.json';

        $params = $this->getState('params')->toString();

        File::write($file, $params);

        return $file;
    }

    /**
     * @throws ExceptionInterface
     */
    public function setDefaultSettings(): void
    {
        $this->setState('params', new Registry([]));
        $this->saveSettings();
    }

    /**
     * @throws ExceptionInterface
     */
    private function mergeSettings(Registry $uploadedSettings): void
    {
        $uploadedSettings->remove('merge');

        foreach ($uploadedSettings as $setting => $value) {
            if (is_array($value)) {
                $mergedSetting = array_unique(array_merge($this->params->get($setting, []), $value));
            } else {
                $mergedSetting = $value;
            }

            $this->params->set($setting, $mergedSetting);
        }

        $this->setState('params', $this->params);
        $this->saveSettings();
    }
}
