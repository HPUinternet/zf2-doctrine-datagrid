<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;

class ArrayStrategy implements RecursiveDataStrategyInterface, DataStrategyFilterInterface
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
        return $this->recursiveParse($data, null);
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

    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @param $fieldName
     * @return mixed
     */
    public function recursiveParse($data, $fieldName)
    {
        $html = '<ol>';
        foreach ($data as $value) {
            $html .= '<li>';
            $html .= $this->delegator->resolveAndParse($value, $fieldName);
            $html .= '</li>';
        }
        $html .= '</ol>';

        return $html;
    }
}
