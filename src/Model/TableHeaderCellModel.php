<?php namespace Wms\Admin\DataGrid\Model;

class TableHeaderCellModel extends TableCellModel
{
    /** @var TableFilterModel */
    protected $filter = null;
    /** @var int  */
    protected $width = 0;
    /** @var bool */
    protected $orderable = true;

    /**
     * @return TableFilterModel
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return boolean
     */
    public function isOrderable()
    {
        return $this->orderable;
    }

    /**
     * @param boolean $orderable
     */
    public function setOrderable($orderable)
    {
        $this->orderable = $orderable;
    }
}
