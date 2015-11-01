<?php namespace Wms\Admin\DataGrid\Tests\Controller\Plugin;

use Wms\Admin\DataGrid\Controller\Plugin\DataGridPlugin;
use Wms\Admin\DataGrid\Factory\DataGridControllerPluginFactory;
use Wms\Admin\DataGrid\Tests\AbstractTestCase;
use Wms\Admin\DataGrid\Tests\Bootstrap\Application\Controller\IndexController;

class ControllerPluginTest extends AbstractTestCase
{
    /**
     * The controller plugin should be known in the application config as a Controller plugin
     */
    public function testIsKnownInApplicationConfig()
    {
        $config = $this->getConfig();
        $this->assertArrayHasKey('controller_plugins', $config);

        $config = $config['controller_plugins'];
        $this->assertArrayHasKey('factories', $config);

        $config = $config['factories'];
        $this->assertArrayHasKey('DataGridPlugin', $config);

        $dataGridController = $config['DataGridPlugin'];
        $this->assertEquals(DataGridControllerPluginFactory::class, $dataGridController);
    }


    public function testUsesTablebuilderInterfaceToFetchData()
    {
        /** @var IndexController $controller */
        $controller = $this->getServiceManger()->get('ControllerManager')->get('Application\Controller\Index');
        $plugin = $controller->getPluginManager()->get('DataGridPLugin');


        /** @var DataGridPlugin $plugin */
        $this->assertInstanceOf('Wms\Admin\DataGrid\Service\TableBuilderInterface', $plugin->getTableBuilderService());

        $mock = $this->getTableBuilderMock();
        $mock->expects($this->once())->method('getTable');
        $plugin->setTableBuilderService($mock);

        $plugin->getTable();
    }

    protected function getTableBuilderMock()
    {
        $mock = $this->getMockBuilder('Wms\Admin\DataGrid\Service\TableBuilderService')
            ->setMethods(['getTable'])
            ->disableOriginalConstructor()
            ->getMock();
        return $mock;
    }
}
