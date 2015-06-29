<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * Resolve the datagrid configuration by looking up the configuration
     * inside a third party module
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* Resolving options based on the route will be implemented later
        $config = $serviceLocator->get('Config');
        $route = $serviceLocator->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
        $module = explode('/', $route)[1];
        return new ModuleOptions(isset($config['wms-category'][$module]) ? $config['wms-category'][$module] : array());
        */


        $dummyOptions = array(
            'entityName' => 'Wms\Admin\MediaManager\Entity\MediaItem',
            'defaultColumns' => array(
                'id', 'title', 'caption', 'originalFile.mimetype', 'originalFile.size', 'thumbnailFile.imagepath'
            ),
            'filters' => array(
                'Wms\Admin\User\Filter\PersonalEntitiesFilter' => 'Wms\Admin\User\Filter\PersonalEntitiesFilterParams'
            ),
            'renders' => array(
                'thumbnailFile.imagepath' => 'Wms\Admin\MediaManager\View\Helper\DataStrategy\ImageStrategy'
            ),
            'optionRoutes' => array(
                'edit' => 'zfcadmin/mediamanager/mediaitemaction',
                'delete' => 'zfcadmin/mediamanager/mediaitemaction',
            )
        );
//        $dummyOptions = array(
//            'entityName' => 'Wms\Admin\Shop\Entity\Product',
//            'defaultColumns' => array(
//                'id',
//                'productCode',
//                'name',
//                'startdate',
//                'enddate',
//                'price',
//                'stock'
//            ),
//            'searchFilters' => array(
//                'Wms\Admin\Shop\SearchFilter\VisibleProductSearchFilter'
//            ),
//            'optionRoutes' => array(
//                'edit' => 'zfcadmin/shop/contentaction',
//                'delete' => 'zfcadmin/shop/contentaction',
//            )
//        );
//
//        $dummyOptions = array(
//            'entityName' => 'Wms\Admin\User\Entity\User',
//            'defaultColumns' => array(
//                'username',
//            ),
//            'prohibitedColumns' => array(
//                'password', 'creator_id.password', 'last_modifier_id.password'
//            ),
//            'filters' => array(
//                'Wms\Admin\User\Filter\OnlyOwnEntitiesFilter' => array(
//                    'parameter' => 'value',
//                )
//            )
////        );
        return new ModuleOptions($dummyOptions);
    }
}
