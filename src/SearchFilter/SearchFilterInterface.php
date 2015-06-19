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
     * Parses the raw searchFilter parameters into QueryBuilder where clauses.
     * Note that the method will must return the same QueryBuilder instance.
     *
     * @param $searchParams
     * @param QueryBuilderService $queryBuilderService
     * @return QueryBuilderService $queryBuilderService
     */
    public function search($searchParams, QueryBuilderService $queryBuilderService);
}
