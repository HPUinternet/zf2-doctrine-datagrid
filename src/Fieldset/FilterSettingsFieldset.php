<?php namespace Wms\Admin\DataGrid\Fieldset;

use Wms\Admin\DataGrid\Model\TableModel;
use Wms\Admin\DataGrid\View\Form\NestedFieldsetInterface;
use Zend\Form\Fieldset;
use Wms\Admin\DataGrid\View\Form\Element\AddFilterButton;

class FilterSettingsFieldset extends Fieldset implements NestedFieldsetInterface
{
    /**
     * @var TableModel
     */
    private $tableModel;


    public function __construct(TableModel $tableModel)
    {
        parent::__construct('Advanced Search');
        $this->tableModel = $tableModel;
        $dataTypes = $this->tableModel->getDataTypes();

        // @todo (re)add the used filters right here
        $this->add(new Fieldset('Active Criteria'));

        if (!empty($dataTypes)) {
            $this->addFilterableProperties($dataTypes);
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

    private function addFilterableProperties(array $tableHeadings)
    {
        $fieldSet = new Fieldset('Available Criteria');
        foreach ($tableHeadings as $heading => $fieldType) {
            $button = new AddFilterButton($heading);
            $button->setLabelOption('additionalLabel', $heading);
            $fieldSet->add($button);
        }
        $this->add($fieldSet);
    }
}
