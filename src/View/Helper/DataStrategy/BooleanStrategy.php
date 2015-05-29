<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Zend\View\Renderer\RendererInterface as View;

class BooleanStrategy implements DataStrategyInterface
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
        echo $this->view->translate($cellValue);
    }
}
