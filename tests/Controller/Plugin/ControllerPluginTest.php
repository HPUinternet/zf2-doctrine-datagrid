<?php namespace Wms\Admin\DataGrid\Tests\Controller\Plugin;

use Wms\Admin\DataGrid\Factory\DataGridControllerPluginFactory;
use Wms\Admin\DataGrid\Tests\AbstractTestCase;

class ControllerPluginTest extends AbstractTestCase
{
    /**
     * The controller plugin should be known in the application config as a Controller plugin
     */
    public function testIsKnownInApplicationConfig() {
        $config = $this->getConfig();
        $this->assertArrayHasKey('controller_plugins', $config);

        $config = $config['controller_plugins'];
        $this->assertArrayHasKey('factories', $config);

        $config = $config['factories'];
        $this->assertArrayHasKey('DataGridPlugin', $config);

        $dataGridController = $config['DataGridPlugin'];
        $this->assertEquals(DataGridControllerPluginFactory::class, $dataGridController);
    }
}