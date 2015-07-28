<?php namespace Wms\Admin\DataGrid\Service;

use Wms\Admin\DataGrid\View\Helper\DataGrid\Table;

/**
 * Sometimes you want to influence the way you dataTable is build without touching your actual QueryBuilder.
 * In this case, creating a class and implementing this interface is enough to replace the TableBuilderService
 * instance in your DataGrid Controller Plugin.
 *
 * Interface TableBuilderInterface
 * @package Wms\Admin\DataGrid\Service\Interfaces
 */
interface TableBuilderInterface
{
    /**
     * Retrieve an new TableModel
     * based on your data configuration in this object
     * @return Table
     */
    public function getTable();

    /**
     * Select visible columns in your table
     *
     * @param array $columns
     * @throws \Exception
     */
    public function selectColumns(array $columns);

    /**
     * Set the current pageNumber
     *
     * @param $pageNumber
     */
    public function setPage($pageNumber);

    /**
     * Order data in your table by a specific column
     *
     * @param $column
     * @param $order
     */
    public function orderBy($column, $order);

    /**
     * search for data on request basis
     *
     * @param array $searchParams
     */
    public function search(array $searchParams);
}
