<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Service\TableBuilderService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TableBuilderServiceFactory implements FactoryInterface
{
    /**
     * Create TableBuilderService
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return TableBuilderService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TableBuilderService(
            $serviceLocator->get('DataGrid_ModuleOptions'),
            $serviceLocator->get('DataGrid_QueryBuilderService'),
            $serviceLocator->get('DataGrid_SearchFilterHelper')
        );
    }
}
