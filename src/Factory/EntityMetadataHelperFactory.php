<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Service\EntityMetadataHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityMetadataHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EntityMetadataHelper($serviceLocator->get('Doctrine\ORM\EntityManager'));
    }
}
