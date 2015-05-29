<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;

class ArrayStrategy implements DataStrategyInterface, DataStrategyFilterInterface {

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

    /**
     * returns a input element for the inline filter
     *
     * @param $elementName
     * @return string|ElementInterface
     */
    public function showFilter($elementName)
    {
       return new Select($elementName);
    }
}