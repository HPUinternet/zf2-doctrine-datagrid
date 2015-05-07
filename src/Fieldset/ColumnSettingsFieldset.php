<?php namespace Wms\Admin\DataGrid\Fieldset;

use Wms\Admin\DataGrid\Model\TableModel;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class ColumnSettingsFieldset extends Fieldset implements InputFilterProviderInterface
{

    public $checkboxName = 'columns';

    /**
     * @var TableModel
     */
    private $tableModel;

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
            $multiCheckbox = array(
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => $this->checkboxName,
                'options' => array(
                    'inline' => false,
                    'value_options' => $checkboxValues
                )
            );
            if (count($checkboxValues) >= 2 ||
                (count($checkboxValues) == 1 && (strpos($checkboxValues[0]['value'], ".") !== false))) {
                $multiCheckbox['options']['label'] = $property;
            }
            $this->add($multiCheckbox);
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
}