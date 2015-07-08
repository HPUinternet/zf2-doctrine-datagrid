<?php namespace Wms\Admin\DataGrid\Model;

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

    public function getTableHeaders()
    {
        return $this->tableHeaders;
    }

    public function getTableRows()
    {
        return $this->tableRows;
    }

    public function getTableFilters()
    {
        return $this->tableFilters;
    }

    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    public function getMaxPageNumber()
    {
        return $this->maxPageNumber;
    }

    public function setMaxPageNumber($maxPageNumber)
    {
        $this->maxPageNumber = $maxPageNumber;
    }

    public function getOptionRoutes()
    {
        return $this->optionRoutes;
    }

    public function setOptionRoutes($optionRoutes)
    {
        $this->optionRoutes = $optionRoutes;
    }

    public function getDataTypes()
    {
        return $this->dataTypes;
    }

    public function setDataTypes($dataTypes)
    {
        $this->dataTypes = $dataTypes;
    }

    public function getPrefetchedFilterValues()
    {
        return $this->prefetchedFilterValues;
    }

    public function setPrefetchedFilterValues($prefetchedFilterValues)
    {
        $this->prefetchedFilterValues = $prefetchedFilterValues;
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
        $allFilters = array_merge($currentValues, $filters);
        foreach ($allFilters as $fieldName => $filterInstance) {
            $filter = new TableFilterModel($fieldName);
            if (array_key_exists($fieldName, $filters)) {
                $filter->setInstance($filterInstance);
            }

            // Associate TableHeaderCell with the filter
            if (array_key_exists($filter->getSafeName(), $this->tableHeaders)) {
                $header = $this->getTableHeader($filter->getSafeName());
                $filter->setHeader($header);
                $header->setFilter($filter);
            }

            // Set current value (if applicable)
            if (isset($currentValues[$fieldName])) {
                $filter->setSelectedValue($currentValues[$fieldName]);
                unset($currentValues[$fieldName]);
            }

            $this->tableFilters[$filter->getSafeName()] = $filter;

            // Set available values (if applicable)
            if (!empty($this->prefetchedFilterValues)) {
                $values = $this->getPrefetchedValuesByName($filter->getName());
                $values ? $filter->setAvailableValues($values) : null;
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
            ;

            return false;
        }

        $filterValues = array();
        foreach ($this->prefetchedFilterValues[$rootSegment] as $filterValueCollection) {
            $filterValues[$filterValueCollection[$fieldNameSegments[1]]] = $filterValueCollection[$fieldNameSegments[1]];
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
            $returnData[$tableHeader->getName()] = $tableCell;
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

        throw new \Exception('Could not find a configured tableHeader for field ' . $name);
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
