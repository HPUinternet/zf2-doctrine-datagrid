<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Service\QueryBuilderHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class QueryBuilderHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator) {
        return new QueryBuilderHelper(
            $serviceLocator->get('Wms\Admin\DataGrid\Options\ModuleOptions')->getEntityName(),
            $serviceLocator->get('Doctrine\ORM\EntityManager'),
            $serviceLocator->get('Wms\Admin\DataGrid\Service\EntityMetadataHelper')
        );
    }
}