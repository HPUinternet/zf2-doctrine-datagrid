<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Wms\Admin\DataGrid\Tests\Bootstrap\Bootstrap;
use Zend\Stdlib\ArrayUtils;

Bootstrap::init();
$serviceManager = Bootstrap::getServiceManager();
$config = $serviceManager->get('config');
$config = ArrayUtils::merge($config, include __DIR__ . '/DataSourceConfig.php');
$serviceManager->setAllowOverride(true)->setService('config', $config);
$entityManager = $serviceManager->get('Doctrine\Orm\EntityManager');

return ConsoleRunner::createHelperSet($entityManager);
