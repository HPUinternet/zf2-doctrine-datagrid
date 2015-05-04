<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Form\FormInterface;
use Zend\Form\View\Helper\Form as BaseFormHelper;

/**
 * In order to create more flexibility with the layout options and wrap the form in several elements, we've created
 * several view helpers that will assist the form's rendering in a page.
 *
 * Class FormCollection
 * @package Wms\Admin\DataGrid\View\Helper
 */
class Form extends BaseFormHelper
{

    public function render(FormInterface $form) {
        if (method_exists($form, 'prepare')) {
            $form->prepare();
        }

        $formContent = '';
        $elementCount = 0;
        $elementsPerContainer = $this->extractProperty('elementsPerContainer', $form, 6);

        foreach ($form as $element) {
            if($elementCount == 0) {
                $formContent.= '<div class="row">';
            }

            if ($element instanceof FieldsetInterface) {
                $formContent.= $this->getView()->formCollection($element);
            } else {
                $formContent.= $this->getView()->DataGridFormRow($element);
            }

            $elementCount++;
            if($elementCount >= $elementsPerContainer) {
                $formContent.= '</div>';
                $elementCount = 0;
            }
        }

        return $this->openTag($form) . $this->formHeading($form) . $formContent . $this->closeTag();
    }

    private function extractProperty($propertyName, $object, $defaultValue){
        if(property_exists($object, $propertyName)) {
           return $object->$propertyName;
        }
        return $defaultValue;
    }

    protected function formHeading(FormInterface $form) {
        $name = $form->getAttribute('name');
        if(!empty($name)) {
            echo sprintf(
                '<div class="row"><div class="col-md-12"><h3>%s</h3></div></div>',
                $this->getView()->Translate($name)
            );
        }
    }

}