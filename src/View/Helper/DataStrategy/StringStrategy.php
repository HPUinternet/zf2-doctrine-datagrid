<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Wms\Admin\DataGrid\View\Helper\DataStrategy\DataStrategyInterface;

class StringStrategy implements DataStrategyInterface {

    public function parse($data)
    {
        echo $data;
    }
}