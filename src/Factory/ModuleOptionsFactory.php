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
                'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.savepath'
            ),
            'joinableColumns' => array(
                'originalFile' => array('savepath', 'mimetype', 'isactive', 'size'),
                'thumbnailFile' => array('savepath')
            )
        );
        return new ModuleOptions($dummyOptions);
    }
}