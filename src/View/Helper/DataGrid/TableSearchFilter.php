<?php namespace Wms\Admin\DataGrid\View\Helper\DataGrid;

use Wms\Admin\DataGrid\Model\TableModel;
use Zend\Di\ServiceLocator;
use Zend\Form\Element;
use Zend\Form\ElementInterface;

abstract class TableSearchFilter
{
    /**
     * converts TableFilterModels into html by resolving them through a filter instance or
     * calling the data resolver for a zend form element
     *
     * @param Table $tableHelper
     * @return string
     */
    public static function printSearchFilter(Table $tableHelper)
    {
        $output = '<tr class="simpleSearch tabelZoekBalk">';

        $elements = self::getFilterElements($tableHelper);
        foreach ($elements as $element) {
            $output .= '<td>' . $tableHelper->getView()->formElement($element) . '</td>';
        }

        $output .= '<td>
                    <span class="pull-right">
                        <button type="submit" class="btn knopSearch">
                            <span class="glyphicon glyphicon-search"></span> Search
                        </button>
                    </span>
                    </td>';
        $output .= '</tr>';

        return $output;
    }

    /**
     * Resolves tableHeaders and additional filters into a single array of
     * Zend ElementInterface forms
     *
     * @param Table $tableHelper
     * @return \Zend\Form\ElementInterface[]
     */
    public static function getFilterElements(Table $tableHelper)
    {
        $elements = array();

        // Get all filters that can be placed above a header
        foreach ($tableHelper->getDisplayedHeaders() as $tableHeader) {
            $filter = $tableHeader->getFilter();

            if ($filter) {
                // Are you a extra configured filter, good we'll call you directly
                if (!is_null($filter->getInstance())) {
                    $filterElement = $filter->getInstance()->getFilterElement();
                    $filterElement->setName('search[' . $filterElement->getName() . ']');
                    $elements[] = $filterElement;
                    continue;
                }

                // No filter instance has been configured, create a new FormElement throughout the strategy resolver
                $dataType = $tableHeader->getDataType();
                if ($tableHelper->getTableModel()->getPrefetchedValuesByName($tableHeader->getName())) {
                    $dataType = "Array";
                }

                $filterName = 'search[' . $filter->getName() . ']';
                $element = $tableHelper->getDataStrategyResolver()->displayFilterForDataType($filterName, $dataType);
                self::setElementCurrentValue($element, $tableHeader->getSafeName(), $tableHelper->getTableModel());
                if ($dataType == "Array") {
                    self::setElementValues($element, $tableHeader->getName(), $tableHelper);
                }
            } else {
                $element = new Element\Hidden('search[' . $tableHeader->getName() . ']');
            }

            $elements[] = $element;
        }

        // Append additional filters
        foreach ($tableHelper->getAdditionalFilters() as $filter) {
            $filterElement = $filter->getInstance()->getFilterElement($filter);
            $filterElement->setName('search[' . $filterElement->getName() . ']');
            $elements[] = $filterElement;
            continue;
        }

        return $elements;
    }

    /**
     * If the TableModel contains FilterData this method will fill the element with these values
     *
     * @param ElementInterface $element
     * @param $fieldName
     * @param Table $tableHelper
     * @return Element
     */
    public static function setElementValues(ElementInterface $element, $fieldName, Table $tableHelper)
    {
        $values = $tableHelper->getTableModel()->getPrefetchedValuesByName($fieldName);
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
            $emptyLabel =
                $tableHelper->getView()->Translate('Select') . ' ' .
                $tableHelper->getView()->Translate($fieldName);
            $element->setEmptyOption($emptyLabel);
        }

        return $element;
    }

    /**
     * If the TableModel holds information about filters that where applied, we pass them into the form element
     *
     * @param ElementInterface $element
     * @param string $fieldName
     * @param TableModel $tableModel
     * @return Element
     */
    public static function setElementCurrentValue(ElementInterface $element, $fieldName, TableModel $tableModel)
    {
        $filter = $tableModel->getTableFilter($fieldName);
        if ($filter && !empty($filter->getSelectedValue())) {
            $element->setValue($filter->getSelectedValue());
        }

        return $element;
    }
}
