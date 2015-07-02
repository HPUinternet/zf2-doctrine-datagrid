<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Controller\Plugin\DataGridPlugin;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DataGridControllerPluginFactory implements FactoryInterface
{
    /**
     * Create a DataGridPlugin for your Controller
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return DataGridPlugin
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sl = $serviceLocator->getServiceLocator();
        $plugin = new DataGridPlugin();
        $plugin->setTableBuilderService($sl->get('Wms\Admin\DataGrid\Service\TableBuilderService'));

        return $plugin;
    }
}
