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

use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareInterface;
use CodeAlfa\Component\JchOptimize\Administrator\Extension\MVCFactoryDecoratorAwareTrait;
use CodeAlfa\Component\JchOptimize\Administrator\Model\ModeSwitcherModel;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\Input\Input;
use PhpParser\Node\Expr\BinaryOp\Mod;
use UnexpectedValueException;

use function defined;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die('Restricted Access');
// phpcs:enable PSR1.Files.SideEffects

class ModeSwitcherController extends BaseController implements MVCFactoryDecoratorAwareInterface
{
    use MVCFactoryDecoratorAwareTrait;

    /**
     * @var ModeSwitcherModel&ModelInterface
     */
    private $model;


    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);

        $this->setRedirect(base64_decode((string)$input->get('return', '', 'base64')));
    }

    public function setProduction(): void
    {
        try {
            $this->getModeSwitcherModel()->setProduction();
            $this->app->enqueueMessage(Text::_('COM_JCHOPTIMIZE_SET_IN_PRODUCTION_MODE'));
            $this->redirect();
        } catch (Exception $e) {
        }
    }

    public function setDevelopment(): void
    {
        try {
            $this->getModeSwitcherModel()->setDevelopment();
            $this->app->enqueueMessage(Text::_('COM_JCHOPTIMIZE_SET_IN_DEVELOPMENT_MODE'));
            $this->redirect();
        } catch (Exception $e) {
        }
    }

    private function getModeSwitcherModel(): ModeSwitcherModel
    {
        $model = $this->getModel('ModeSwitcher', 'Administrator');

        if ($model instanceof ModeSwitcherModel) {
            return  $model;
        }

        throw new UnexpectedValueException('ModeSwitcherModel not retrieved');
    }
}
