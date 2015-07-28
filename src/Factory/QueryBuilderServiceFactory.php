<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Service\QueryBuilderService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class QueryBuilderServiceFactory implements FactoryInterface
{
    /**
     * Create QueryBuilderService
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return QueryBuilderService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new QueryBuilderService(
            $serviceLocator->get('DataGrid_ModuleOptions')->getEntityName(),
            $serviceLocator->get('DataGrid_QueryFilterHelper')->getFilteredEntityManager(),
            $serviceLocator->get('DataGrid_EntityMetadataHelper')
        );
    }
}
