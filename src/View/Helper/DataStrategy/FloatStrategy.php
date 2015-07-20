<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;

class FloatStrategy implements DataStrategyInterface
{

    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @return mixed
     */
    public function parse($data)
    {
        return $data;
    }
}
