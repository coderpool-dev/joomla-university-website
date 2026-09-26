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

namespace CodeAlfa\Component\JchOptimize\Administrator\Controller;

use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\UploadedFile;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use CodeAlfa\Component\JchOptimize\Administrator\Model\ModeSwitcherModel;
use CodeAlfa\Component\JchOptimize\Administrator\Model\ReCacheModel;
use CodeAlfa\Component\JchOptimize\Administrator\Model\TogglePluginsModel;
use Exception;
use JchOptimize\Core\Admin\AdminTasks;
use JchOptimize\Core\Model\CacheMaintainer;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Platform\PluginInterface;
use JchOptimize\Core\Registry;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Input\Input;
use Joomla\Uri\UriInterface;
use Throwable;

use function base64_decode;
use function defined;
use function hash_hmac;
use function ob_clean;
use function rawurlencode;
use function time;

use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');

// phpcs:enable PSR1.Files.SideEffects

class UtilityController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    private CacheMaintainer $cacheMaintainer;

    private Registry $params;

    private PluginInterface $pluginUtils;

    private PathsInterface $pathsUtils;

    private AdminTasks $tasks;

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);

        $return = $this->input->get('return', '', 'base64');

        if ($return) {
            $redirectUrl = Route::_(base64_decode($return));
            $this->input->set('return', null);
        } else {
            $redirectUrl = Route::_('index.php?option=com_jchoptimize', false);
        }

        $this->setRedirect($redirectUrl);
    }

    public function setCacheMaintainer(CacheMaintainer $cacheMaintainer): void
    {
        $this->cacheMaintainer = $cacheMaintainer;
    }

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }

    public function setPluginUtils(PluginInterface $pluginUtils): void
    {
        $this->pluginUtils = $pluginUtils;
    }

    public function setPathsUtils(PathsInterface $pathsUtils): void
    {
        $this->pathsUtils = $pathsUtils;
    }

    public function setTasks(AdminTasks $tasks): void
    {
        $this->tasks = $tasks;
    }

    public function browsercaching(): void
    {
        $success = null;

        $expires = $this->tasks->leverageBrowserCaching($success);

        if ($success === false) {
            $this->message = Text::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_FAILED');
            $this->messageType = 'error';
        } elseif ($expires === 'FILEDOESNTEXIST') {
            $this->message = Text::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_FILEDOESNTEXIST');
            $this->messageType = 'warning';
        } elseif ($expires === 'CODEUPDATEDSUCCESS') {
            $this->message = Text::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_CODEUPDATEDSUCCESS');
        } elseif ($expires === 'CODEUPDATEDFAIL') {
            $this->message = Text::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_CODEUPDATEDFAIL');
            $this->messageType = 'notice';
        } else {
            $this->message = Text::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_SUCCESS');
        }

        $this->redirect();
    }

    public function cleancache(): void
    {
        $deleted = $this->cacheMaintainer->cleanCache();

        if (!$deleted) {
            $this->message = Text::_('COM_JCHOPTIMIZE_CACHECLEAN_FAILED');
            $this->messageType = 'error';
        } else {
            $this->message = Text::_('COM_JCHOPTIMIZE_CACHECLEAN_SUCCESS');
        }

        $this->redirect();
    }

    public function togglepagecache(): void
    {
        $this->message = Text::_('COM_JCHOPTIMIZE_TOGGLE_PAGE_CACHE_FAILURE');
        $this->messageType = 'error';

        /** @var ModeSwitcherModel $modeSwitcher */
        $modeSwitcher = $this->getModel('ModeSwitcher');
        $result = $modeSwitcher->togglePageCacheState();


        if ($result) {
            $this->message = Text::sprintf('COM_JCHOPTIMIZE_TOGGLE_PAGE_CACHE_SUCCESS', 'enabled');
            $this->messageType = 'success';
        }

        $this->redirect();
    }

    public function togglerecacheplugin()
    {
        $this->message = Text::_('COM_JCHOPTIMIZE_TOGGLE_RECACHE_FAILURE');
        $this->messageType = 'error';

        /** @var TogglePluginsModel $modeSwitcher */
        $togglePlugins = $this->getModel('TogglePlugins', 'Administrator');
        $result = $togglePlugins->toggleRecachePluginState();

        if ($result) {
            $this->message = Text::sprintf('COM_JCHOPTIMIZE_TOGGLE_RECACHE_SUCCESS', 'enabled');
            $this->messageType = 'success';
        }

        $this->redirect();
    }

    public function keycache(): void
    {
        $this->tasks->generateNewCacheKey();

        $this->message = Text::_('COM_JCHOPTIMIZE_CACHE_KEY_GENERATED');

        $this->redirect();
    }

    public function orderplugins(): void
    {
        $saved = $this->getModel('OrderPlugins')->orderPlugins();

        if ($saved === false) {
            $this->message = Text::_('JLIB_APPLICATION_ERROR_REORDER_FAILED');
            $this->messageType = 'error';
        } else {
            $this->message = Text::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED');
        }

        $this->redirect();
    }

    public function restoreimages(): void
    {
        $mResult = $this->tasks->restoreBackupImages();

        if ($mResult === 'SOMEIMAGESDIDNTRESTORE') {
            $this->message = Text::_('COM_JCHOPTIMIZE_SOMERESTOREIMAGE_FAILED');
            $this->messageType = 'warning';
        } elseif ($mResult === 'BACKUPPATHDOESNTEXIST') {
            $this->message = Text::_('COM_JCHOPTIMIZE_BACKUPPATH_DOESNT_EXIST');
            $this->messageType = 'warning';
        } else {
            $this->message = Text::_('COM_JCHOPTIMIZE_RESTOREIMAGE_SUCCESS');
        }

        $this->setRedirect(Route::_('index.php?option=com_jchoptimize&view=OptimizeImages', false));
        $this->redirect();
    }

    public function deletebackups(): void
    {
        $mResult = $this->tasks->deleteBackupImages();

        if ($mResult === false) {
            $this->message = Text::_('COM_JCHOPTIMIZE_DELETEBACKUPS_FAILED');
            $this->messageType = 'error';
        } elseif ($mResult === 'BACKUPPATHDOESNTEXIST') {
            $this->message = Text::_('COM_JCHOPTIMIZE_BACKUPPATH_DOESNT_EXIST');
            $this->messageType = 'warning';
        } else {
            $this->message = Text::_('COM_JCHOPTIMIZE_DELETEBACKUPS_SUCCESS');
        }

        $this->setRedirect(Route::_('index.php?option=com_jchoptimize&view=OptimizeImages', false));
        $this->redirect();
    }

    public function recache(string|UriInterface|null $redirectUrl = null): void
    {
        $redirectUrl = (string)($redirectUrl ?? Route::_('index.php?option=com_jchoptimize', false));
        $this->setRedirect($redirectUrl);

        if (JCH_PRO === '1') {
            $this->app->enqueueMessage(Text::_('COM_JCHOPTIMIZE_RECACHE_STARTED'), 'success');

            try {
                /** @var ReCacheModel $recacheModel */
                $recacheModel = $this->getModel('ReCache', 'Administrator');
                $recacheModel->triggerCliRecache();
            } catch (Throwable) {
                try {
                    $this->pingRecacheAsync();
                } catch (Throwable) {
                    //Didn't work. Try legacy method
                    $this->customRedirectAndRecache($redirectUrl);
                }
            }
        }

        $this->redirect();
    }


    public function importsettings()
    {
        /** @psalm-var array{tmp_name:string, size:int, error:int, name:string|null, type:string|null}|null $file */
        $file = $this->input->files->get('file');

        if (empty($file)) {
            $this->message = Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_NO_FILE');
            $this->messageType = 'error';

            return;
        }

        $uploadErrorMap = [
            UPLOAD_ERR_OK => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_OK'),
            UPLOAD_ERR_INI_SIZE => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_INI_SIZE'),
            UPLOAD_ERR_FORM_SIZE => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_FORM_SIZE'),
            UPLOAD_ERR_PARTIAL => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_PARTIAL'),
            UPLOAD_ERR_NO_FILE => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_NO_FILE'),
            UPLOAD_ERR_NO_TMP_DIR => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_NO_TMP_DIR'),
            UPLOAD_ERR_CANT_WRITE => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_CANT_WRITE'),
            UPLOAD_ERR_EXTENSION => Text::_('COM_JCHOPTIMIZE_UPLOAD_ERR_EXTENSION')
        ];

        try {
            $uploadedFile = new UploadedFile(
                $file['tmp_name'],
                $file['size'],
                $file['error'],
                $file['name'],
                $file['type']
            );

            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                throw new Exception(Text::_($uploadErrorMap[$uploadedFile->getError()]));
            }
        } catch (Exception $e) {
            $this->message = Text::sprintf('COM_JCHOPTIMIZE_UPLOADED_FILE_ERROR', $e->getMessage());
            $this->messageType = 'error';

            return;
        }

        try {
            $this->getModel('BulkSettings')->importSettings($uploadedFile);
        } catch (Exception $e) {
            $this->message = Text::sprintf('COM_JCHOPTIMIZE_IMPORT_SETTINGS_ERROR', $e->getMessage());
            $this->messageType = 'error';

            return;
        }

        $this->message = Text::_('COM_JCHOPTIMIZE_SUCCESSFULLY_IMPORTED_SETTINGS');

        $this->redirect();
    }

    public function exportsettings(): void
    {
        $file = $this->getModel('BulkSettings')->exportSettings();

        if (file_exists($file) && $this->app instanceof CMSApplication) {
            $this->app->setHeader('Content-Description', 'FileTransfer');
            $this->app->setHeader('Content-Type', 'application/json');
            $this->app->setHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"');
            $this->app->setHeader('Expires', '0');
            $this->app->setHeader('Cache-Control', 'must-revalidate');
            $this->app->setHeader('Pragma', 'public');
            $this->app->setHeader('Content-Length', (string)filesize($file));
            $this->app->sendHeaders();

            ob_clean();
            flush();
            readfile($file);

            File::delete($file);

            $this->app->close();
        }
    }

    public function setdefaultsettings(): void
    {
        try {
            $this->getModel('BulkSettings')->setDefaultSettings();
        } catch (Exception $e) {
            $this->message = Text::_('COM_JCHOPTIMIZE_RESTORE_DEFAULT_SETTINGS_FAILED');
            $this->messageType = 'error';

            return;
        }

        $this->message = Text::_('COM_JCHOPTIMIZE_DEFAULT_SETTINGS_RESTORED');

        $this->redirect();
    }

    private function default(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_jchoptimize', false));
        $this->redirect();
    }

    /**
     * @throws Exception
     */
    private function pingRecacheAsync(): void
    {
        $ts = (string)time();
        $secret = $this->app->get('secret');
        $base = Uri::root(); // public site base, not /administrator
        $sig = hash_hmac('sha256', 'recache.run|' . $ts, $secret);
        $url = $base . 'index.php?option=com_jchoptimize&task=recache.run'
            . '&ts=' . rawurlencode($ts)
            . '&sig=' . rawurlencode($sig);
        $parts = parse_url($url);
        $host = $parts['host'] ?? 'localhost';
        $port = ($parts['scheme'] ?? 'http') === 'https' ? 443 : 80;
        $transport = $port === 443 ? 'ssl://' : '';
        if (
            $fp = @stream_socket_client(
                $transport . $host . ':' . $port,
                $errno,
                $errstr,
                0.5,
                STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT
            )
        ) {
            $path = ($parts['path'] ?? '/') . (isset($parts['query']) ? ('?' . $parts['query']) : '');
            fwrite($fp, "POST {$path} HTTP/1.1\r\nHost: {$host}\r\nConnection: Close\r\nContent-Length: 0\r\n\r\n");
            fclose($fp);
        } else {
            throw new Exception('Error trying to start recache: ' . $errno . ' - ' . $errstr);
        }
    }

    private function customRedirectAndRecache(string $redirectUrl): void
    {
        ignore_user_abort(true);

        $app = $this->app;
        if (!$app instanceof CMSApplication) {
            return;
        }

        // If headers already went out, don't touch session—just do a JS redirect and bail.
        if (headers_sent()) {
            echo '<script>document.location.href=' . json_encode($redirectUrl) . ";</script>\n";

            return;
        }

        // 1) Persist messages and CLOSE the session BEFORE any output.
        $messageQueue = $app->getMessageQueue();

        $session = $app->getSession(); // Joomla\Session\SessionInterface
        // Start proactively to avoid a lazy start later (which would fail after we echo).
        $session->start();

        if (!empty($messageQueue)) {
            $session->set('application.queue', $messageQueue);
        }

        // Release the session lock so the "background" work can keep running
        // without blocking other requests for this user.
        // Use native close to be extra sure no late handlers try to write again.
        if (method_exists($session, 'close')) {
            $session->close();
        } else {
            @session_write_close();
        }

        // 2) Kill any active output buffers from earlier code to prevent accidental output.
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        // 3) Handle the Trident + non-ASCII edge case with a tiny HTML wrapper.
        if ($app->client->engine == WebClient::TRIDENT && !$app::isAscii($redirectUrl)) {
            $html = '<!doctype html><html><head>';
            $html .= '<meta http-equiv="content-type" content="text/html; charset=' . $app->charSet . '">';
            $html .= '<script>document.location.href=' . json_encode($redirectUrl) . ';</script>';
            $html .= '</head><body></body></html>';

            // No extra headers here—just send body and finish.
            echo $html;

            // Try to finish the response cleanly.
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                @flush();
            }

            return;
        }

        // 4) Build a tiny body and send a proper 303 with no-cache headers.
        $body = 'Redirecting...';

        // Avoid content-length mismatches with compression—set it only if zlib is off.
        $zlibOn = ini_get('zlib.output_compression');
        $app->setBody($body);
        $app->setHeader('Status', '303', true);
        $app->setHeader('Location', $redirectUrl, true);
        $app->setHeader('Expires', 'Wed, 17 Aug 2005 00:00:00 GMT', true);
        $app->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
        $app->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Connection', 'close', true);
        if (!$zlibOn) {
            $app->setHeader('Content-Length', (string)strlen($body), true);
        }

        $app->sendHeaders();
        echo $body;

        // 5) From this point on, do not touch the session or emit more output.
        // Disconnect DB to free up connection for the crawler run.
        try {
            Factory::getContainer()->get(DatabaseInterface::class)->disconnect();
        } catch (Throwable $e) {
            // swallow; not critical
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @flush();
        }

        // 6) Recache in the background.
        try {
            /** @var ReCacheModel $recacheModel */
            $recacheModel = $this->getModel('ReCache', 'Administrator');
            $recacheModel->reCache();
        } catch (Exception $e) {
            $app->getLogger()->error('Recache failed: ' . $e->getMessage(), ['category' => 'com_jchoptimize']);
        }
    }
}
