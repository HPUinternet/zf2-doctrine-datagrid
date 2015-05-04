<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\View\Renderer\RendererInterface as View;

class BooleanStrategy implements DataStrategyInterface {

    /**
     * @var View;
     */
    protected $view = null;

    public function __construct(View $view) {
        $this->view = $view;
    }

    public function parse($data)
    {
        $cellValue = "no";
        if($data == true) {
            $cellValue = "yes";
        }
        echo $this->view->translate($cellValue);
    }
}