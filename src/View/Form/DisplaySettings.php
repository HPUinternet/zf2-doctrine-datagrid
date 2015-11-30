<?php namespace Wms\Admin\DataGrid\View\Form;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormCollection as BaseFormCollectionHelper;

class DisplaySettings extends BaseFormCollectionHelper
{

    /**
     * @inheritdoc
     */
    public function __invoke(ElementInterface $element = null, $wrap = true)
    {
        if (!$element) {
            return $this;
        }

        $this->setShouldWrap($wrap);

        $this->setWrapper(
            '<div class="tab-pane" id="' . str_replace(" ", "_", $element->getName()) . 'Tab" %4$s>%2$s%1$s%3$s</div>'
        );

        $this->setFieldsetHelper($this->getView()->DataGridNestedSettings());

        return $this->render($element);
    }
}
