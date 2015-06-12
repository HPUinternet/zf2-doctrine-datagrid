<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

class IntegerStrategy implements DataStrategyInterface
{

    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @return mixed
     */
    public function parse($data)
    {
        return $data;
    }
}
