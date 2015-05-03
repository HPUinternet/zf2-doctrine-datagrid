<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Wms\Admin\DataGrid\View\Helper\DataStrategy\DataStrategyInterface;

class ArrayStrategy implements DataStrategyInterface {

    public function parse($data)
    {
        echo '<ol>';
        foreach ($data as $value) {
            echo sprintf('<li>%s</li>', $value);
        }
        echo '</ol>';
    }
}