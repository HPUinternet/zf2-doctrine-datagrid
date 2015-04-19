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


        $dummyOptions = array(
            'entityName' => 'Wms\Admin\MediaManager\Entity\MediaItem',
            'defaultColumns' => array(
                'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.imagepath'
            ),
        );
//        $dummyOptions = array(
//            'entityName' => 'Wms\Admin\Shop\Entity\Product',
//            'defaultColumns' => array(
//                'id', 'productCode', 'name'
//            ),
//        );

//        $dummyOptions = array(
//            'entityName' => 'Wms\Admin\User\Entity\User',
//            'defaultColumns' => array(
//                'id',
//            ),
//            'prohibitedColumns' => array(
//                'password'
//            )
//        );
        return new ModuleOptions($dummyOptions);
    }
}