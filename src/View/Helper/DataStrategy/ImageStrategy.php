<?php namespace Wms\Admin\DataGrid\View\Helper\DataStrategy;

class ImageStrategy implements DataStrategyInterface
{
    /**
     * Parse the data to a html representation
     *
     * @param $data
     * @return mixed
     */
    public function parse($data)
    {
        echo '<img src="/' . $data . '" alt="' . $data . '" />';
    }
}
