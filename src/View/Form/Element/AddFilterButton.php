<?php namespace Wms\Admin\DataGrid\View\Form\Element;

use Zend\Form\Element\Button as BaseButton;
use Zend\Form\View\Helper\FormLabel;

class AddFilterButton extends BaseButton {

    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->setLabel('+');
    }
}