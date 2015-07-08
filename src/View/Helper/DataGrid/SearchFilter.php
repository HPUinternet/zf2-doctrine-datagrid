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
        $filter = $this->tableModel->getTableFilter($tableHeader->getName());
        if ($filter && !is_null($filter->getInstance())) {
            $filterElement = $tableHeader->getFilter()->getInstance()->getFilterElement();
            $filterElement->setName('search[' . $filterElement->getName() . ']');
            $this->setElementCurrentValue($filterElement, $tableHeader->getSafeName());
            return $this->getView()->formElement($filterElement);
        }

        $dataType = $tableHeader->getDataType();
        if ($this->tableModel->getPrefetchedValuesByName($tableHeader->getName())) {
            $dataType = "Array";
        }

        $filterName = 'search[' . $tableHeader->getName() . ']';
        $element = $this->dataStrategyResolver->displayFilterForDataType($filterName, $dataType);
        if ($element instanceof Element) {
            $this->setElementCurrentValue($element, $tableHeader->getSafeName());
            if ($dataType == "Array") {
                $this->setElementValues($element, $tableHeader->getName());
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
        $values = $this->tableModel->getPrefetchedValuesByName($fieldName);
        if ($values && method_exists($element, 'setValueOptions')) {
            $valueOptions = array();
            foreach ($values as $value) {
                if ($value instanceof \DateTime) {
                    $valueOptions[$value->format('Y-m-d')] = $value->format('Y-m-d');
                    continue;
                }

                $valueOptions[$value] = $value;
            }

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
        $filter = $this->tableModel->getTableFilter($fieldName);
        if ($filter && !empty($filter->getSelectedValue())) {
            $element->setValue($filter->getSelectedValue());
        }

        return $element;
    }
}
