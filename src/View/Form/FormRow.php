<?php namespace Wms\Admin\DataGrid\View\Form;

use Wms\Admin\DataGrid\View\Form\Element\AddFilterButton;
use Zend\Form\View\Helper\FormRow as BaseFormRowHelper;
use Zend\Form\ElementInterface;

class FormRow extends BaseFormRowHelper
{
    private $count = 1;

    /**
     * @inheritdoc
     */
    public function render(ElementInterface $element)
    {
        $wrapper = '<div class="col-md-12">%s</div>';
        if ($element->getAttribute('type') == 'multi_checkbox') {
            $wrapper = '<div class="col-md-2">%s</div>';
            if ($this->count == 6) {
                $wrapper .= ' <div class="clearfix visible-lg-block"></div>';
                $this->count = 0;
            }
            $this->count = $this->count + 1;
        }

        if ($element instanceof AddFilterButton && $element->hasLabelOption('additionalLabel')) {
            $wrapper = '<div class="col-md-12"><label><span>';
            $wrapper .= $element->getLabelOption('additionalLabel');
            $wrapper .= '</span>%s</label></div>';
        }

        return sprintf($wrapper, parent::render($element));
    }
}
