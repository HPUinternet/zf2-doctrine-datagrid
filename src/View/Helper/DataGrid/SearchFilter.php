<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;

use Wms\Admin\DataGrid\Model\TableHeaderCellModel;
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
     * @return string
     */
    public function __invoke(TableModel $tableModel, StrategyResolver $dataStrategyResolver)
    {
        $this->tableModel = $tableModel;
        $this->dataStrategyResolver = $dataStrategyResolver;

        $output = '<tr class="simpleSearch tabelZoekBalk">';
        $output .= $this->printFilterFields();
        $output .= '<td>
                    <span class="pull-right">
                        <button type="submit" class="btn knopSearch">
                            <span class="glyphicon glyphicon-search"></span> Search
                        </button>
                    </span>
                    </td>';
        $output .= '</tr>';
        $output .= '</thead>';

        return $output;
    }

    /**
     * Prints the form elements per field
     */
    public function printFilterFields()
    {
        $html = '';
        // Get the configured filter foreach table header
        foreach ($this->tableModel->getTableHeaders() as $tableHeader) {
            if ($tableHeader->isVisible()) {
                $html .= '<td>';
                $html .= $this->getFilterElement($tableHeader);
                $html .= '</td>';
            }
        }

        // Get additional search filters that do not belong to a table header
        foreach ($this->tableModel->getTableFilters() as $filter) {
            if (is_null($filter->getHeader())) {
                $filterElement = $filter->getInstance()->getFilterElement();
                $filterElement = $this->setElementCurrentValue($filterElement, $filterElement);
                $filterElement->setName('search[' . $filterElement->getName() . ']');

                $html .= '<td>';
                $html .= $this->getView()->formElement($filterElement);
                $html .= '</td>';
            }
        }

        return $html;
    }

    /**
     * @param TableHeaderCellModel $tableHeader
     * @return string|Element
     */
    protected function getFilterElement($tableHeader)
    {
        if (!is_null($tableHeader->getFilter()) && !is_null($tableHeader->getFilter()->getInstance())) {
            $filterElement = $tableHeader->getFilter()->getFilterElement();
            $filterElement->setName('search[' . $filterElement->getName() . ']');
            $filterElement = $this->setElementCurrentValue($filterElement, $tableHeader);

            return $this->getView()->formElement($filterElement);
        }

        $dataType = $tableHeader->getDataType();
        if ($tableHeader->getName() != $tableHeader->getAccessor()) {
            $dataType = "Array";
        }

        $filterName = 'search[' . $tableHeader->getName() . ']';
        $element = $this->dataStrategyResolver->displayFilterForDataType($filterName, $dataType);
        if ($element instanceof Element) {
            $element = $this->setElementValues($element, $tableHeader->getName());

            if (!is_null($tableHeader->getFilter()) && !empty($tableHeader->getFilter()->getAvailableValues())) {
                $element = $this->setElementCurrentValue($element, $tableHeader);
            }

            return $this->getView()->formElement($element);
        }

        return $element;
    }

    /**
     * If the TableModel contains FilterData this method will fill the element with these values
     *
     * @param Element $element
     * @param $fieldName
     * @return Element
     */
    protected function setElementValues(Element $element, $fieldName)
    {
        $valueOptions = $this->tableModel->getPrefetchedValuesByName($fieldName);
        if ($valueOptions && method_exists($element, 'setValueOptions')) {
            $element->setValueOptions($valueOptions);
        }

        if (method_exists($element, 'setEmptyOption')) {
            $emptyLabel = $this->getView()->Translate('Select') . ' ' . $this->getView()->Translate($fieldName);
            $element->setEmptyOption($emptyLabel);
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
        //        if (!array_key_exists($fieldName, $this->tableModel->getUsedFilterValues())) {
//            return $element;
//        }
//
//        $element->setValue($this->tableModel->getUsedFilterValues()[$fieldName]);

        return $element;
    }
}
