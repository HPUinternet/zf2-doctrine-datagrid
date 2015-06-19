<?php namespace Wms\Admin\DataGrid\SearchFilter;

use Wms\Admin\DataGrid\Service\QueryBuilderService;
use Zend\Form\ElementInterface;

interface SearchFilterInterface
{
    /**
     * Provides the DataGrid view helper with an instance of a Zend form Element.
     *
     * @return ElementInterface;
     */
    public function getFilterElement();

    /**
     * Returns the value of which GET or POST parameter should be parsed by this SearchFilter
     *
     * @return string
     */
    public function getFilterName();

    /**
     * When adding "new" filters to the table, one could imagine the data should receive a additional field
     * that can indicate if the row passes the filter values.
     *
     * @return mixed
     */
    public function getFilterValue();

    /**
     * Parses the raw searchFilter parameters into QueryBuilder where clauses.
     * Note that the method will must return the same QueryBuilder instance.
     *
     * @param $searchParams
     * @param QueryBuilderService $queryBuilderService
     * @return QueryBuilderService $queryBuilderService
     */
    public function search($searchParams, QueryBuilderService $queryBuilderService);
}
