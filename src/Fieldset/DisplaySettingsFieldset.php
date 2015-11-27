<?php namespace Wms\Admin\DataGrid\Fieldset;

use Wms\Admin\DataGrid\Model\TableHeaderCellModel;
use Wms\Admin\DataGrid\Model\TableModel;
use Zend\Form\ElementPrepareAwareInterface;
use Zend\Form\Fieldset;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * This class is used to to build the Display settings form
 *
 * Class DisplaySettingsFieldset
 * @package Wms\Admin\DataGrid\Fieldset
 */
class DisplaySettingsFieldset extends Fieldset implements InputFilterProviderInterface
{
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

            if (!is_array($columnNameSegments) || empty($columnNameSegments)) {
                continue;
            }

            // Create all values for each property
            $label = $tableHeader->getName();

            $columnGroupName = 'base';
            if (count($columnNameSegments) > 1) {
                $columnGroupName = $columnNameSegments[0];
                $segments = $columnNameSegments;
                unset($segments[0]);
                $label = implode('.', $segments);
            }

            if (!array_key_exists($columnGroupName, $columnGroups)) {
                $columnGroups[$columnGroupName] = array();
            }

            $valueOption = array(
                'value' => $tableHeader->getName(),
                'label' => $label,
                'selected' => $tableHeader->isVisible(),
            );

            $columnGroups[$columnGroupName][] = $valueOption;
        }

        // Create the actual form element per property
        foreach ($columnGroups as $property => $checkboxValues) {
            $entityColumn = new NestedSettingsFieldset($property, $checkboxValues);
            $this->add($entityColumn);
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
