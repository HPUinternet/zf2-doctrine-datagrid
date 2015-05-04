<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

class StringStrategy implements DataStrategyInterface {

    public function parse($data)
    {
        echo $data;
    }
}