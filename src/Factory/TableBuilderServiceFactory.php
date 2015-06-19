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
            $serviceLocator->get('Wms\Admin\DataGrid\Options\ModuleOptions'),
            $serviceLocator->get('Wms\Admin\DataGrid\Service\QueryBuilderService'),
            $serviceLocator->get('Wms\Admin\DataGrid\Service\SearchFilterHelper')
        );
    }
}
