<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Component\JchOptimize\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use JchOptimize\Core\Admin\AdminHelper;
use JchOptimize\Core\Admin\MultiSelectItems;
use JchOptimize\Core\Helper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use SimpleXMLElement;

use const JCH_PRO;

FormHelper::loadFieldClass('list');

class JchMultiSelectField extends ListField
{
    protected $type = 'JchMultiSelect';

    protected MultiSelectItems $multiSelect;

    protected Container $container;

    protected bool $proOnly = false;
    protected $layout = 'form.field.jch-multiselect';

    public function __construct($form = null)
    {
        parent::__construct($form);

        $this->container = Factory::getApplication()->bootComponent('com_jchoptimize')->getContainer();
        $this->multiSelect = $this->container->buildObject(MultiSelectItems::class);
    }

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $this->addAttribute($element, 'multiple', 'true');
        $this->addAttribute($element, 'class', 'inputbox chzn-custom-value input-xlarge jch-multiselect');

        if ($element['proOnly']) {
            $this->proOnly = true;
        }

        $value = $this->castValue($value);

        if ($element['sort']) {
            sort($value);
        }

        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->dataAttributes['data-jch_param'] = $this->fieldname;
        }

        return $return;
    }

    protected function getInput(): string
    {
        if ($this->proOnly && !JCH_PRO) {
            return AdminHelper::proOnlyField();
        }

        return parent::getInput();
    }

    protected function castValue(string|array $value): array
    {
        if (!is_array($value)) {
            $value = Helper::getArray($value);
        }

        return $value;
    }

    protected function getOptions(): array
    {
        $options = [];

        foreach ($this->value as $excludeValue) {
            $options[$excludeValue] = $this->multiSelect->{'prepare' . ucfirst(
                (string)$this->dataAttributes['data-jch_group']
            ) . 'Values'}($excludeValue);
        }

        return $options;
    }

    protected function getLayoutPaths(): array
    {
        return [
            JPATH_ADMINISTRATOR . '/components/com_jchoptimize/layouts',
            JPATH_ROOT . '/layouts'
        ];
    }

    private function addAttribute(SimpleXMLElement $element, string $attrName, string $attrValue): void
    {
        // Check if the attribute exists
        if (isset($element[$attrName]) || $element->attributes()->$attrName) {
            // Append to existing attribute value
            $currentValue = (string)$element[$attrName];

            //prevent duplicates
            $values = explode(' ', $currentValue);
            if (!in_array($attrValue, $values)) {
                $values[] = $attrValue;
                $element[$attrName] = implode(' ', $values);
            }
        } else {
            // Attribute does not exist, add it
            $element->addAttribute($attrName, $attrValue);
        }
    }
}
