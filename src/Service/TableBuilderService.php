<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Model\TableModel as Table;
use Wms\Admin\DataGrid\Service\QueryBuilderService;

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

    /**
     * @var Array
     */
    protected $columns;
    /**
     * @var QueryBuilderService
     */
    private $queryBuilderService;

    public function __construct(ModuleOptions $moduleOptions, EntityManager $entityManager)
    {
        $this->setModuleOptions($moduleOptions);
        $this->setEntityManager($entityManager);
        $this->setQueryBuilderService(new QueryBuilderService(
            $this->getEntityManager(),
            $this->getModuleOptions()->getEntityName()
        ));
    }

    public function getTable()
    {
        $table = new Table();
        $this->setColumns($this->getEntityColumns());
        $table->setHeaderRow($this->getColumns());
        $table->setAndParseRows($this->getTableData());
        return $table;
    }

    public function getEntityColumns()
    {
        $entityClass = $this->getModuleOptions()->getEntityName();
        if (!$entityClass) {
            throw new \Exception("No Entity found for the dataGrid module");
        }

        $metaData = $this->getEntityManager()->getClassMetadata($entityClass);
        $coloumns = $this->parseMetaDataToFieldArray($metaData);
        return $this->parseColumnsForDisplay($coloumns);
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

    protected function parseColumnsForDisplay($originalColoumns) {
        $returnData = array();
        foreach($originalColoumns as $key => $columnData) {
            $returnData[$key] = array(
                'fieldName' => $columnData['fieldName'],
                'type' => $columnData['type']
            );
        }
        return $returnData;
    }

    public function getTableData()
    {
        $tableData = $this->getQueryBuilderService()->getResult();
        return $tableData;
    }


//        echo '<pre>';
//        var_dump($qb);
//        echo '</pre>';
//        die('asdfasdf');
//
//        $reflectionClass = new \ReflectionClass($entityClass);
//        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
//        $queryBuilder->select("*");
//        $queryBuilder->from($entityClass, $reflectionClass->getShortName());
//        $results = $queryBuilder->getQuery()->execute();
//
//        echo '<pre>';
//        print_r($results);
//        echo '</pre>';
//        die('asdfasdf');
//
//
//        // Create the paginator itself
//        $paginator = new Paginator(
//            new DoctrinePaginator(new ORMPaginator($query))
//        );
//
//        $paginator
//            ->setCurrentPageNumber(1)
//            ->setItemCountPerPage(5);
//    }

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

    /**
     * @return QueryBuilderService
     */
    public function getQueryBuilderService()
    {
        return $this->queryBuilderService;
    }

    /**
     * @param QueryBuilderService $queryBuilderService
     */
    public function setQueryBuilderService($queryBuilderService)
    {
        $this->queryBuilderService = $queryBuilderService;
    }

    /**
     * @return Array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }
}