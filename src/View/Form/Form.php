<?php namespace Wms\Admin\DataGrid\View\Form;

use Zend\Form\FieldsetInterface;
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
    /**
     * @inheritdoc
     */
    public function render(FormInterface $form)
    {
        if (method_exists($form, 'prepare')) {
            $form->prepare();
        }

        $formContent = '';
        $formContent .= '<div id="my-tab-content" class="tab-content">';

        // Print tabs per fieldset
        $tabHeading = '<ul class="nav nav-tabs">';
        foreach ($form as $element) {
            if ($element instanceof FieldsetInterface) {
                $tabHeading .= sprintf(
                    '<li><a href="#%sTab" data-toggle="tab">%s</a></li>',
                    str_replace(" ", "_", $element->getName()),
                    $element->getName()
                );

                $formContent .= $this->getView()->DataGridDisplaySettings($element);
            }
        }
        $tabHeading .= '</ul>';
        $formContent .= '</div>';

        // Remaining elements
        foreach ($form as $element) {
            if ($element instanceof FieldsetInterface == false) {
                $formContent .= $this->getView()->FormRow($element);
            }
        }

        return $this->openTag($form) . $tabHeading . $formContent . $this->closeTag();
    }
}
