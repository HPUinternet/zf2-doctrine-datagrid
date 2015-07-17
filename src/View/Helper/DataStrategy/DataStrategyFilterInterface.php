<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

interface DataStrategyFilterInterface
{
    /**
     * returns a input element for the inline filter
     *
     * @param $elementName
     * @return \Zend\Form\ElementInterface
     */
    public function showFilter($elementName);
}
