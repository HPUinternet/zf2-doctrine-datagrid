<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

interface RecursiveDataStrategyInterface extends DataStrategyInterface
{
    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @param $fieldName
     * @return mixed
     */
    public function recursiveParse($data, $fieldName);
}
