<?php namespace Wms\Admin\DataGrid\View\Form;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormCollection as BaseFormCollectionHelper;

class NestedFormCollection extends BaseFormCollectionHelper
{

    protected $defaultElementHelper = 'DataGridFormRow';

    public function __invoke(ElementInterface $element = null, $wrap = true)
    {
        if (!$element) {
            return $this;
        }

        $this->setShouldWrap($wrap);

        $parentWrapStart = '<div class="tab-pane" id="' . $element->getName() . 'Tab">';
        $parentWrapEnd = '</div>';

        $columnSize = floor(12/count($element));
        $this->setWrapper('<div class="col-md-'.$columnSize.'" %4$s>%2$s%1$s%3$s</div>');

        $renderData = '';
        foreach($element as $fieldset) {
            $renderData .= $this->render($fieldset);
        }

        return $parentWrapStart . $renderData . $parentWrapEnd;
    }
}