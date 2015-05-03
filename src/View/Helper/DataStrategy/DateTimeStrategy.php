<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use Wms\Admin\DataGrid\View\Helper\DataStrategy\DataStrategyInterface;
use IntlDateFormatter;
use Zend\View\Renderer\RendererInterface as View;

class DateTimeStrategy implements DataStrategyInterface {


    /**
     * @var View;
     */
    protected $view = null;

    public function __construct(View $view) {
        $this->view = $view;
    }

    public function parse($data)
    {
        echo $this->view->dateFormat(
            $data,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            $this->view->formLabel()->getTranslator()->getLocale()
        );
    }
}