<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Controller\DataGridController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DataGridControllerFactory implements FactoryInterface
{
    /**
     * Create DataGridController
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return DataGridController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sl = $serviceLocator->getServiceLocator();
        return new DataGridController($sl->get('Wms\Admin\DataGrid\Service\TableBuilderService'));
    }
}
