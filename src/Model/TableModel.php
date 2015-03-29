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
    protected $headerRow;

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
     * Set Rows from RAW object data.
     * The model will parse every attribute of the data object to a printable value.
     * Initially made for parsing doctrine result object to a table.
     * @param array $rows
     * @return $this
     * @throws \Exception
     */
    public function setAndParseRows(array $rows)
    {
        foreach ($rows as $row) {
            $newRow = array();
            foreach ($this->getHeaderRow() as $columnName => $columnProperties) {
                $cellValue = $this->extractProperty($row, $columnName);
                $newRow[$columnName] = $this->preParseCellValue($cellValue);
            }
            $this->rows[] = $newRow;
        }
        return $this;
    }

    /**
     * @param array $headerRow
     * @return Table
     */
    public function setHeaderRow(array $headerRow)
    {
        $this->headerRow = $headerRow;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaderRow()
    {
        // If no header row is provided, get headers from 1st rows row.
        if (!isset($this->headerRow)) {
            if (!count($this->rows)) {
                return array();
            }

            return array_combine(array_keys($this->rows[0]), array_keys($this->rows[0]));
        }

        return $this->headerRow;
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

}