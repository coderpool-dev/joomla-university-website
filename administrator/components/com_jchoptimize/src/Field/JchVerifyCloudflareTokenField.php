<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Field;

use JchOptimize\Core\Admin\AdminHelper;
use Joomla\CMS\Form\Field\PasswordField;

class JchVerifyCloudflareTokenField extends PasswordField
{
    protected $type = 'JchVerifyCloudflareToken';

    protected $layout = 'form.field.jch-verify-api';

    protected function getLayoutPaths(): array
    {
        return [
            JPATH_ADMINISTRATOR . '/components/com_jchoptimize/layouts',
            JPATH_ROOT . '/layouts'
        ];
    }

    protected function getInput(): string
    {
        if (!JCH_PRO) {
            return AdminHelper::proOnlyField();
        } else {
            return parent::getInput();
        }
    }
}
