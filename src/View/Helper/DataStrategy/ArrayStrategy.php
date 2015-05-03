<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Wms\Admin\DataGrid\View\Helper\DataStrategy\DataStrategyInterface;
use Wms\Admin\DataGrid\View\Helper\DataStrategy\StrategyResolver;

class ArrayStrategy implements DataStrategyInterface {

    /**
     * @var StrategyResolver;
     */
    private $delegator;

    public function __construct(StrategyResolver $delegator) {
        $this->delegator = $delegator;
    }

    public function parse($data)
    {
        echo '<ol>';
        foreach ($data as $value) {
            echo '<li>';
            echo $this->delegator->resolveAndParse($value);
            echo '</li>';
        }
        echo '</ol>';
    }
}