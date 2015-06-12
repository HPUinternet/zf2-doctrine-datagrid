<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

use IntlDateFormatter;
use Zend\View\Renderer\RendererInterface as View;

class DatetimeStrategy implements DataStrategyInterface
{


    /**
     * @var View;
     */
    protected $view = null;

    /**
     * Create a new instance of the DateTime Strategy
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
        echo sprintf(
            '<time datetime="%s">%s</time>',
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
