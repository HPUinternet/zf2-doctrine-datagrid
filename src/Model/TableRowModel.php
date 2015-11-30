<?php namespace Wms\Admin\DataGrid\Model;

class TableRowModel
{
    protected $cells;

    /**
     * @param $cellName
     * @return bool|mixed
     */
    public function getCellValue($cellName)
    {
        if ($cell = $this->getCell($cellName)) {
            return $cell->getValue();
        }

        return $cell;
    }

    /**
     * @param $cellName
     * @return TableCellModel|bool
     */
    public function getCell($cellName)
    {
        if (isset($this->cells[$cellName])) {
            return $this->cells[$cellName];
        }

        return null;
    }

    /**
     * @return TableCellModel[]
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @param mixed $cells
     */
    public function setCells($cells)
    {
        $this->cells = $cells;
    }

    /**
     * Add a single tableCell to the cells property
     *
     * @param TableCellModel $cellModel
     */
    public function addCell(TableCellModel $cellModel)
    {
        $this->cells[$cellModel->getName()] = $cellModel;
    }
}
