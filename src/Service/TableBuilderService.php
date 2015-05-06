<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo as MetaData;
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
     * @var EntityMetadataHelper
     */
    private $entityMetadataHelper;

    /**
     * @var Array
     */
    protected $availableTableColumns;

    /**
     * @var Array
     */
    private $selectedTableColumns;

    /**
     * @var int
     */
    protected $pageNumber;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var Array
     */
    private $subQueries;

    // --------------------------------------------------------------------
    //                         SERVICE INTERACTIONS
    // --------------------------------------------------------------------

    /**
     * @param ModuleOptions $moduleOptions
     * @param EntityManager $entityManager
     * @return TableBuilderService
     */
    public function __construct(ModuleOptions $moduleOptions, EntityManager $entityManager)
    {
        $this->setModuleOptions($moduleOptions);
        $this->setEntityManager($entityManager);
        $this->setQueryBuilder($entityManager->getRepository($moduleOptions->getEntityName())->createQueryBuilder($this->getEntityShortName($moduleOptions->getEntityName())));

        $this->entityMetadataHelper = new EntityMetadataHelper($entityManager);

        // Make sure data retrieval is default when not configured
        $this->setAvailableTableColumns($this->resolveAvailableTableColumns());
        $this->selectColumns($this->getModuleOptions()->getDefaultColumns())->setPage(1);
        $this->subQueries = array();
    }

    /**
     * Builds the selectquery for the database, based on the available entity properties
     *
     * @param array $columns
     * @return $this
     * @throws \Exception
     */
    public function selectColumns(array $columns)
    {
        $this->getQueryBuilder()->resetDQLPart('select');
        $this->getQueryBuilder()->resetDQLPart('join');

        $entityMetaData = $this->entityMetadataHelper->getMetaData($this->getModuleOptions()->getEntityName());
        if (!in_array($entityMetaData->getSingleIdentifierFieldName(), $columns)) {
            $columns[] = $entityMetaData->getSingleIdentifierFieldName();
        }

        $joinedProperties = array();
        $entityMetaData = $this->entityMetadataHelper->parseMetaDataToFieldArray($entityMetaData);

        foreach ($columns as $selectColumn) {
            if (!in_array($selectColumn, $this->getAvailableTableColumns())) {
                continue;
            }

            $selectColumnParts = explode(".", $selectColumn);
            $selectColumn = reset($selectColumnParts);
            $columnMetadata = $entityMetaData[$selectColumn];
            $entityShortName = $this->getEntityShortName($this->getModuleOptions()->getEntityName());

            if ($columnMetadata['type'] === 'association') {
                /**
                 * Only owning One-to-One associations can be handled inline. others, like One-To-Many and Many-To-Many
                 * should result in a different query since querying them will result in multiple duplicate rows
                 * in the database result set.
                 */
                if (!in_array($columnMetadata['associationType'], array(MetaData::ONE_TO_ONE, MetaData::MANY_TO_ONE))) {
                    $this->selectInSubQuery($selectColumn, $columnMetadata['targetEntity'], end($selectColumnParts));
                    continue;
                }

                if (!isset($columnMetadata['joinColumns']) || empty($columnMetadata['joinColumns'])) {
                    throw new \Exception(sprintf('Can\'t create join query parameters for %s in Entity %s',
                        $columnMetadata['fieldName'], $entityShortName));
                }

                if (!array_key_exists($selectColumn, $joinedProperties)) {
                    $joinedEntityAlias = $this->getEntityShortName($columnMetadata['targetEntity']) . count($joinedProperties);
                    $this->getQueryBuilder()->leftJoin(
                        $entityShortName . '.' . $selectColumn,
                        $joinedEntityAlias
                    );
                    $joinedProperties[$selectColumn] = $joinedEntityAlias;
                } else {
                    $joinedEntityAlias = $joinedProperties[$selectColumn];
                }

                $this->getQueryBuilder()->addSelect(
                    $joinedEntityAlias . '.' . end($selectColumnParts) . ' AS ' . implode($selectColumnParts)
                );
                $this->addToSelectedTableColumns(implode($selectColumnParts));
                continue;
            }

            $this->getQueryBuilder()->addSelect($entityShortName . '.' . $selectColumn . ' AS ' . $selectColumn);
            $this->addToSelectedTableColumns($selectColumn);
        }

        return $this;
    }

    /**
     * Set the page for pagination
     *
     * @param $pageNumber
     * @return $this
     */
    public function setPage($pageNumber)
    {
        $this->pageNumber = $pageNumber;
        $itemsPerPage = $this->getModuleOptions()->getItemsPerPage();
        $offset = ($pageNumber <= 1) ? 0 : ($pageNumber - 1) * $itemsPerPage;
        $this->getQueryBuilder()->setMaxResults($itemsPerPage);
        $this->getQueryBuilder()->setFirstResult($offset);

        return $this;
    }

    /**
     * Fires the configured queries to the datbase and migrates the results back to one resultsset.
     *
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getTableData()
    {
        $resultSet = array();

        // Retrieve data from the primary query and re-order the array keys so they can be accessed more easily
        $result = $this->getQueryBuilder()->getQuery()->execute();
        $entityName = $this->getModuleOptions()->getEntityName();
        $primaryKey = $this->entityMetadataHelper->getMetaData($entityName)->getSingleIdentifierFieldName();
        foreach ($result as $key => $data) {
            $resultSet[$data[$primaryKey]] = $data;
        }

        foreach ($this->subQueries as $fieldName => $queryBuilder) {
            $queryBuilder->setParameter('resultIds', array_column($resultSet, $primaryKey));
            $results = $queryBuilder->getQuery()->execute();
            foreach ($results as $result) {
                $resultSetKey = $result['association'];
                if (!array_key_exists($fieldName, $resultSet[$resultSetKey])) {
                    $resultSet[$resultSetKey][$fieldName] = array();
                }
                unset($result['association']);
                $resultSet[$resultSetKey][$fieldName][] = $result;
            }
        }

        return $resultSet;
    }

    public function getMaxResultCount()
    {
        // Play nice with the filters and create a separate query to get the result count
        $query = clone $this->getQueryBuilder();
        $entityName = $this->getModuleOptions()->getEntityName();
        $entityMetaData = $this->entityMetadataHelper->getMetaData($entityName);

        $query->resetDQLParts(array('select', 'join'));
        $query->setFirstResult(0);
        $query->setMaxResults(null);
        $query->select(sprintf(
            'count(%s.%s)',
            $this->getEntityShortName($entityName),
            $entityMetaData->getSingleIdentifierFieldName()
        ));
        $count = $query->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * Retrieve an new TableModel
     * based on your data configuration in the object
     * @return Table
     */
    public function getTable()
    {
        $table = new Table();
        $table->setAvailableHeaders($this->getAvailableTableColumns());
        $table->setUsedHeaders($table->calculateTableHeader($this->selectedTableColumns));
        $table->setAndParseRows($this->getTableData());
        $table->setPageNumber($this->pageNumber);
        $table->setMaxPageNumber($this->calculateMaxPages());

        return $table;
    }

    // --------------------------------------------------------------------
    //                          GETTERS & SETTERS
    // --------------------------------------------------------------------
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
     * @return string
     */
    public function getEntityShortName($entityName)
    {
        $nameSpaceSegments = explode('\\', $entityName);

        return strtoupper(end($nameSpaceSegments));
    }

    /**
     * @return Array
     */
    public function getAvailableTableColumns()
    {
        return $this->availableTableColumns;
    }

    /**
     * @param Array $availableTableColumns
     */
    public function setAvailableTableColumns($availableTableColumns)
    {
        $this->availableTableColumns = $availableTableColumns;
    }

    // --------------------------------------------------------------------
    //                          INTERNAL LOGIC
    // --------------------------------------------------------------------

    /**
     * Resolve the available columns based on the configured entity.
     * Will also resolve the available columns for the associated properties
     *
     * @return array
     * @throws \Exception
     */
    protected function resolveAvailableTableColumns()
    {
        $entityProperties = $this->entityMetadataHelper->parseMetaDataToFieldArray(
            $this->entityMetadataHelper->getMetaData($this->getModuleOptions()->getEntityName())
        );

        $returnData = array();
        $prohibitedColumns = $this->getModuleOptions()->getProhibitedColumns();

        foreach ($entityProperties as $property) {
            if (in_array($property['fieldName'], $prohibitedColumns)) {
                continue;
            }

            if ($property['type'] != "association") {
                $returnData[] = $property['fieldName'];
                continue;
            }

            if (!isset($property['targetEntity'])) {
                throw new \Exception(
                    sprintf('%s is configured as a association, but no target Entity found', $property['fieldName'])
                );
            }

            $targetEntity = $property['targetEntity'];
            $targetEntityProperties = $this->entityMetadataHelper->parseMetaDataToFieldArray(
                $this->entityMetadataHelper->getMetaData($targetEntity)
            );

            foreach ($targetEntityProperties as $targetEntityProperty) {
                if ($targetEntityProperty['type'] !== "association" && !array_search($targetEntityProperty,
                        $prohibitedColumns)
                ) {
                    $returnData[] = $property['fieldName'] . '.' . $targetEntityProperty['fieldName'];
                }
            }
        }

        return $returnData;
    }

    /**
     * Whenever we need to retrieve additional data (like in a one-to-may or many-to-many) we need to work
     * aside from the main query. This method lets you select anything form another entity that has associations
     * with the configured entity of the main query.
     *
     * @param $sourceFieldName
     * @param $targetEntityName
     * @param $targetFieldName
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    protected function selectInSubQuery($sourceFieldName, $targetEntityName, $targetFieldName)
    {
        if (!isset($this->subQueries[$sourceFieldName])) {
            $this->subQueries[$sourceFieldName] = $this->createSubQuery($sourceFieldName, $targetEntityName);
        }

        $query = $this->subQueries[$sourceFieldName];
        $query->addSelect(sprintf("%s AS %s",
            $this->getEntityShortName($targetEntityName) . '.' . $targetFieldName,
            $sourceFieldName . $targetFieldName
        ));
        $this->addToSelectedTableColumns($sourceFieldName . $targetFieldName, $sourceFieldName);
    }

    /**
     * Since the configured association (one-to-many vs many-to-many) has a lot of
     * influence on how the query will be build, its vital we identify the used
     * association type first. The createSubQuery method will handle this delicately and return
     * an usable queryobject for you to add your select statement in.
     *
     * @param $sourceFieldName
     * @param $targetEntityName
     * @return QueryBuilder
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    protected function createSubQuery($sourceFieldName, $targetEntityName)
    {
        // Get additional information about the association
        $sourceEntityName = $this->getModuleOptions()->getEntityName();
        $sourceEntityMetadata = $this->entityMetadataHelper->getMetaData($sourceEntityName);
        $associationMapping = $sourceEntityMetadata->getAssociationMapping($sourceFieldName);
        if (empty($associationMapping)) {
            throw new \Exception(
                sprintf("Could not determine the association for %s", $sourceFieldName)
            );
        }

        $query = $this->getEntityManager()->createQueryBuilder();

        $associationType = $associationMapping['type'];

        // One to Many associations should always start in the external entity, they contain data about the join
        if ($associationType === MetaData::ONE_TO_MANY) {
            $mappedColumn = $this->getEntityShortName($targetEntityName) . '.' . $associationMapping['mappedBy'];
            $query->addSelect(sprintf("IDENTITY(%s) AS association", $mappedColumn));
            $query->from($targetEntityName, $this->getEntityShortName($targetEntityName));
            $query->where(sprintf('%s IN (:resultIds)', $mappedColumn));

            return $query;
        }

        // When dealing with many-to-many we can make the assumption that our SourceEntity knows what to bind
        if ($associationType === MetaData::MANY_TO_MANY) {
            // @todo: the code below will break if you have a multi column primary key
            $identityColumn = $this->getEntityShortName($sourceEntityName) . '.' . $sourceEntityMetadata->getSingleIdentifierFieldName();
            $query->addSelect(sprintf("%s AS association", $identityColumn));
            $query->from($sourceEntityName, $this->getEntityShortName($sourceEntityName));
            $query->innerJoin($this->getEntityShortName($sourceEntityName) . '.' . $sourceFieldName,
                $this->getEntityShortName($targetEntityName));
            $query->where(sprintf('%s IN (:resultIds)', $identityColumn));

            return $query;
        }


        throw new \Exception(
            sprintf("Unsupported association type: %s", $associationType)
        );
    }

    private function addToSelectedTableColumns($name, $parent = false) {
        $dummyValue = '';
        if($parent) {
            if(!isset($this->selectedTableColumns[$parent])) {
                $this->selectedTableColumns[$parent] = array();
            }
            $this->selectedTableColumns[$parent][$name] = $dummyValue;
            return;
        }
        $this->selectedTableColumns[$name] = $dummyValue;
    }

    protected function calculateMaxPages()
    {
        $maxResults = $this->getMaxResultCount();
        $itemsPerPage = $this->getModuleOptions()->getItemsPerPage();

        if ($maxResults <= $itemsPerPage) {
            return 1;
        }

        return ceil($maxResults / $itemsPerPage);

    }
}