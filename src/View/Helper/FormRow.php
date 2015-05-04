<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Form\View\Helper\FormRow as BaseFormRowHelper;
use Zend\Form\ElementInterface;

class FormRow extends BaseFormRowHelper
{
    public function render(ElementInterface $element)
    {
        $wrapper = '<div class="col-md-2">%s</div>';
        if($element->getAttribute('type') == 'submit') {
            $wrapper = '<div class="col-md-12">%s</div>';
        }
        return sprintf($wrapper, parent::render($element));
    }
}