<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Service\QueryFilterHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class QueryFilterHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator) {
        return new QueryFilterHelper(
            $serviceLocator->get('Doctrine\ORM\EntityManager'),
            $serviceLocator->get('Wms\Admin\DataGrid\Options\ModuleOptions')->getFilters()
        );
    }
}