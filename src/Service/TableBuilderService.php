<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Model\TableModel as Table;
use Zend\Paginator\Paginator;

class TableBuilderService
{

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(ModuleOptions $moduleOptions, EntityManager $entityManager)
    {
        $this->setModuleOptions($moduleOptions);
        $this->setEntityManager($entityManager);
    }

    public function getTable()
    {
        $table = new Table();
        $table->setHeaderRow($this->getTableHeaders());
        $table->setRows($this->getTableData());
    }

    public function getTableHeaders()
    {
        $entityClass = $this->getModuleOptions()->getEntityName();
        if (!$entityClass) {
            throw new \Exception("No Entity found for the dataGrid module");
        }

        $metaData = $this->getEntityManager()->getClassMetadata($entityClass);
        return $this->parseMetaDataToFieldArray($metaData);
    }

    protected function parseMetaDataToFieldArray(ClassMetadata $metaData)
    {
        $columns = array();
        foreach ($metaData->reflFields as $fieldName => $reflectionData) {
            if (array_key_exists($fieldName, $metaData->fieldMappings)) {
                $fieldData = $metaData->getFieldMapping($fieldName);
                $columns[$fieldName] = $fieldData;
            } elseif (array_key_exists($fieldName, $metaData->associationMappings)) {
                $fieldData = $metaData->getAssociationMapping($fieldName);
                $fieldData['type'] = 'association';
                $columns[$fieldName] = $fieldData;
            } else {
                throw new \Exception(sprintf('Can\'t map %s in the %s Entity', $fieldName, $metaData->name));
            }
        }

        return $columns;
    }

    public function getTableData()
    {
        $entityClass = $this->getModuleOptions()->getEntityName();
        if (!$entityClass) {
            throw new \Exception("No Entity found for the dataGrid module");
        }

        $repository = $this->getEntityManager()->getRepository($entityClass);
        $qb = $repository->createQueryBuilder('test');





        echo '<pre>';
        var_dump($qb);
        echo '</pre>';
        die('asdfasdf');

        $reflectionClass = new \ReflectionClass($entityClass);
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("*");
        $queryBuilder->from($entityClass, $reflectionClass->getShortName());
        $results = $queryBuilder->getQuery()->execute();

        echo '<pre>';
        print_r($results);
        echo '</pre>';
        die('asdfasdf');


        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($query))
        );

        $paginator
            ->setCurrentPageNumber(1)
            ->setItemCountPerPage(5);
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * @param ModuleOptions $moduleOptions
     */
    public function setModuleOptions($moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }
}