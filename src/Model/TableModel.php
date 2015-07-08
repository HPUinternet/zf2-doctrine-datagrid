<?php namespace Wms\Admin\DataGrid\Model;

use Wms\Admin\DataGrid\SearchFilter\SearchFilterInterface;

class TableModel
{
    protected $tableHeaders = array();
    protected $tableRows = array();
    protected $tableFilters = array();
    protected $prefetchedFilterValues = array();
    protected $pageNumber = 1;
    protected $maxPageNumber = 1;

    private $dataTypes = array();
    private $optionRoutes = array();

    /**
     * @return TableHeaderCellModel[]
     */
    public function getTableHeaders()
    {
        return $this->tableHeaders;
    }

    /**
     * @return TableRowModel[]
     */
    public function getTableRows()
    {
        return $this->tableRows;
    }

    /**
     * @return TableFilterModel[]
     */
    public function getTableFilters()
    {
        return $this->tableFilters;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * @param int $pageNumber
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    /**
     * @return int
     */
    public function getMaxPageNumber()
    {
        return $this->maxPageNumber;
    }

    /**
     * @param int $maxPageNumber
     */
    public function setMaxPageNumber($maxPageNumber)
    {
        $this->maxPageNumber = $maxPageNumber;
    }

    /**
     * @return array
     */
    public function getOptionRoutes()
    {
        return $this->optionRoutes;
    }

    /**
     * @param array $optionRoutes
     */
    public function setOptionRoutes($optionRoutes)
    {
        $this->optionRoutes = $optionRoutes;
    }

    /**
     * @return array
     */
    public function getDataTypes()
    {
        return $this->dataTypes;
    }

    /**
     * @param array $dataTypes
     */
    public function setDataTypes($dataTypes)
    {
        $this->dataTypes = $dataTypes;
    }

    /**
     * @return array
     */
    public function getPrefetchedFilterValues()
    {
        return $this->prefetchedFilterValues;
    }

    /**
     * @param array $prefetchedFilterValues
     */
    public function setPrefetchedFilterValues($prefetchedFilterValues)
    {
        $this->prefetchedFilterValues = $prefetchedFilterValues;
    }

    /**
     * @param $filterName
     * @return TableFilterModel|bool
     */
    public function getTableFilter($filterName)
    {
        if (isset($this->tableFilters[$filterName])) {
            return $this->tableFilters[$filterName];
        }

        return false;
    }


    /**
     * Parses the custom configurable filter instances into this model instance
     * Also assures the correct TableHeaderCellModels are informed about their filters
     *
     * @param array $filters all configured filters in the module options
     * @param array $currentValues the current filter values
     * @throws \Exception
     */
    public function addFilters(array $filters, $currentValues = array())
    {
        if (empty($this->tableHeaders)) {
            throw new \Exception("Please provide the table model with table header information first");
        }

        // Create a filtermodel for each header
        foreach ($this->getTableHeaders() as $header) {
            $filterName = $header->getName();
            $filter = new TableFilterModel($filterName);
            $header->setFilter($filter);
            $filter->setHeader($header);
            if (array_key_exists($filterName, $filters)) {
                $filter->setInstance($filters[$filterName]);
                unset($filters[$filterName]);
            }

            // Set current value (if applicable)
            if (isset($currentValues[$filterName])) {
                $filter->setSelectedValue($currentValues[$filterName]);
            }

            $this->tableFilters[$filter->getSafeName()] = $filter;

            // Set available values (if applicable)
            if (!empty($this->prefetchedFilterValues)) {
                $values = $this->getPrefetchedValuesByName($filter->getName());
                $values ? $filter->setAvailableValues($values) : null;
            }
        }

        // Create additional filters out of the configuration
        /** @var SearchFilterInterface $filterInstance */
        foreach ($filters as $filterInstance) {
            $filterName = $filterInstance->getFilterName();
            $filter = new TableFilterModel($filterName, $filterInstance);
            $this->tableFilters[$filter->getSafeName()] = $filter;
            if (isset($currentValues[$filterName])) {
                $filter->setSelectedValue($currentValues[$filterName]);
            }
        }

    }

    /**
     * Loads up the a TableFilterModel's available values by checking for the fieldName
     * inside the model PrefetchedFilterValues array.
     *
     * @param $fieldName
     * @return array|bool
     */
    public function getPrefetchedValuesByName($fieldName)
    {
        $fieldNameSegments = explode('.', $fieldName);
        $rootSegment = $fieldNameSegments[0];
        if (!isset($this->prefetchedFilterValues[$rootSegment])) {
            return false;
        }

        $filterValues = array();
        foreach ($this->prefetchedFilterValues[$rootSegment] as $filterValueCollection) {
            if (isset($filterValueCollection[$fieldNameSegments[1]])) {
                $filterValues[] = $filterValueCollection[$fieldNameSegments[1]];
            }
        }

        return $filterValues;
    }

    /**
     * Parses result rows (as an associative array) into the model instance by creating
     * TableRowModels containing TableCellModels. These models will contain the name and value
     * at minimum, but might contain more properties like "visible", "safeName" or their dataTypes
     *
     * @see TableRowModel
     * @see TableModel::tableRows
     * @param array $rowData containing TableRowModel objects
     * @throws \Exception
     */
    public function addRows(array $rowData)
    {
        if (empty($this->tableHeaders)) {
            throw new \Exception("Please provide the table model with table header information first");
        }

        foreach ($rowData as $row) {
            $tableRow = new TableRowModel();
            $tableRow->setCells($this->parseCells($row));
            $this->tableRows[] = $tableRow;
        }
    }

    /**
     * Method used by addRows to recursively parse cellValues into TableCellModels
     *
     * @see TableModel::addRows
     * @see TableCellModel
     * @param array $cells
     * @return array containing TableCellModel objects
     * @throws \Exception
     */
    private function parseCells(array $cells)
    {
        $returnData = array();
        foreach ($cells as $cellName => $cellValue) {
            if (is_array($cellValue)) {
                $returnData = array_merge($returnData, $this->parseCells($cellValue));
                continue;
            }

            // The TableHeader holds 90% of the information, find it in order to resolve the rest
            $tableHeader = $this->getTableHeader($cellName, $cellValue);

            $tableCell = new TableCellModel(
                $tableHeader->getName(),
                $this->extractRowValue($cells, $tableHeader->getSafeName())
            );

            $tableCell->setVisible($tableHeader->isVisible());
            $tableCell->setDataType($tableHeader->getDataType());
            $returnData[$tableHeader->getSafeName()] = $tableCell;
        }

        return $returnData;
    }


    /**
     * @param $name
     * @return TableHeaderCellModel
     * @throws \Exception
     */
    public function getTableHeader($name)
    {
        if (isset($this->tableHeaders[$name])) {
            return $this->tableHeaders[$name];
        }

        return false;
    }

    /**
     * @param array $rowData
     * @param $cellName
     * @return mixed
     * @throws \Exception
     */
    private function extractRowValue(array $rowData, $cellName)
    {
        if (isset($rowData[$cellName]) || is_null($rowData[$cellName])) {
            return $rowData[$cellName];
        }

        throw new \Exception('Could not find a correct value for ' . $cellName);
    }

    /**
     * Parse an array of headers and an array of visible header names into multiple
     * TableHeaderCellModels, stored in the tableHeaders property of this object.
     *
     * @param array $headerData
     * @param array $visibleHeaders
     */
    public function addHeaders(array $headerData, array $visibleHeaders = array())
    {
        if (!empty($visibleHeaders)) {
            $visibleHeaders = $this->flattenHeadersArray($visibleHeaders);
        }

        foreach ($headerData as $header) {
            $newHeader = new TableHeaderCellModel($header, '');

            if (!empty($visibleHeaders)) {
                $newHeader->setVisible(in_array($header, $visibleHeaders));
            }

            if (!empty($this->dataTypes) && isset($this->dataTypes[$header])) {
                $newHeader->setDataType($this->dataTypes[$header]);
            }

            $this->tableHeaders[$newHeader->getSafeName()] = $newHeader;
        }
    }

    /**
     * Because the TableBuilderService will provide us with an nested assocative array
     * of selected values, a simple conversion is needed before parsing them into TableHeaderCellModels
     * internal method used by addHeaders()
     *
     * @see TableModel::addHeaders()
     * @param array $associativeArray
     * @return array
     */
    private function flattenHeadersArray(array $associativeArray)
    {
        $fieldNames = array();
        foreach ($associativeArray as $columnName => $value) {
            if (!is_array($value)) {
                $fieldNames[] = $columnName;
                continue;
            }

            foreach ($value as $selectedColumn => $safeName) {
                $fieldNames[] = $selectedColumn;
            }
        }

        return $fieldNames;
    }
}
