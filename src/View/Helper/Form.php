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
        $elementsPerContainer = $this->extractProperty('elementsPerContainer', $form, 1);
        $containerClass = $this->extractProperty('containerClass', $form, 'col-md-2');

        foreach ($form as $element) {
            if($elementCount == 0) {
                $formContent .= sprintf('<div class="%s">', $containerClass);
            }

            if ($element instanceof FieldsetInterface) {
                $formContent.= $this->getView()->formCollection($element);
            } else {
                $formContent.= $this->getView()->formRow($element);
            }

            $elementCount++;
            if($elementCount >= $elementsPerContainer) {
                $elementCount = 0;
                $formContent .= '</div>';
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