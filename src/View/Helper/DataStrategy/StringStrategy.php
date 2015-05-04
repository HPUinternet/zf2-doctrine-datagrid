<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

class StringStrategy implements DataStrategyInterface {

    private $maxLength = 32;

    public function parse($data)
    {
        if(strlen($data) > $this->maxLength) {
            echo sprintf('%s...', mb_substr($data, 0, $this->maxLength));
            return;
        }
        echo $data;
    }
}