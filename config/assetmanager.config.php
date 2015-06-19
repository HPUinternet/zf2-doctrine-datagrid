<?php
return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'collections' => array(
                'js/Admin/DataGrid/Dist/dataGrid.js' => array(
                    '/js/Admin/DataGrid/datagrid.js',
                ),
                'css/Admin/DataGrid/Dist/dataGrid.css' => array(
                    '/css/Admin/DataGrid/style.css',
                ),
//                'js/Admin/DataGrid/Dist/vendorLibraries.js' => array(
//                    '/vendor/datatables/jquery.dataTables.min.js',
//                ),
//                'css/Admin/DataGrid/Dist/vendorStyles.css' => array(
//                    '/vendor/datatables/jquery.dataTables.min.css',
//                ),
            ),
            'paths' => array(
                'Admin/DataGrid/' => __DIR__ . '/../public',
            ),
        ),
    ),
);
