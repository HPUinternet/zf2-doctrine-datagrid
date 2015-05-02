<?php namespace Wms\Admin\DataGrid\Model;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Proxy\Proxy;

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
     * @var int
     */
    protected $pageNumber;

    /**
     * @var int
     */
    protected $maxPageNumber;

    public function __construct()
    {
        $this->rows = array();
        $this->availableHeaders = array();
        $this->usedHeaders = array();
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

        $this->setUsedHeaders($this->buildTableHeaderFromRow(reset($rows)));
        foreach ($rows as $row) {
            $newRow = array();
            foreach ($this->getUsedHeaders() as $columnName => $accessor) {
                $cellValue = $this->extractProperty($row, $columnName, $accessor);
                $newRow[$columnName] = $this->preParseCellValue($cellValue);
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
    private function buildTableHeaderFromRow(array $row, $availableHeaders = array(), $accessorProperty = false)
    {
        // To prevent nested foreach loops, first rebuild the available headers
        if (empty($availableHeaders)) {
            foreach ($this->availableHeaders as $availableHeader) {
                $availableHeaders[] = str_replace(".", "", $availableHeader);
            }
        }

        $tableHeaders = array();
        foreach ($row as $property => $value) {
            // Find index by searching for the Key in the available headers
            $indexKey = array_search($property, $availableHeaders);
            if ($indexKey !== false) {
                $accessProperty = $accessorProperty ? $accessorProperty : $this->availableHeaders[$indexKey];
                $tableHeaders[$this->availableHeaders[$indexKey]] = $accessProperty;
                continue;
            }

            // If the data is an array (when data is joined) validate the first array value
            if (is_array($value) && array_search(key($value[0]), $availableHeaders)) {
                $tableHeaders = array_merge(
                    $tableHeaders,
                    $this->buildTableHeaderFromRow(reset($value), $availableHeaders, $property)
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
                return $data[$property];
            }

            // See if we have nested data
            if (is_array($data[$accessor])) {
                $resultData = array();
                foreach ($data[$accessor] as $accessorData) {
                    $resultData[] = $accessorData[$property];
                }

                return $resultData;
            }
        }

        throw new \Exception(sprintf('Failed extracting %s out of the result set', $property));
    }

    /**
     * Prepare the data for parsing by the viewHelper
     * Please note that, other parsing (like the datetime objects and arrays should be done in the viewHelper)
     *
     * @param $cellData
     * @return array
     */
    protected function preParseCellValue($cellData)
    {
        if ($cellData instanceof PersistentCollection) {
            return $cellData->getValues();
        }

        if ($cellData instanceof Proxy) {
            return '@todo handle proxy classes';
        }

        return $cellData;
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

}