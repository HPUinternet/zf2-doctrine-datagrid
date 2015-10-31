<?php
namespace Wms\Admin\DataGrid\Tests\Bootstrap\Application;

return array(
    'wms-datagrid' => array(
        'Application\Controller\Index' => array(
            'entityName' => 'Wms\Admin\DataGrid\Tests\Bootstrap\Application\Entity\Painting',
            'defaultColumns' => array('field3', 'artist.name', 'field2', 'name', 'field1', 'field4')
        ),
    ),
);