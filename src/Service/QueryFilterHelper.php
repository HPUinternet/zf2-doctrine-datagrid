<?php namespace Wms\Admin\DataGrid\Service;

/**
 * http://www.bdfi.info/img_forum/laravel_administrator001.jpg
 * https://raw.githubusercontent.com/jordillonch/CrudGeneratorBundle/master/screenshot.png
 *
 * Class QueryFilterHelper
 * @package Wms\Admin\DataGrid\Service
 */

use Doctrine\ORM\EntityManager;

class QueryFilterHelper
{

    /**
     * @var Array
     */
    private $loadFilters;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     * @param array $filters
     */
    public function __construct(EntityManager $entityManager, $filters = array())
    {
        $this->entityManager = $entityManager;
        $this->loadFilters = $filters;
    }

    /**
     * Loads the filters using the loadFilters method and returns the filtered
     * doctrine EntityManger
     *
     * @return EntityManager
     */
    public function getFilteredEntityManager()
    {
        $this->loadFilters($this->loadFilters);
        return $this->entityManager;
    }

    /**
     * Let the filterhelper configure the doctrine filters and
     * pass the appropriate parameters.
     *
     * @param $filters
     */
    public function loadFilters($filters)
    {
        $config = $this->entityManager->getConfiguration();
        foreach ($filters as $filterNamespace => $properties) {
            if (!is_array($properties)) {
                $filterNamespace = $properties;
            }

            $filterAlias = $this->generateFilterName($filterNamespace);

            if (in_array($filterNamespace, $this->entityManager->getFilters()->getEnabledFilters())) {
                continue;
            }

            $config->addFilter($filterAlias, $filterNamespace);
            $filter = $this->entityManager->getFilters()->enable($filterAlias);

            if (is_array($properties)) {
                foreach ($properties as $key => $value) {
                    $filter->setParameter($key, $value);
                }
            }
        }
    }

    /**
     * Filters need a name, this is a simple way of generating a name
     * that makes more sense than "filter1" or "filterB"
     *
     * @param $filterNamespace
     * @return string
     */
    private function generateFilterName($filterNamespace)
    {
        $nameSpaceSegments = explode('\\', $filterNamespace);

        return strtoupper(end($nameSpaceSegments));
    }
}
