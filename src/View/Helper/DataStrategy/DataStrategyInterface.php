<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

interface DataStrategyInterface
{
    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @return mixed
     */
    public function parse($data);
}
