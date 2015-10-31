<?php namespace Wms\Admin\DataGrid\Tests;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Tests\Bootstrap\Bootstrap;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{

    protected $application;

    /**
     * @var Array
     */
    protected $applicationConfig;

    /**
     * @var ServiceManager
     */
    protected $serviceManger;


    public function setUp()
    {
        Bootstrap::init();
        $this->applicationConfig = Bootstrap::getConfig();
        $this->serviceManger = Bootstrap::getServiceManager();
        $this->buildConfigMock();
        $this->replaceDbSettings();
    }

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return $this->getServiceManger()->get('application');
    }

    /**
     * @return Array
     */
    public function getConfig()
    {
        return $this->getServiceManger()->get('config');
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManger()
    {
        return $this->serviceManger;
    }

    protected function buildConfigMock()
    {
        $configArray = include __DIR__ . '/Application/config/datagrid.config.php';
        $config = new ModuleOptions($configArray['wms-datagrid']['Application\Controller\Index']);
        $configFactoryMock = $this->getMockBuilder('Wms\Admin\DataGrid\Factory\ModuleOptionsFactory')->getMock();
        $configFactoryMock->method('createService')->willReturn($config);


        $this->getServiceManger()->setAllowOverride(true);
        $this->getServiceManger()->setFactory('DataGrid_ModuleOptions', $configFactoryMock);
    }

    protected function replaceDbSettings()
    {
        $config = $this->getServiceManger()->get('config');
        $config = ArrayUtils::merge($config, include __DIR__ . '/DataSourceConfig.php');
        $this->getServiceManger()->setAllowOverride(true)->setService('config', $config);
    }
}