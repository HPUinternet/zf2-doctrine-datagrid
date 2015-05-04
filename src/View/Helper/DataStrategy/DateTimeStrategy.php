<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

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
        echo sprintf('<time datetime="%s">%s</time>',
            $data->format('Y-m-d'),
            $this->view->dateFormat(
                $data,
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::NONE,
                $this->view->formLabel()->getTranslator()->getLocale()
            )
        );
    }
}