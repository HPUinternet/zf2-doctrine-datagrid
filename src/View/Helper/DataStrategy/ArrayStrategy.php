<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;

class ArrayStrategy implements DataStrategyInterface, DataStrategyFilterInterface
{

    /**
     * @var StrategyResolver;
     */
    private $delegator;

    /**
     * Create a new instance of the ArrayStrategy
     *
     * @param StrategyResolver $delegator
     */
    public function __construct(StrategyResolver $delegator)
    {
        $this->delegator = $delegator;
    }

    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @return mixed
     */
    public function parse($data)
    {
        $html = '<ol>';
        foreach ($data as $value) {
            $html .= '<li>';
            $html .= $this->delegator->resolveAndParse($value);
            $html .= '</li>';
        }
        $html .= '</ol>';

        return $html;
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
