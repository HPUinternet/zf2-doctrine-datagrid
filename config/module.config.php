<?php
namespace Wms\Admin\DataGrid;

return array(
    'service_manager' => array(
        'invokables' => array(
            'DataGrid_ModuleOptionsClass' => 'Wms\Admin\DataGrid\Options\ModuleOptions'
        ),
        'factories' => array(
            'DataGrid_QueryFilterHelper' => 'Wms\Admin\DataGrid\Factory\QueryFilterHelperFactory',
            'DataGrid_SearchFilterHelper' => 'Wms\Admin\DataGrid\Factory\SearchFilterHelperFactory',
            'DataGrid_QueryBuilderService' => 'Wms\Admin\DataGrid\Factory\QueryBuilderServiceFactory',
            'DataGrid_EntityMetadataHelper' => 'Wms\Admin\DataGrid\Factory\EntityMetadataHelperFactory',
            'DataGrid_TableBuilderService' => 'Wms\Admin\DataGrid\Factory\TableBuilderServiceFactory',
            'DataGrid_ModuleOptions' => 'Wms\Admin\DataGrid\Factory\ModuleOptionsFactory',
        ),
    ),
    'controller_plugins' => array(
        'factories' => array(
            'DataGridPlugin' => 'Wms\Admin\DataGrid\Factory\DataGridControllerPluginFactory',
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'controller_map' => array(
            __NAMESPACE__ => '/',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'DataGridTable' => 'Wms\Admin\DataGrid\View\Helper\DataGrid\Table',
            'UrlWithQuery' => 'Wms\Admin\DataGrid\View\Helper\UrlWithQuery',
            'DataGridForm' => 'Wms\Admin\DataGrid\View\Form\Form',
            'DataGridFormRow' => 'Wms\Admin\DataGrid\View\Form\FormRow',
            'DataGridFormCollection' => 'Wms\Admin\DataGrid\View\Form\FormCollection',
            'DataGridNestedFormCollection' => 'Wms\Admin\DataGrid\View\Form\NestedFormCollection',
        ),
    ),
);
