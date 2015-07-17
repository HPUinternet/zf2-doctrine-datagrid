<?php namespace Wms\Admin\DataGrid\SearchFilter;

use Wms\Admin\DataGrid\Model\TableFilterModel;
use Wms\Admin\DataGrid\Model\TableRowModel;
use Wms\Admin\DataGrid\Service\QueryBuilderService;
use Zend\Form\ElementInterface;

interface SearchFilterInterface
{
    /**
     * Provides the DataGrid view helper with an instance of a Zend form Element.
     *
     * @param TableFilterModel $tableFilterModel
     * @return ElementInterface
     */
    public function getFilterElement(TableFilterModel $tableFilterModel);

    /**
     * Parses the raw searchFilter parameters into QueryBuilder where clauses.
     * Note that the method will must return the same QueryBuilder instance.
     *
     * @param $searchParams
     * @param QueryBuilderService $queryBuilderService
     */
    public function search($searchParams, QueryBuilderService $queryBuilderService);

    /**
     * Returns the value of which GET or POST parameter should be parsed by this SearchFilter
     *
     * @return string
     */
    public function getFilterName();

    /**
     * When adding "new" filters to the table, one could imagine the data should receive a additional field
     * that can indicate if the row passes the filter values. Please note that
     *
     * @param TableRowModel $rowData current result row that is being parsed by the DataGrid table
     * @return mixed
     */
    public function getFilterCellValue(TableRowModel $rowData);
}
