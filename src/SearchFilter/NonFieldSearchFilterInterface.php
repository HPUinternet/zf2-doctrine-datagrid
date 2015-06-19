<?php namespace Wms\Admin\DataGrid\SearchFilter;

use Wms\Admin\DataGrid\Service\QueryBuilderService;
use Zend\Form\ElementInterface;

interface NonFieldSearchFilterInterface extends SearchFilterInterface
{
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
}
