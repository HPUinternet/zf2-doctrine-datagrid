<?php namespace Wms\Admin\DataGrid\Fieldset;

use Wms\Admin\DataGrid\Model\TableModel;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Fieldset;
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
        $columns = $this->tableModel->getAvailableHeaders();

        // Group checkboxes by property
        $columnGroups = array();
        foreach ($columns as $column) {
            $columnNameSegments = explode('.', $column);
            if (!array_key_exists($columnNameSegments[0], $columnGroups)) {
                $columnGroups[$columnNameSegments[0]] = array();
            }
        }

        // Create all values for each property
        foreach ($columns as $column) {
            $columnNameSegments = explode('.', $column);
            $label = $column;
            if (count($columnNameSegments) > 1) {
                $segments = $columnNameSegments;
                unset($segments[0]);
                $label = implode('.', $segments);
            }
            $valueOption = array(
                'value' => $column,
                'label' => $label,
                'selected' => !$this->tableModel->isHiddenColumn($column),
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
            $this->iterator->insert($multiCheckbox, 0);
        }

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Apply',
                'class' => 'btn btn-primary',
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
}
