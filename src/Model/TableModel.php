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
        $this->setUsedHeaders($this->buildTableHeaderFromRow(reset($rows)));
        foreach ($rows as $row) {
            $newRow = array();
            foreach ($this->getUsedHeaders() as $columnName) {
                $cellValue = $this->extractProperty($row, str_replace(".", "", $columnName));
                $newRow[$columnName] = $this->preParseCellValue($cellValue);
            }
            $this->rows[] = $newRow;
        }

        return $this;
    }

    /**
     * @param array $row
     * @return array
     */
    private function buildTableHeaderFromRow(array $row) {
        // To prevent nested foreach loops, first rebuild the available headers
        $availableHeaders = array();
        foreach($this->availableHeaders as $availableHeader) {
            $availableHeaders[] = str_replace(".", "", $availableHeader);
        }

        $tableHeaders = array();
        foreach ($row as $property => $value) {
            $indexKey = array_search($property, $availableHeaders);
            if($indexKey) {
                $tableHeaders[] = $this->availableHeaders[$indexKey];
            }
        }
        return $tableHeaders;
    }

    /**
     * Get property of object using several ways of extraction
     *
     * @param $class
     * @param $propertyName
     * @return bool|mixed
     * @throws \Exception
     */
    private function extractProperty($class, $propertyName)
    {
        if (is_array($class)) {
            $propertyName = str_replace(".", "", $propertyName);

            return $class[$propertyName];
        }

        $propertyNameSegments = explode(".", $propertyName);
        $propertyName = reset($propertyNameSegments);

        if (!property_exists($class, $propertyName)) {
            throw new \Exception (
                sprintf("Expected %s to contain a property named: %s, but it didn't", get_class($class), $propertyName)
            );
        }

        $getter = sprintf('get%s', ucfirst($propertyName));
        $propertyValue = method_exists($class, $getter) ? $class->$getter() : false;
        if ($propertyValue !== false) {
            return $propertyValue;
        }

        // if the normal getter method fails (due to weird naming conventions or sub-classed protected properties)
        // try to retrieve the property value through reflection
        $reflectionClass = new \ReflectionClass($class);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        if (!$reflectionProperty->isPrivate()) {
            $reflectionProperty->setAccessible(true);

            return $reflectionProperty->getValue($class);
        }

        throw new \Exception (
            sprintf("Cant get %s property in class %s. Is the property private?", $propertyName, get_class($class))
        );
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

}