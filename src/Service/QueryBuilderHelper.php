<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo as MetaData;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

class QueryBuilderHelper
{
    /**
     * @var Array
     */
    protected $availableTableColumns;

    /**
     * @var Array
     */
    protected $selectedTableColumns;

    /**
     * @var Array
     */
    protected $prohibitedColumns;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var Array
     */
    private $subQueries = array();

    /**
     * @var String
     */
    private $sourceEntityName;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityMetadataHelper
     */
    private $entityMetadataHelper;

    /**
     * Create a new instance of the QueryBuilder
     *
     * You can optionally retrieve the entityManager from the QueryFilterHelper to assure permanent filters are
     * configured in the query builder.
     *
     * @param $sourceEntityName
     * @param EntityManager $entityManager
     * @param EntityMetadataHelper $entityMetadataHelper
     */
    public function __construct(
        $sourceEntityName,
        EntityManager $entityManager,
        EntityMetadataHelper $entityMetadataHelper
    ) {
        $this->sourceEntityName = $sourceEntityName;
        $this->entityManager = $entityManager;

        $this->queryBuilder = $entityManager->getRepository($sourceEntityName)->createQueryBuilder(
            $this->getEntityShortName($sourceEntityName)
        );
        $this->entityMetadataHelper = $entityMetadataHelper;
    }

    /**
     * Builds the selectquery for the database, based on the available entity properties
     *
     * @param array $columns
     * @return $this
     * @throws \Exception
     */
    public function select(array $columns)
    {
        $this->selectedTableColumns = array();
        $this->queryBuilder->resetDQLPart('select');
        $this->queryBuilder->resetDQLPart('join');

        $entityMetaData = $this->entityMetadataHelper->getEntityMetadata($this->sourceEntityName);
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
            $entityShortName = $this->getEntityShortName($this->sourceEntityName);

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
                    throw new \Exception(sprintf(
                        'Can\'t create join query parameters for %s in Entity %s',
                        $columnMetadata['fieldName'], $entityShortName)
                    );
                }

                if (!array_key_exists($selectColumn, $joinedProperties)) {
                    $joinedEntityAlias =
                        $this->getEntityShortName($columnMetadata['targetEntity']). count($joinedProperties);
                    $this->queryBuilder->leftJoin(
                        $entityShortName . '.' . $selectColumn,
                        $joinedEntityAlias
                    );
                    $joinedProperties[$selectColumn] = $joinedEntityAlias;
                } else {
                    $joinedEntityAlias = $joinedProperties[$selectColumn];
                }

                $this->queryBuilder->addSelect(
                    $joinedEntityAlias . '.' . end($selectColumnParts) . ' AS ' . implode($selectColumnParts)
                );
                $this->addToSelectedTableColumns(implode($selectColumnParts));
                continue;
            }

            $this->queryBuilder->addSelect($entityShortName . '.' . $selectColumn . ' AS ' . $selectColumn);
            $this->addToSelectedTableColumns($selectColumn);
        }

        return $this;
    }

    /**
     * Set the page for pagination
     *
     * @param $pageNumber
     * @param $itemsPerPage
     * @return $this
     */
    public function limit($pageNumber, $itemsPerPage)
    {
        $offset = ($pageNumber <= 1) ? 0 : ($pageNumber - 1) * $itemsPerPage;
        $this->queryBuilder->setMaxResults($itemsPerPage);
        $this->queryBuilder->setFirstResult($offset);

        return $this;
    }

    /**
     * Fires the configured queries to the datbase and migrates the results back to one resultsset.
     *
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getResultSet()
    {
        $resultSet = array();

        // Retrieve data from the primary query and re-order the array keys so they can be accessed more easily
        $result = $this->queryBuilder->getQuery()->execute();
        $primaryKey = $this->entityMetadataHelper->getEntityMetadata($this->sourceEntityName)
            ->getSingleIdentifierFieldName();
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

    /**
     * Add a orderBy clause to the main query
     *
     * @param $column
     * @param $order
     * @return $this
     */
    public function orderBy($column, $order)
    {
        $column = str_replace(".", "", $column);
        $selects = $this->queryBuilder->getDQLPart('select');
        foreach ($selects as $select) {
            $selectSegments = explode(" ", $select);
            if ($selectSegments[count($selectSegments) - 1] == $column) {
                $this->queryBuilder->orderBy($selectSegments[0], $order);
            }
        }

        return $this;
    }

    /**
     * Resolve the maximum result count
     *
     * @return mixed
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getMaxResultCount()
    {
        // Play nice with the filters and create a separate query to get the result count
        $query = clone $this->queryBuilder;
        $entityMetaData = $this->entityMetadataHelper->getEntityMetadata($this->sourceEntityName);

        $query->resetDQLParts(array('select', 'join', 'orderBy'));
        $query->setFirstResult(0);
        $query->setMaxResults(null);
        $query->select(sprintf(
            'count(%s.%s)',
            $this->getEntityShortName($this->sourceEntityName),
            $entityMetaData->getSingleIdentifierFieldName()
        ));
        $count = $query->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * Use this method to internally clean up the queryBuilder between each complete call
     * @param $prohibitedColumns
     * @return $this
     * @throws \Exception
     */
    public function refreshColumns($prohibitedColumns)
    {
        $this->prohibitedColumns = $prohibitedColumns;
        $this->availableTableColumns =
        $this->entityMetadataHelper->resolveAvailableTableColumns($this->sourceEntityName, $this->prohibitedColumns);

        return $this;
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
        $sourceEntityName = $this->sourceEntityName;
        $sourceEntityMetadata = $this->entityMetadataHelper->getEntityMetadata($sourceEntityName);
        $associationMapping = $sourceEntityMetadata->getAssociationMapping($sourceFieldName);
        if (empty($associationMapping)) {
            throw new \Exception(
                sprintf("Could not determine the association for %s", $sourceFieldName)
            );
        }

        $query = $this->entityManager->createQueryBuilder();

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
            $identityColumn = $this->getEntityShortName($sourceEntityName)
                . '.' . $sourceEntityMetadata->getSingleIdentifierFieldName();
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

    /**
     * To keep track on the queried columns, this internal method is used to add them to a list
     *
     * @param $name
     * @param bool $parent
     */
    private function addToSelectedTableColumns($name, $parent = false)
    {
        $dummyValue = $name;
        if ($parent) {
            if (!isset($this->selectedTableColumns[$parent])) {
                $this->selectedTableColumns[$parent] = array();
            }
            $this->selectedTableColumns[$parent][$name] = $dummyValue;

            return;
        }
        $this->selectedTableColumns[$name] = $dummyValue;
    }

    /**
     * @return Array
     */
    public function getProhibitedColumns()
    {
        return $this->prohibitedColumns;
    }

    /**
     * @param Array $prohibitedColumns
     */
    public function setProhibitedColumns($prohibitedColumns)
    {
        $this->prohibitedColumns = $prohibitedColumns;
    }

    /**
     * @return Array
     */
    public function getSelectedTableColumns()
    {
        return $this->selectedTableColumns;
    }

    /**
     * @param Array $selectedTableColumns
     */
    public function setSelectedTableColumns($selectedTableColumns)
    {
        $this->selectedTableColumns = $selectedTableColumns;
    }

    /**
     * @return Array
     */
    public function getAvailableTableColumns()
    {
        return array_keys($this->availableTableColumns);
    }

    /**
     * @return Array
     */
    public function getTableColumnTypes()
    {
        return $this->availableTableColumns;
    }
}
