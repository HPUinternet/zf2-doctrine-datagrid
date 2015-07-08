<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;

use Wms\Admin\DataGrid\Model\TableFilterModel;
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
     * @var TableHeaderCellModel[]
     */
    private $tableHeaders = array();

    /**
     * @var TableFilterModel[]
     */
    private $additionalFilters = array();

    /**
     * @var StrategyResolver
     */
    private $dataStrategyResolver;

    /**
     * If configured, this view helper will print the inline simple filter right after the
     * initial table heading.
     *
     * @param TableModel $tableModel
     * @param array $tableHeaders
     * @param array $additionalFilters
     * @param StrategyResolver $dataStrategyResolver
     * @return string
     */
    public function __invoke(
        TableModel $tableModel,
        array $tableHeaders,
        array $additionalFilters,
        StrategyResolver $dataStrategyResolver
    ) {
        $this->tableModel = $tableModel;
        $this->tableHeaders = $tableHeaders;
        $this->additionalFilters = $additionalFilters;
        $this->dataStrategyResolver = $dataStrategyResolver;

        return $this->invoke();
    }

    public function invoke()
    {
        $output = '<tr class="simpleSearch tabelZoekBalk">';

        $elements = $this->getFilterElements();
        foreach ($elements as $element) {
            $output .= '<td>' . $element . '</td>';
        }

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

    protected function getFilterElements()
    {
        $elements = array();

        // Get all filters that can be placed above a header
        foreach ($this->tableHeaders as $tableHeader) {
            $filter = $tableHeader->getFilter();

            // Are you a extra configured filter, good we'll call you directly
            if (!is_null($filter->getInstance())) {
                $filterElement = $filter->getInstance()->getFilterElement();
                $filterElement->setName('search[' . $filterElement->getName() . ']');
                $elements[] = $this->getView()->formElement($filterElement);
                continue;
            }

            // No filter instance has been configured, create a new FormElement throughout the strategy resolver
            $dataType = $tableHeader->getDataType();
            if ($this->tableModel->getPrefetchedValuesByName($tableHeader->getName())) {
                $dataType = "Array";
            }

            $filterName = 'search[' . $filter->getName() . ']';
            $element = $this->dataStrategyResolver->displayFilterForDataType($filterName, $dataType);
            $this->setElementCurrentValue($element, $tableHeader->getSafeName());
            if ($dataType == "Array") {
                $this->setElementValues($element, $tableHeader->getName());
            }

            $elements[] = $this->getView()->formElement($element);
        }

        // Append additional filters
        foreach ($this->additionalFilters as $filter) {
            $filterElement = $filter->getInstance()->getFilterElement($filter);
            $filterElement->setName('search[' . $filterElement->getName() . ']');
            $elements[] = $this->getView()->formElement($filterElement);
            continue;
        }

        return $elements;
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
     * @param string $fieldName
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
