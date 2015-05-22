<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\Filter\FilterParameterProviderInterface;
use Wms\Admin\DataGrid\Service\QueryFilterHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class QueryFilterHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator) {

        // Resolve any dynamic filters, if any
        $filters = $serviceLocator->get('Wms\Admin\DataGrid\Options\ModuleOptions')->getFilters();
        foreach($filters as $filterName => $filterParameters) {
            if(is_array($filterParameters) || (is_int($filterName) && !empty($filterParameters))) {
                continue;
            }

            $providerClass = new $filterParameters($serviceLocator);
            if($providerClass instanceof FilterParameterProviderInterface == false) {
                continue;
            }

            $parameters = $providerClass->resolveParameters($serviceLocator);
            if(!is_array($parameters)) {
                throw new \Exception(sprintf('The class %s did not return an array of parameters', $filterParameters));
            }

            $filters[$filterName] = $parameters;
        }

        return new QueryFilterHelper(
            $serviceLocator->get('Doctrine\ORM\EntityManager'),
            $filters
        );
    }
}