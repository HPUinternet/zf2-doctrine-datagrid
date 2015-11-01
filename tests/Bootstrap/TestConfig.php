<?php
return array(
    'modules' => array(
        'DoctrineModule',
        'DoctrineORMModule',
        'Wms\Admin\DataGrid',
        'Wms\Admin\DataGrid\Tests\Bootstrap\Application',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__,
            './vendor',
        ),
    ),
);
