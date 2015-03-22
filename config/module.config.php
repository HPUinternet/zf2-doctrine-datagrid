<?php
namespace Wms\Admin\DataGrid;
return array(
    'service_manager' => array(
        'factories' => array(
            'Wms\Admin\DataGrid\Service\TableBuilderService' => 'Wms\Admin\DataGrid\Factory\TableBuilderServiceFactory',
            'Wms\Admin\DataGrid\Options\ModuleOptions' => 'Wms\Admin\DataGrid\Factory\ModuleOptionsFactory',
        )
    ),
    'controllers' => array(
        'factories' => array(
            'Wms\Admin\DataGrid\Controller\DataGridController' => 'Wms\Admin\DataGrid\Factory\DataGridControllerFactory',
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'controller_map' => array(
            __NAMESPACE__ => '/',
        ),
    ),
    'router' => array(
        'routes' => array(
            'zfcadmin' => array(
                'child_routes' => array(
                    'layoutbuilder' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/datagrid',
                            'defaults' => array(
                                'controller'    => 'Wms\Admin\DataGrid\Controller\DataGridController',
                                'action' => 'index'
                            ),
                        ),
                    )
                )
            )
        )
    ),
);