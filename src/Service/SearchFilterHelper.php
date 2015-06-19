<?php namespace Wms\Admin\DataGrid\Service;

class SearchFilterHelper
{

    /**
     * @var array
     */
    private $filters;

    /**
     * @param array $filters
     */
    public function __construct($filters = array())
    {
        $this->filters = $filters;
    }

    /**
     * Checks if any filter is configured for the field
     *
     * @param $fieldName
     * @return bool
     */
    public function hasFilterForField($fieldName)
    {
        if (isset($this->filters[$fieldName]) && !empty($this->filters[$fieldName])) {
            return true;
        }

        return false;
    }

    /**
     * Invokes the search method of the filter
     *
     * @param $fieldName
     * @param $searchParam
     * @param QueryBuilderService $queryBuilderService
     * @return mixed
     */
    public function useFilterForField($fieldName, $searchParam, QueryBuilderService $queryBuilderService)
    {
        $filterInstance = $this->filters[$fieldName];

        return $filterInstance->search($searchParam, $queryBuilderService);
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }
}