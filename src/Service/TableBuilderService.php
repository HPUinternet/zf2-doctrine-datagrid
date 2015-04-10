<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Model\TableModel as Table;

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
     * @var Array;
     */
    protected $visibleColumns;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(ModuleOptions $moduleOptions, EntityManager $entityManager)
    {
        $this->setModuleOptions($moduleOptions);
        $this->setEntityManager($entityManager);
        $this->setVisibleColumns($this->getModuleOptions()->getDefaultColumns());
        $this->setQueryBuilder($entityManager->getRepository($moduleOptions->getEntityName())->createQueryBuilder($moduleOptions->getEntityShortName()));
    }

    public function getTable()
    {
        $table = new Table();
        $this->setColumns($this->getEntityColumns());
        $table->setHeaderRow($this->parseColumnsForDisplay($this->getColumns()));
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

    protected function parseColumnsForDisplay($originalColoumns)
    {
        $returnData = array();
        foreach ($originalColoumns as $key => $columnData) {
            $returnData[$key] = array(
                'fieldName' => $columnData['fieldName'],
                'type' => $columnData['type']
            );
        }
        return $returnData;
    }

    public function getTableData()
    {
        $this->selectColumns($this->getModuleOptions()->getDefaultColumns());
        $tableData = $this->getQueryBuilder()->getQuery()->execute();
        return $tableData;
    }

    public function selectColumns(array $columns)
    {
        $this->getQueryBuilder()->resetDQLPart(array('select', 'join'));
        foreach ($columns as $selectColumn) {
            $joinedEntities = array();
            if (!array_key_exists($selectColumn, $this->getColumns())) {
                continue;
            }

            $entityShortName = $this->getEntityShortName($this->moduleOptions->getEntityName());

            // Make sure associations are joined by looking at the targetEntity and sourceToTargetKeyColumns fields
            if ($selectColumn['association'] === 'association') {
                if (!isset($selectColumn['association']['targetEntity']) || empty($selectColumn['association']['targetEntity'])) {
                    throw new \Exception(sprintf('Can\'t create join query parameters for %s in Entity %s', $selectColumn, $entityShortName));
                }

                $joinedEntityShortName = $this->getEntityShortName($selectColumn['association']['targetEntity']);
                if(!in_array($selectColumn['association']['targetEntity'], $joinedEntities)) {
                    foreach ($selectColumn as $entityColumn => $joinedColumn) {
                        $this->getQueryBuilder()->leftJoin(
                            $selectColumn['association']['targetEntity'],
                            $joinedEntityShortName,
                            Expr\Join::WITH,
                            sprintf("%s = %s", $entityShortName . '.' . $entityColumn, $joinedEntityShortName . '.' . $joinedColumn)
                        );
                    }
                }
                $entityShortName = $joinedEntityShortName;
            }
            $this->getQueryBuilder()->addSelect($entityShortName . '.' . $selectColumn);
        }
    }

    public function setPage($pageNumber, $itemsPerPage = 30)
    {
        $offset = ($pageNumber == 0) ? 0 : ($pageNumber - 1) * $itemsPerPage;
        $this->getQueryBuilder()->setMaxResults($itemsPerPage);
        $this->getQueryBuilder()->setFirstResult($offset);
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
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
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

    /**
     * @return Array
     */
    public function getVisibleColumns()
    {
        return $this->visibleColumns;
    }

    /**
     * @param Array $visibleColumns
     */
    public function setVisibleColumns($visibleColumns)
    {
        $this->visibleColumns = $visibleColumns;
    }

    /**
     * @return string
     */
    public function getEntityShortName($entityName)
    {
        $nameSpaceSegments = explode('\\', $entityName);
        return strtolower(end($nameSpaceSegments));
    }
}