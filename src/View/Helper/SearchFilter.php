<?php namespace Wms\Admin\DataGrid\View\Helper;

use Wms\Admin\DataGrid\Model\TableModel;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\StrategyResolver;
use Zend\Di\ServiceLocator;
use Zend\Form\Element;
use Zend\View\Helper\AbstractHelper;

class SearchFilter extends AbstractHelper
{
    /**
     * @var TableModel
     */
    private $tableModel;

    /**
     * @var StrategyResolver
     */
    private $dataStrategyResolver;

    /**
     * If configured, this view helper will print the inline simple filter right after the
     * initial table heading.
     * @param TableModel $tableModel
     * @param StrategyResolver $dataStrategyResolver
     */
    public function __invoke(TableModel $tableModel, StrategyResolver $dataStrategyResolver)
    {
        $this->tableModel = $tableModel;
        $this->dataStrategyResolver = $dataStrategyResolver;

        echo '<tr class="simpleSearch tabelZoekBalk">';
        echo $this->printFilterFields();
        echo '<td>
                <span class="pull-right">
                    <button type="submit" class="btn knopSearch">
                        <span class="glyphicon glyphicon-search"></span> Search
                    </button>
                </span>
                </td>';
        echo '</tr>';
        echo '</thead>';
    }

    /**
     * Prints the form elements per field
     */
    public function printFilterFields()
    {
        foreach ($this->tableModel->getUsedHeaders() as $tableHeader => $accessor) {
            echo '<td>';
            echo $this->getFilterElement($tableHeader, $accessor);
            echo '</td>';
        }

        foreach ($this->tableModel->getNonFieldFilters() as $filter) {
            $filterElement = $filter->getFilterElement();
            $filterElement = $this->setElementCurrentValue($filterElement, $filterElement->getName());
            $filterElement->setName('search[' . $filterElement->getName() . ']');

            echo '<td>';
            echo $this->getView()->formElement($filterElement);
            echo '</td>';
        }
    }

    /**
     * @param $tableHeader
     * @param $accessor
     * @return string|Element
     */
    protected function getFilterElement($tableHeader, $accessor)
    {
        if (isset($this->tableModel->getFilters()[$tableHeader])) {
            $filterElement = $this->tableModel->getFilters()[$tableHeader]->getFilterElement();
            $filterElement->setName('search[' . $filterElement->getName() . ']');
            $filterElement = $this->setElementCurrentValue($filterElement, $tableHeader);

            return $this->getView()->formElement($filterElement);
        }

        $dataType = $this->tableModel->getDataTypeByHeader($tableHeader);
        if ($tableHeader != $accessor) {
            $dataType = "Array";
        }

        $element = $this->dataStrategyResolver->displayFilterForDataType('search[' . $tableHeader . ']', $dataType);
        if ($element instanceof Element) {
            $element = $this->setElementValues($element, $tableHeader);
            $element = $this->setElementCurrentValue($element, $tableHeader);

            return $this->getView()->formElement($element);
        }

        return $element;
    }

    /**
     * If the TableModel contains FilterData this method can provide the input element with the right optional values
     *
     * @param Element $element
     * @param $fieldName
     * @return Element
     */
    protected function setElementValues(Element $element, $fieldName)
    {
        // If the fieldname is not nested, there is no way the joined query returns data for you.
        $fieldNameSegments = explode(".", $fieldName);
        if (count($fieldNameSegments) <= 1) {
            return $element;
        }

        $parentField = $fieldNameSegments[0];
        $childField = $fieldNameSegments[1];


        if (!isset($this->tableModel->getAvailableFilterValues()[$parentField])) {
            return $element;
        }

        $valueOptions = array();
        foreach ($this->tableModel->getAvailableFilterValues()[$parentField] as $filterValues) {
            if (isset($filterValues[$childField]) && !in_array($filterValues[$childField], $valueOptions)) {
                $value = $filterValues[$childField];
                $displayValue = $this->dataStrategyResolver->resolveAndParse($value, $fieldName);
                $valueOptions[$value] = $displayValue;
            }
        }

        if (method_exists($element, 'setValueOptions') && method_exists($element, 'setEmptyOption')) {
            $emptyLabel = $this->getView()->Translate('Select') . ' ' . $this->getView()->Translate($fieldName);
            $element->setEmptyOption($emptyLabel);
            $element->setValueOptions($valueOptions);
        }

        return $element;
    }

    /**
     * If the TableModel holds information about filters that where applied, we pass them into the form element
     *
     * @param Element $element
     * @param $fieldName
     * @return Element
     */
    protected function setElementCurrentValue(Element $element, $fieldName)
    {
        if (!array_key_exists($fieldName, $this->tableModel->getUsedFilterValues())) {
            return $element;
        }

        $element->setValue($this->tableModel->getUsedFilterValues()[$fieldName]);

        return $element;
    }
}
