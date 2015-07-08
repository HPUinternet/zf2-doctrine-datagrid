<?php namespace Wms\Admin\DataGrid\Model;

class TableHeaderCellModel extends TableCellModel
{
    protected $filter = null;

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
}
