<?php namespace Wms\Admin\DataGrid\Service;

/**
 * http://www.bdfi.info/img_forum/laravel_administrator001.jpg
 * https://raw.githubusercontent.com/jordillonch/CrudGeneratorBundle/master/screenshot.png
 *
 * Class QueryFilterHelper
 * @package Wms\Admin\DataGrid\Service
 */

use Doctrine\ORM\EntityManager;
use Zend\EventManager\EventManagerInterface;

class QueryFilterHelper {

    /**
     * @var Array
     */
    private $loadFilters;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager, $filters = array()) {
        $this->entityManager = $entityManager;
        $this->loadFilters = $filters;
    }

    public function getFilteredEntityManager() {
        $this->loadFilters($this->loadFilters);
        return $this->entityManager;
    }

    public function loadFilters($filters) {
        $config = $this->entityManager->getConfiguration();
        foreach($filters as $filterNamespace => $properties) {
            if(!is_array($properties)) {
                $filterNamespace = $properties;
            }

            $filterAlias = $this->generateFilterName($filterNamespace);

            if(in_array($filterNamespace, $this->entityManager->getFilters()->getEnabledFilters())) {
                continue;
            }

            $config->addFilter($filterAlias, $filterNamespace);
            $filter = $this->entityManager->getFilters()->enable($filterAlias);

            if(is_array($properties)) {
                foreach($properties as $key => $value) {
                    $filter->setParameter($key, $value);
                }
            }
        }
    }

    private function generateFilterName($filterNamespace) {
        $nameSpaceSegments = explode('\\', $filterNamespace);

        return strtoupper(end($nameSpaceSegments));
    }

}