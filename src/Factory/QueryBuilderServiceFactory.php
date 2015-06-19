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
            $serviceLocator->get('Wms\Admin\DataGrid\Options\ModuleOptions')->getEntityName(),
            $serviceLocator->get('Wms\Admin\DataGrid\Service\QueryFilterHelper')->getFilteredEntityManager(),
            $serviceLocator->get('Wms\Admin\DataGrid\Service\EntityMetadataHelper')
        );
    }
}
