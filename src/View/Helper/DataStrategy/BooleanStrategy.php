<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Select;
use Zend\View\Renderer\RendererInterface as View;

class BooleanStrategy implements DataStrategyInterface, DataStrategyFilterInterface
{

    /**
     * @var View;
     */
    protected $view = null;

    /**
     * Create a new instance of the booleanStrategy
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @return mixed
     */
    public function parse($data)
    {
        $cellValue = "no";
        if ($data == true) {
            $cellValue = "yes";
        }

        return $this->view->translate($cellValue);
    }

    /**
     * returns a input element for the inline filter
     *
     * @param $elementName
     * @return string|\Zend\Form\ElementInterface
     */
    public function showFilter($elementName)
    {
        $select = new Select($elementName);
        $select->setEmptyOption('');
        $select->setValueOptions(array(
            '0' => $this->view->translate('no'),
            '1' => $this->view->translate('yes')
        ));

        return $select;
    }
}
