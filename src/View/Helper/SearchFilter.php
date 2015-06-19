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

        echo '<tr class="simpleSearch">';
        echo $this->printFilterFields();
        echo '<td>
                <button type="submit" class="btn btn-primary max-width">
                    <span class="glyphicon glyphicon-search"></span> Search
                </button>
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

            if (isset($this->tableModel->getFilters()[$tableHeader])) {
                echo $this->getView()->formElement($this->tableModel->getFilters()[$tableHeader]->getFilterElement());
                continue;
            }

            $dataType = $this->tableModel->getDataTypeByHeader($tableHeader);
            if ($tableHeader != $accessor) {
                $dataType = "Array";
            }

            $element = $this->dataStrategyResolver->displayFilterForDataType('search[' . $tableHeader . ']', $dataType);
            if ($element instanceof Element) {
                $element = $this->setElementValues($element, $tableHeader);
                $element = $this->setElementCurrentValue($element, $tableHeader);
                echo $this->getView()->formElement($element);
            } else {
                echo $element;
            }

            echo '</td>';
        }

        foreach ($this->tableModel->getNonFieldFilters() as $filter) {
            $filterElement = $filter->getFilterElement();
            $filterElement->setName('search[' . $filterElement->getName() . ']');

            echo '<td>';
            echo $this->getView()->formElement($filterElement);
            echo '</td>';
        }
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