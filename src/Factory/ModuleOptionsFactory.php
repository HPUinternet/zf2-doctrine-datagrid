<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModuleOptionsFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator) {
        /* Resolving options based on the route will be implemented later
        $config = $serviceLocator->get('Config');
        $route = $serviceLocator->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
        $module = explode('/', $route)[1];
        return new ModuleOptions(isset($config['wms-category'][$module]) ? $config['wms-category'][$module] : array()); */

//        $dummyOptions = array( 'entityName' => 'Wms\Admin\User\Entity\User');
        $dummyOptions = array(
            'entityName' => 'Wms\Admin\MediaManager\Entity\File',
            'defaultColumns' => array(
                'name', 'extension', 'size'
//                'name','extension', 'size', 'mimetype', 'isactive', 'savepath',
//                'imagepath', 'keywords', 'mediaItem', 'fileBlocks'
            )
        );
        return new ModuleOptions($dummyOptions);
    }
}