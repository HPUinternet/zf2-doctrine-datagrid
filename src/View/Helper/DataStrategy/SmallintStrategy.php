<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

class SmallintStrategy implements DataStrategyInterface
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
