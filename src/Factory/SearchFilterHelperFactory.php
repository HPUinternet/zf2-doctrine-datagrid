<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\SearchFilter\SearchFilterInterface;
use Wms\Admin\DataGrid\Service\SearchFilterHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SearchFilterHelperFactory implements FactoryInterface
{
    /**
     * Create SearchFilterHelper
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SearchFilterHelper
     * @throws \Exception
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        // Directly invoke or instantiate the filter Class
        $filters = array();
        $configuredFilters = $serviceLocator->get('Wms\Admin\DataGrid\Options\ModuleOptions')->getSearchFilters();
        foreach ($configuredFilters as $filterClass) {
            if ($serviceLocator->has($filterClass)) {
                $filterClassInstance = $serviceLocator->get($filterClass);
            } else {
                $filterClassInstance = new $filterClass();
            }

            if ($filterClassInstance instanceof SearchFilterInterface == false) {
                throw new \Exception(sprintf('%s does not implement the SearchFilterInterface', $filterClass));
            }

            $filters[$filterClassInstance->getFilterName()] = $filterClassInstance;
        }

        return new SearchFilterHelper($filters);
    }
}
