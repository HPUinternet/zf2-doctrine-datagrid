<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

class IntegerStrategy implements DataStrategyInterface
{

    public function parse($data)
    {
        echo $data;
    }
}
