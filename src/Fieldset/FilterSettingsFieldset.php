<?php namespace Wms\Admin\DataGrid\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class FilterSettingsFieldset extends Fieldset
{
    public function __construct()
    {
        parent::__construct('Filters');
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'example_filter',
            'options' => array(
                'label' => 'A Filter',
            )
        ));
    }
}