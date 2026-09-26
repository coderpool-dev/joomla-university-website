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

namespace CodeAlfa\Component\JchOptimize\Administrator\Controller;

use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use JchOptimize\Core\Uri\Utils;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

use function defined;
use function ini_set;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class CriticalJsTableBodyController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);
        ini_set('pcre.jit', '0');
    }

    public function display($cachable = false, $urlparams = []): void
    {
        $view = $this->getView('CriticalJsTableBody', 'html', 'Administrator');
        $model = $this->getModel('PopulateModalBody');
        $view->setModel($model);

        $baseUrl = rawurldecode($this->input->getRaw('baseUrl'));
        $model->setState('jchoptimize.base_url', Utils::uriFor($baseUrl));

        parent::display($cachable, $urlparams);

        $this->app->close();
    }
}
