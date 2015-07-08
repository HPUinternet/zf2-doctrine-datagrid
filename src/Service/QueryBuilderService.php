<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo as MetaData;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

class QueryBuilderService
{
    protected $availableTableColumns = array();
    protected $selectedTableColumns = array();
    protected $additionalWhereColumns = array();
    protected $subQueries = array();
    protected $prioritizedSubQueries = array();
    private $iterator = 0;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

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
     * @return Array
     */
    public function getSelectedTableColumns()
    {
        $returnData = $this->selectedTableColumns;
        foreach ($this->additionalWhereColumns as $columnName) {
            if ($this->isSelectedField($columnName)) {
                $columnSegments = explode('.', $columnName);
                if (count($columnSegments) >= 2) {
                    unset($returnData[$columnSegments[0]][$columnSegments[1]]);
                    continue;
                }
                unset($returnData[$columnSegments[0]]);
            }
        }

        return $returnData;
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
     * Since some fields might be fetched using external queries this methods retrieves
     * the correct Doctrine QueryBuilder for your fieldName.
     *
     * @param $fieldName
     * @return bool|QueryBuilder
     */
    public function getQueryForField($fieldName)
    {
        if (!array_key_exists($fieldName, $this->availableTableColumns)) {
            return false;
        }

        $query = $this->queryBuilder;
        if ($this->needsSubQuery($fieldName)) {
            $fieldNameSegments = explode(".", $fieldName);
            $fieldName = reset($fieldNameSegments);
            $query = $this->getSubQuery($fieldName);
        }

        return $query;
    }

    /**
     * Wrapper around getSelectorForField that assures the field will be available for a where clause.
     * An essential tool when hooking into the QueryBuilderService, the only way this method will return false is when
     * you are trying to retrieve a field that is not available in the configured entity due to column restrictions
     * or your entity lacking it.
     *
     * @param $fieldName
     * @return bool|string
     * @throws \Exception
     */
    public function getColumnNameForField($fieldName)
    {
        $selector = $this->getSelectorForField($fieldName);
        if ($selector) {
            return $selector;
        }

        $this->addSingleSelect($fieldName);
        $this->additionalWhereColumns[] = $fieldName;

        return $this->getSelectorForField($fieldName);
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
        $this->reset();

        // Always assure the Identifier field is also in the select statement, this field is needed to join subqueries
        $entityMetaData = $this->entityMetadataHelper->getEntityMetadata($this->sourceEntityName);
        if (!in_array('id', $columns)) {
            $columns[] = $entityMetaData->getSingleIdentifierFieldName();
        }

        $joinedProperties = array();
        $fieldsMetaData = $this->entityMetadataHelper->parseMetaDataToFieldArray($entityMetaData);
        $entityShortName = $this->getEntityShortName($this->sourceEntityName);

        foreach ($columns as $fieldName) {
            // filter in availableTableColumns instead of $fieldMetaData due to the prohibitedColumns
            if (!array_key_exists($fieldName, $this->availableTableColumns)) {
                continue;
            }

            $fieldNameSegments = explode(".", $fieldName);
            $fullFieldName = $fieldName;
            $fieldName = reset($fieldNameSegments);
            $fieldMetaData = $fieldsMetaData[$fieldName];

            // Treat non association fields normally
            if ($fieldMetaData['type'] !== 'association') {
                $this->queryBuilder->addSelect($entityShortName . '.' . $fieldName . ' AS ' . $fieldName);
                $this->addToSelectedTableColumns($fieldName);
                continue;
            }

            // fields in a different query need different processing
            if ($this->needsSubQuery($fullFieldName)) {
                $this->selectInSubQuery($fieldName, $fieldMetaData['targetEntity'], end($fieldNameSegments));
                continue;
            }

            // Joining and selecting
            if (!isset($fieldMetaData['joinColumns']) || empty($fieldMetaData['joinColumns'])) {
                throw new \Exception(sprintf(
                    'Can\'t create join query parameters for %s in Entity %s',
                    $fieldMetaData['fieldName'],
                    $entityShortName
                ));
            }

            $joinAlias = array_key_exists($fieldName, $joinedProperties) ? $joinedProperties[$fieldName] : false;
            if (!$joinAlias) {
                $joinAlias = $this->getEntityShortName($fieldMetaData['targetEntity']) . count($joinedProperties);
                $this->queryBuilder->leftJoin($entityShortName . '.' . $fieldName, $joinAlias);
                $joinedProperties[$fieldName] = $joinAlias;
            }

            $fieldAlias = implode($fieldNameSegments);
            $this->queryBuilder->addSelect($joinAlias . '.' . end($fieldNameSegments) . ' AS ' . $fieldAlias);
            $this->addToSelectedTableColumns(implode('.', $fieldNameSegments));
        }

        return $this;
    }

    /**
     * Add a where clause to the query.
     * note that: when you are putting a where clause on a column that belongs to a sub query
     * the property prioritizedSubQueries will be the new container for that QueryBuilder instance
     * to ensure the data is filtered properly
     *
     * @see prioritizeSubQueries
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @param string $clause
     * @return bool|this
     * @throws \Exception
     */
    public function where($fieldName, $fieldValue, $clause = "LIKE")
    {
        if (!$this->isSelectedField($fieldName) && !$this->addSingleSelect($fieldName)) {
            return false;
        }

        $isSubQuery = $this->needsSubQuery($fieldName);
        $query = $this->getQueryForField($fieldName);
        if ($isSubQuery) {
            $fieldNameSegments = explode(".", $fieldName);
            if (!array_key_exists($fieldNameSegments[0], $this->prioritizedSubQueries)) {
                $query = $this->prioritizeSubQuery($fieldNameSegments[0]);
            }
        }

        $selector = $this->getSelectorForField($fieldName);
        if ($fieldValue == 'NULL' || $fieldValue == 'NOT NULL') {
            $query->andWhere($selector . ' ' . $clause . ' ' . $fieldValue);
        } else {
            $parameterName = 'value' . $this->iterator;
            $query->andWhere($selector . ' ' . $clause . ' :' . $parameterName);
            $query->setParameter($parameterName, $fieldValue);
            $this->iterator++;
        }

        return $this;
    }

    /**
     * Fire a raw where statement into the query
     *
     * @note please escape your rawStatement properly since this is a bit more vulnerable for injections
     * @param $fieldName
     * @param $rawStatement
     */
    public function whereRaw($fieldName, $rawStatement)
    {
        $query = $this->getQueryForField($fieldName);
        $query->andWhere($rawStatement);
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
        $subQueryResultSet = array();

        $sourceEntityMetaData = $this->entityMetadataHelper->getEntityMetadata($this->sourceEntityName);
        $primaryKey = $sourceEntityMetaData->getSingleIdentifierFieldName();

        // if we have any prioritized Sub Queries, the results will become a where clause for our main query
        if (!empty($this->prioritizedSubQueries)) {
            foreach ($this->prioritizedSubQueries as $fieldName => $queryBuilder) {
                $results = $queryBuilder->getQuery()->execute();
                $subQueryResultSet[$fieldName] = $results;
                $whereClause = array();
                foreach ($results as $result) {
                    $whereClause[] = $result['association'];
                }

                $primaryKeyField = $this->getEntityShortName($this->sourceEntityName) . '.' . $primaryKey;
                $this->queryBuilder->andWhere($primaryKeyField . ' IN (:' . $fieldName . $this->iterator . ')');
                $this->queryBuilder->setParameter($fieldName . $this->iterator, $whereClause);
                $this->iterator++;
            }
        }

        // Retrieve the primary results and re-order the array keys for easy insertion of subQuery data
        $result = $this->queryBuilder->getQuery()->execute();
        foreach ($result as $data) {
            $resultSet[$data[$primaryKey]] = $data;
        }

        // Retrieve data from the subQueries and merge them in the results
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

        // Merge the prioritized Sub Query results into the rest of the results
        foreach ($subQueryResultSet as $fieldName => $results) {
            foreach ($results as $result) {
                $resultSetKey = $result['association'];
                if (!isset($resultSet[$resultSetKey])) {
                    continue;
                }

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

        $query->resetDQLParts(array('select', 'orderBy'));
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
     * Use this method to reload the queryBuilder's available columns.
     * Changing this between calls allows custom filters to override prohibited column configuration
     *
     *
     * @param $prohibitedColumns
     * @return $this
     * @throws \Exception
     */
    public function refreshColumns($prohibitedColumns = array())
    {
        $this->availableTableColumns = $this->entityMetadataHelper->resolveAvailableTableColumns(
            $this->sourceEntityName,
            $prohibitedColumns
        );

        return $this;
    }


    /**
     * Generate a queryAlias for doctrine based on the fully qualified entity namespace
     *
     * @param $entityName
     * @return string
     */
    public function getEntityShortName($entityName)
    {
        $nameSpaceSegments = explode('\\', $entityName);

        return strtoupper(end($nameSpaceSegments));
    }


    /**
     * When showing HTML select filters on association fields, all possible data
     * should be preloaded into the filter fields. Since the QueryBuilderService
     * keeps track on what is joined in a separate query, the QueryBuilderService is able to
     * "eager load" this association data relatively easy.
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function preLoadAllAssociationFields()
    {
        $returnData = array();
        $entityMetadata = $this->entityMetadataHelper->getEntityMetadata($this->sourceEntityName);

        $fieldNames = array_merge(array_keys($this->subQueries), array_keys($this->prioritizedSubQueries));
        foreach ($fieldNames as $associationField) {
            $query = $this->entityManager->createQueryBuilder($associationField);
            $fieldData = $entityMetadata->getAssociationMapping($associationField);
            $query->from($fieldData['targetEntity'], $associationField);
            foreach ($this->selectedTableColumns[$associationField] as $field) {
                $query->addSelect(str_replace($associationField, $associationField . '.', $field));
            }

            $returnData[$associationField] = $query->getQuery()->getResult();
        }

        return $returnData;
    }

    /**
     * Reset the queryBuilder to an initial state
     */
    protected function reset()
    {
        $this->selectedTableColumns = array();
        $this->queryBuilder->resetDQLPart('select');
        $this->subQueries = array();
        $this->prioritizedSubQueries = array();
        $this->queryBuilder->resetDQLPart('join');
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
        if (!($query = $this->getSubQuery($sourceFieldName))) {
            $this->subQueries[$sourceFieldName] = $this->createSubQuery($sourceFieldName, $targetEntityName);
            $query = $this->subQueries[$sourceFieldName];
        }

        $query->addSelect(sprintf(
            "%s AS %s",
            $this->getEntityShortName($targetEntityName) . '.' . $targetFieldName,
            $sourceFieldName . $targetFieldName
        ));
        $this->addToSelectedTableColumns($sourceFieldName . '.' . $targetFieldName, $sourceFieldName);
    }

    /**
     * Appends a column to the selected columns by re-selecting all fields
     *
     * TODO: obviously a dirty/intensive way of adding a single column, we should look for a better implementation
     *
     * @param $fieldName
     * @return bool
     * @throws \Exception
     */
    private function addSingleSelect($fieldName)
    {
        if (!array_key_exists($fieldName, $this->availableTableColumns)) {
            return false;
        }

        $newColumns = array();
        foreach ($this->selectedTableColumns as $columnName => $value) {
            if (!is_array($value)) {
                $newColumns[] = $columnName;
                continue;
            }

            foreach ($value as $selectedColumn => $safeName) {
                $newColumns[] = $selectedColumn;
            }
        }

        $newColumns[] = $fieldName;
        $this->select($newColumns);
    }

    /**
     * Resolves if a property should be queried in a separate query.
     * Wil answer false for joinable entities, which occurs when selecting ONE_TO_ONE or MANY_TO_ONE
     * Also validates a joinable field.
     *
     * @param $fieldName
     * @return bool
     */
    private function needsSubQuery($fieldName)
    {
        $fieldNameSegments = explode(".", $fieldName);
        $fieldName = reset($fieldNameSegments);

        if (count($fieldNameSegments) <= 1) {
            return false;
        }

        $type = $this->entityMetadataHelper->getAssociationType($this->sourceEntityName, $fieldName);
        if (!$type) {
            return false;
        }

        if (in_array($type, array(MetaData::ONE_TO_ONE, MetaData::MANY_TO_ONE))) {
            return false;
        }

        return true;
    }

    /**
     * Simple wrapper around if statements to retrieve the correct subQuery
     * Because a subQuery might be contained by the subQueries or the prioritizedSubQueries property.
     *
     * @param $key
     * @return QueryBuilder|bool
     */
    private function getSubQuery($key)
    {
        if (array_key_exists($key, $this->subQueries)) {
            return $this->subQueries[$key];
        } elseif (array_key_exists($key, $this->prioritizedSubQueries)) {
            return $this->prioritizedSubQueries[$key];
        }

        return false;
    }

    /**
     * Moves a subQuery to the prioritizedSubQueries
     * and removes the "where in" clause that links it back to the main query
     *
     * @param $key
     * @return QueryBuilder
     */
    private function prioritizeSubQuery($key)
    {
        $query = $this->subQueries[$key];
        unset($this->subQueries[$key]);
        $query->resetDQLPart('where');
        $this->prioritizedSubQueries[$key] = $query;

        return $query;
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
    private function createSubQuery($sourceFieldName, $targetEntityName)
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
            $query->innerJoin(
                $this->getEntityShortName($sourceEntityName) . '.' . $sourceFieldName,
                $this->getEntityShortName($targetEntityName)
            );
            $query->where(sprintf('%s IN (:resultIds)', $identityColumn));

            return $query;
        }


        throw new \Exception(
            sprintf("Unsupported association type: %s", $associationType)
        );
    }

    /**
     * Naming things in doctrine can be hard. This method resolves your FieldName into the named
     * column of the doctrine query.
     *
     * @param $fieldName
     * @return bool|string
     * @throws \Exception
     */
    private function getSelectorForField($fieldName)
    {
        if (!$this->isSelectedField($fieldName)) {
            return false;
        }

        $isSubQuery = $this->needsSubQuery($fieldName);
        $query = $this->getQueryForField($fieldName);
        $fieldNameSegments = explode(".", $fieldName);
        $fieldName = reset($fieldNameSegments);
        $entityAlias = $query->getDQLPart('from')[0]->getAlias();

        // When dealing with one-to-one or many-to-many associations, the entityAlias is the joined alias
        if (count($fieldNameSegments) >= 2 && !$isSubQuery || $isSubQuery) {
            $associationType = $this->entityMetadataHelper->getAssociationType($this->sourceEntityName, $fieldName);
            if (!$associationType) {
                throw new \Exception("Could not determine the association type when building a where clause");
            }

            $changedEntityAliasTypes = array(MetaData::ONE_TO_ONE, MetaData::MANY_TO_MANY, MetaData::MANY_TO_ONE);
            if (in_array($associationType, $changedEntityAliasTypes)) {
                $joins = $query->getDQLPart('join');
                foreach ($joins[$entityAlias] as $join) {
                    if ($join->getJoin() == $entityAlias . '.' . $fieldName) {
                        $entityAlias = $join->getAlias();
                    }
                }
            }
            $fieldName = end($fieldNameSegments);
        }

        return $entityAlias . '.' . $fieldName;
    }

    /**
     * Recursive replacement of PHP's in_array()
     *
     * @param $fieldName
     * @return bool
     */
    private function isSelectedField($fieldName)
    {
        $searchField = str_replace('.', '', $fieldName);
        foreach ($this->selectedTableColumns as $key => $value) {
            if ((is_array($value) && in_array($searchField, $value)) || $searchField == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * To keep track on the queried columns, this internal method is used to add them to a list
     * @param $name
     * @param bool $parent
     */
    private function addToSelectedTableColumns($name, $parent = false)
    {
        $value = str_replace('.', '', $name);
        if ($parent) {
            if (!isset($this->selectedTableColumns[$parent])) {
                $this->selectedTableColumns[$parent] = array();
            }
            $this->selectedTableColumns[$parent][$name] = $value;

            return;
        }
        $this->selectedTableColumns[$name] = $value;
    }
}
