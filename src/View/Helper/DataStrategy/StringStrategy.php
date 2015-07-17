<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;

class StringStrategy implements DataStrategyInterface, DataStrategyFilterInterface
{
    protected $maxLength = 128;

    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @return mixed
     */
    public function parse($data)
    {
        if (strlen($data) > $this->maxLength) {
            return sprintf('%s...', mb_substr($data, 0, $this->maxLength));
        }

        return $data;
    }

    /**
     * returns a input element for the inline filter
     *
     * @param $elementName
     * @return string|ElementInterface
     */
    public function showFilter($elementName)
    {
        return new Text($elementName);
    }
}
