<?php namespace Wms\Admin\DataGrid\Model;

class TableModel
{
    /**
     * @var array
     */
    protected $rows;

    /**
     * @var array
     */
    protected $usedHeaders;

    /**
     * @var Array
     */
    protected $availableHeaders;

    /**
     * @var Array
     */
    protected $availableFilterValues;

    /**
     * @var Array
     */
    protected $usedFilterValues;

    /**
     * @var Array
     */
    protected $dataTypes;

    /**
     * @var int
     */
    protected $pageNumber;

    /**
     * @var int
     */
    protected $maxPageNumber;

    /**
     * Create a new instance of the TableModel
     */
    public function __construct()
    {
        $this->rows = array();
        $this->availableHeaders = array();
        $this->usedHeaders = array();
        $this->dataTypes = array();
        $this->pageNumber = 1;
        $this->maxPageNumber = 1;
    }

    /**
     * Set Rows from RAW object data.
     * The model will parse every attribute of the data object to a printable value.
     * Initially made for parsing doctrine result object to a table.
     * @param array $rows
     * @return $this
     * @throws \Exception
     */
    public function setAndParseRows(array $rows)
    {
        if (empty($rows)) {
            return $this;
        }

        // You should avoid bulding a table without the heading's
        // the first result element can contain invalid data, which will result in data being trimmed from all results
        if (empty($this->usedHeaders)) {
            $this->setUsedHeaders($this->calculateTableHeader(reset($rows)));
        }

        foreach ($rows as $row) {
            $newRow = array();
            foreach ($this->getUsedHeaders() as $columnName => $accessor) {
                $cellValue = $this->extractProperty($row, $columnName, $accessor);
                $newRow[$columnName] = $cellValue;
            }
            $this->rows[] = $newRow;
        }

        return $this;
    }

    /**
     * Resolve the data accessors by parsing an resultrow
     * This will return a array with the used table headings and accessors like the example with a person object below:
     *
     * array(
     *  name => name (the property name of the result person array/object is accessible by calling it directly)
     *  photo.name => photos (one person can have multiple photos. So the data for all the photos for the
     * );                     result person array/object is accessible in the property "photos")
     *
     * @param array $row
     * @return array
     */
    public function calculateTableHeader(array $row, $availableHeaders = array(), $accessorProperty = false)
    {
        // To prevent nested foreach loops, first rebuild the available headers
        if (empty($availableHeaders)) {
            foreach ($this->availableHeaders as $availableHeader) {
                $availableHeaders[] = str_replace(".", "", $availableHeader);
            }
        }

        $tableHeaders = array();
        foreach ($row as $property => $value) {
            $indexKey = array_search($property, $availableHeaders);
            if ($indexKey !== false) {
                $accessProperty = $accessorProperty ? $accessorProperty : $this->availableHeaders[$indexKey];
                $tableHeaders[$this->availableHeaders[$indexKey]] = $accessProperty;
                continue;
            }

            if (is_array($value)) {
                if (isset($value[0]) && is_array($value[0])) {
                    $value = $value[0];
                }
                $tableHeaders = array_merge(
                    $tableHeaders,
                    $this->calculateTableHeader($value, $availableHeaders, $property)
                );
                continue;
            }
        }

        return $tableHeaders;
    }

    /**
     * Extract property from a result array
     *
     * @param $data
     * @param $property
     * @param $accessor
     * @return array
     * @throws \Exception
     */
    protected function extractProperty($data, $property, $accessor)
    {
        if (is_array($data)) {
            $accessor = str_replace(".", "", $accessor);
            $property = str_replace(".", "", $property);

            if ($accessor == $property) {
                return isset($data[$property]) ? $data[$property] : null;
            }

            // See if we have nested data
            if (isset($data[$accessor]) && is_array($data[$accessor])) {
                $resultData = array();
                foreach ($data[$accessor] as $accessorData) {
                    $resultData[] = $accessorData[$property];
                }

                return $resultData;
            }
        }
    }

    /**
     * Resolve a datatype by a configured tableheader
     *
     * @param $headerName
     * @return bool
     */
    public function getDataTypeByHeader($headerName)
    {
        if (array_key_exists($headerName, $this->dataTypes)) {
            return $this->dataTypes[$headerName];
        }

        return false;
    }

    /**
     * Check if the columnName is configured to be hidden
     *
     * @param $columnName
     * @return bool
     */
    public function isHiddenColumn($columnName)
    {
        if (array_key_exists($columnName, $this->getUsedHeaders())) {
            return false;
        }

        return true;
    }

    /**
     * @param array $rows
     * @return Table
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return array
     */
    public function getUsedHeaders()
    {
        return $this->usedHeaders;
    }

    /**
     * @param array $usedHeaders
     */
    public function setUsedHeaders($usedHeaders)
    {
        $this->usedHeaders = $usedHeaders;
    }

    /**
     * @return Array
     */
    public function getAvailableHeaders()
    {
        return $this->availableHeaders;
    }

    /**
     * @param Array $availableHeaders
     */
    public function setAvailableHeaders($availableHeaders)
    {
        $this->availableHeaders = $availableHeaders;
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
     * @return Array
     */
    public function getDataTypes()
    {
        return $this->dataTypes;
    }

    /**
     * @param Array $dataTypes
     */
    public function setDataTypes($dataTypes)
    {
        $this->dataTypes = $dataTypes;
    }

    /**
     * @return Array
     */
    public function getAvailableFilterValues()
    {
        return $this->availableFilterValues;
    }

    /**
     * @param Array $availableFilterValues
     */
    public function setAvailableFilterValues($availableFilterValues)
    {
        $this->availableFilterValues = $availableFilterValues;
    }

    /**
     * @return Array
     */
    public function getUsedFilterValues()
    {
        return $this->usedFilterValues;
    }

    /**
     * @param Array $usedFilterValues
     */
    public function setUsedFilterValues($usedFilterValues)
    {
        $this->usedFilterValues = $usedFilterValues;
    }
}
