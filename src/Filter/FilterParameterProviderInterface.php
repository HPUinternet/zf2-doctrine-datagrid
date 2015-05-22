<?php namespace Wms\Admin\DataGrid\Filter;

use Zend\ServiceManager\ServiceLocatorInterface;
use Wms\Admin\DataGrid\Options\ModuleOptions;

interface FilterParameterProviderInterface {

    public function resolveParameters(ServiceLocatorInterface $serviceLocator);

}