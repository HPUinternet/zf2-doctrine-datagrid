<?php namespace Wms\Admin\DataGrid\Factory;

use Wms\Admin\DataGrid\SearchFilter\NonFieldSearchFilterInterface;
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
        foreach ($configuredFilters as $fieldName => $filterClass) {
            if ($serviceLocator->has($filterClass)) {
                $filterClassInstance = $serviceLocator->get($filterClass);
            } else {
                $filterClassInstance = new $filterClass();
            }

            if ($filterClassInstance instanceof NonFieldSearchFilterInterface) {
                $filters[$filterClassInstance->getFilterName()] = $filterClassInstance;
                continue;
            }

            if ($filterClassInstance instanceof SearchFilterInterface) {
                $filters[$fieldName] = $filterClassInstance;
                continue;
            }

            throw new \Exception(sprintf('%s does not implement one of the searchFilter interfaces', $filterClass));
        }

        return new SearchFilterHelper($filters);
    }
}
