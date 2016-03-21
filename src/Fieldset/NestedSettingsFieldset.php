<?php namespace Wms\Admin\DataGrid\Fieldset;

use Zend\Form\Element\Checkbox;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\Fieldset;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class NestedSettingsFieldset extends Fieldset implements InputFilterProviderInterface
{
    public $checkboxName = 'columns';

    /**
     * @param  null|int|string $name
     * @param array $tableHeaders
     */
    public function __construct($name, array $tableHeaders)
    {
        parent::__construct($name);

        // Create the actual form element per property
        foreach ($tableHeaders as $checkboxValues) {
            $options = array(
                'inline' => false,
                'value_options' => array(
                    0 => array(
                        'value' => $checkboxValues['value'],
                        'selected' => $checkboxValues['selected'],
                        'label' => $checkboxValues['label'],
                    )
                )
            );
            $multiCheckbox = new MultiCheckbox(
                $this->checkboxName,
                $options
            );

            // Dirty hack, because this->add() does not detect element or fieldset naming conflicts
            $this->iterator->insert($checkboxValues['label'], $multiCheckbox, 0);
        }
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array();
    }

    /**
     * @param FormInterface $form
     * @return mixed|void
     */
    public function prepareElement(FormInterface $form)
    {
        foreach ($this->iterator as $elementOrFieldset) {
            // Recursively prepare elements
            if ($elementOrFieldset instanceof ElementPrepareAwareInterface) {
                $elementOrFieldset->prepareElement($form);
            }
        }
    }
}
