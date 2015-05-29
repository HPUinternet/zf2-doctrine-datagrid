<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

class TextStrategy extends StringStrategy implements DataStrategyInterface, DataStrategyFilterInterface
{
    protected $maxLength = 64;
}
