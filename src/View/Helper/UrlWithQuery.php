<?php namespace Wms\Admin\DataGrid\View\Helper;

use Zend\Di\ServiceLocator;
use Zend\View\Helper\AbstractHelper;

class UrlWithQuery extends AbstractHelper
{
    /**
     * Execution of the view helper
     *
     * @return null|string
     */
    public function __invoke(array $queryOverrides)
    {
        $currentUrl = strtok($this->getView()->ServerUrl(true), '?');
        $queryParams = array();

        parse_str(parse_url($this->getView()->ServerUrl(true), PHP_URL_QUERY), $queryParams);
        foreach ($queryOverrides as $key => $value) {
            $queryParams[$key] = $value;
        }

        if(empty($queryParams)) {
            return $currentUrl;
        }

        $queryParams = http_build_query($queryParams);
        return $currentUrl .'?'. $queryParams;
    }
}