<?php namespace Wms\Admin\DataGrid\Model;

use Zend\Stdlib\ArrayUtils;

class TableModel
{
    /**
     * @var array
     */
    protected $rows;

    /**
     * @var string
     */
    protected $caption;

    /**
     * @var array
     */
    protected $headerRow;

    /**
     * @var arrray
     */
    protected $attributes;

    /**
     * @param array $rows - Array of rows for the table
     */
    public function __construct(array $rows = array())
    {
        $this->rows = $rows;
    }

    /**
     * @param string $caption Text for the HTML caption tag.
     * @return Table
     */
    public function setCaption($caption)
    {
        $this->caption = (string)$caption;

        return $this;
    }

    /**
     * @return string The HTML caption tag text.
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @return bool
     */
    public function hasCaption()
    {
        return isset($this->caption);
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
     * Determines if table will has a header row.
     *
     * A header row can be set explicitly using setHeaderRow() or by passing an
     * associative array for the table rows.
     *
     * @return bool
     */
    public function hasHeaderRow()
    {
        // If no header row is provided, see if we can get headers from row keys.
        if (!isset($this->headerRow)) {
            return count($this->rows) ? ArrayUtils::hasStringKeys($this->rows[0]) : false;
        }

        return true;
    }

    /**
     * @param array $attributes An associative array of tag attributes. Each key-value pair is an attribute name and value.
     * @return Table
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return bool
     */
    public function hasAttributes()
    {
        return isset($this->attributes);
    }
}