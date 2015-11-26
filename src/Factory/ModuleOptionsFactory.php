<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * @var string
     */
    public static $configurationKey = 'wms-datagrid';

    /**
     * Resolve the DataGrid configuration by looking up the configuration
     * inside a third party module
     *
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     * @throws \Exception
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $moduleOptions = $this->getControllerConfig(
            $serviceLocator->get('Application')->getMvcEvent(),
            $serviceLocator->get('Config')
        );

        if (!$moduleOptions) {
            throw new \Exception('Could not find a valid DataGrid configuration for your current controller');
        }

        if (!isset($moduleOptions['entityName']) || !isset($moduleOptions['defaultColumns'])) {
            throw new \Exception('Missing the bare minimum entityName and defaultColumns in your configuration');
        }

        $moduleOptionsClass = $this->getModuleOptionsClass($serviceLocator);
        return new $moduleOptionsClass($moduleOptions);
    }

    /**
     * Get configuration based on your controller Name
     *
     * @param MvcEvent $mvcEvent
     * @param $config
     * @return mixed|bool
     */
    protected function getControllerConfig(MvcEvent $mvcEvent, $config)
    {
        $controllerName = $mvcEvent->getRouteMatch()->getParam('controller');
        $actionName = $mvcEvent->getRouteMatch()->getParam('action');

        if (!isset($config[self::$configurationKey]) || !isset($config[self::$configurationKey][$controllerName])) {
            return false;
        }

        $controllerConfiguration = $config[self::$configurationKey][$controllerName];
        if (isset($controllerConfiguration['entityName'])) {
            return $controllerConfiguration;
        }

        if (isset($controllerConfiguration[$actionName]) && is_array($controllerConfiguration[$actionName])) {
            return $controllerConfiguration[$actionName];
        }

        return false;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     * @throws \Exception
     */
    protected function getModuleOptionsClass(ServiceLocatorInterface $serviceLocator)
    {
        $moduleOptionsClass = $serviceLocator->get('DataGrid_ModuleOptionsClass');
        $reflector = new \ReflectionClass($moduleOptionsClass);
        if (!$reflector->isSubclassOf(ModuleOptions::class)) {
            throw new \Exception($moduleOptionsClass . ' must be an instance of ' . ModuleOptions::class);
        }
        return $moduleOptionsClass;
    }
}
