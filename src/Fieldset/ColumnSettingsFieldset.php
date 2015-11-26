<?php namespace Wms\Admin\DataGrid\Fieldset;

use ReflectionFunction;
use Wms\Admin\DataGrid\Model\TableHeaderCellModel;
use Wms\Admin\DataGrid\Model\TableModel;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\Fieldset;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class ColumnSettingsFieldset extends Fieldset implements InputFilterProviderInterface
{
    public $checkboxName = 'columns';

    /**
     * @var TableModel
     */
    private $tableModel;

    /**
     * @param TableModel $table
     */
    public function __construct(TableModel $table)
    {
        parent::__construct('Display');
        $this->tableModel = $table;
        $tableHeaders = $this->tableModel->getTableHeaders();

        // Group checkboxes by property
        $columnGroups = array();
        foreach ($tableHeaders as $tableHeader) {
            /** @var TableHeaderCellModel $tableHeader */
            $columnNameSegments = explode('.', $tableHeader->getName());
            if (!array_key_exists($columnNameSegments[0], $columnGroups)) {
                $columnGroups[$columnNameSegments[0]] = array();
            }
        }

        // Create all values for each property
        foreach ($tableHeaders as $tableHeader) {
            $columnNameSegments = explode('.', $tableHeader->getName());
            $label = $tableHeader->getName();

            if (count($columnNameSegments) > 1) {
                $segments = $columnNameSegments;
                unset($segments[0]);
                $label = implode('.', $segments);
            }

            $valueOption = array(
                'value' => $tableHeader->getName(),
                'label' => $label,
                'selected' => $tableHeader->isVisible(),
            );

            $columnGroups[$columnNameSegments[0]][] = $valueOption;
        }

        // Create the actual form element per property
        foreach ($columnGroups as $property => $checkboxValues) {
            $multiCheckbox = new MultiCheckbox($this->checkboxName);
            $multiCheckbox->setOptions(array('inline' => false, 'value_options' => $checkboxValues));

            $isParentField = (strpos($checkboxValues[0]['value'], ".") !== false);
            if (count($checkboxValues) >= 2 || (count($checkboxValues) == 1 && $isParentField)) {
                $multiCheckbox->setLabel($property);
            }

            // Dirty hack, because this->add() does not detect element or fieldset naming conflicts
            $this->iterator->insert($property, $multiCheckbox, 0);
        }

        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'label' => '<span class="glyphicon glyphicon glyphicon-refresh"></span>',
            'attributes' => array(
                'value' => 'Apply',
                'class' => 'btn btn-default',
            ),
        ));
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
