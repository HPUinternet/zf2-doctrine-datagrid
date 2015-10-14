<?php namespace Wms\Admin\DataGrid\Tests;

use Wms\Admin\DataGrid\Tests\Bootstrap\Bootstrap;
use Zend\ServiceManager\ServiceManager;

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
}