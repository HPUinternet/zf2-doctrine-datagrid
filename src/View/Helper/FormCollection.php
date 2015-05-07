<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormCollection as BaseFormCollectionHelper;

class FormCollection extends BaseFormCollectionHelper
{

    protected $defaultElementHelper = 'DataGridFormRow';

    public function __invoke(ElementInterface $element = null, $wrap = true)
    {
        if (!$element) {
            return $this;
        }

        $this->setShouldWrap($wrap);

        $this->setWrapper('<div class="tab-pane" id="' . $element->getName() . 'Tab" %4$s>%2$s%1$s%3$s</div>');

        return $this->render($element);
    }
}