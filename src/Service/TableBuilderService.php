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
    protected $entityProperties;

    /**
     * @var Array
     */
    protected $availableTableColumns;

    /**
     * @var Array;
     */
    protected $visibleTableColumns;


    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(ModuleOptions $moduleOptions, EntityManager $entityManager)
    {
        $this->setModuleOptions($moduleOptions);
        $this->setEntityManager($entityManager);
        $this->setVisibleTableColumns($this->getModuleOptions()->getDefaultColumns());
        $this->setQueryBuilder($entityManager->getRepository($moduleOptions->getEntityName())->createQueryBuilder($this->getEntityShortName($moduleOptions->getEntityName())));
    }

    public function getTable()
    {
        // Retrieve data from Doctrine and the dataprovider
        $this->setAvailableTableColumns($this->resolveAvailableTableColumns());
        $tableData = $this->getTableData();

        $table = new Table();
        $table->setHeaderRow($this->getVisibleTableColumns());
        $table->setAndParseRows($tableData);

        return $table;
    }

    /**
     * Resolve the available columns based on the configured entity.
     * Will also resolve the available columns for the associated properties
     *
     * @return array
     * @throws \Exception
     */
    public function resolveAvailableTableColumns() {
        if(!$this->getEntityProperties()) {
            $this->setEntityProperties($this->resolveEntityProperties());
        }

        $returnData = array();
        $entityProperties = array();
        foreach($this->getEntityProperties() as $property) {
            if($property['type'] === "association" && isset($property['targetEntity'])) {
                $targetEntity = $property['targetEntity'];
                if(!array_key_exists($targetEntity, $entityProperties)) {
                    $entityProperties[$targetEntity] = $this->resolveEntityProperties($targetEntity);
                }
                $targetEntityProperties = $entityProperties[$targetEntity];
                foreach($targetEntityProperties as $targetEntityProperty) {
                    $returnData[] = $property['fieldName'].'.'.$targetEntityProperty['fieldName'];
                }
            } else {
                $returnData[] = $property['fieldName'];
            }
        }
        return $returnData;
    }

    public function resolveEntityProperties($entityClass = "")
    {
        if($entityClass === "") {
            $entityClass = $this->getModuleOptions()->getEntityName();
        }

        if (!$entityClass) {
            throw new \Exception("No Entity configured for the dataGrid module");
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
        $this->selectColumns($this->getVisibleTableColumns());
        $tableData = $this->getQueryBuilder()->getQuery()->execute();

        return $tableData;
    }

    /**
     * Builds the selectquery for the database, based on the available entity properties
     *
     * @param array $columns
     * @throws \Exception
     */
    public function selectColumns(array $columns)
    {
        $this->getQueryBuilder()->resetDQLPart('select');
        $joinedProperties = array();

        foreach ($columns as $selectColumn) {
            if (!in_array($selectColumn, $this->getAvailableTableColumns())) {
                continue;
            }

            $selectColumnParts = explode(".", $selectColumn);
            $selectColumn = reset($selectColumnParts);
            $columnMetadata = $this->getEntityProperties()[$selectColumn];
            $entityShortName = $this->getEntityShortName($this->getModuleOptions()->getEntityName());

            // Make sure associations are joined by looking at the targetEntity and sourceToTargetKeyColumns fields
            if ($columnMetadata['type'] === 'association') {
                if (!isset($columnMetadata['targetEntity']) || empty($columnMetadata['targetEntity'])) {
                    throw new \Exception(sprintf('Can\'t create join query parameters for %s in Entity %s',
                        $selectColumn['fieldName'], $entityShortName));
                }

                // @todo: OneToMany vanuit de huidige entity
                // @todo: ManyToMany
                if (!isset($columnMetadata['joinColumns']) || empty($columnMetadata['joinColumns'])) {
                    continue;
                }

                if (!array_key_exists($selectColumn, $joinedProperties)) {
                    $joinedEntityAlias = $this->getEntityShortName($columnMetadata['targetEntity']).count($joinedProperties);
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
                continue;
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
    public function getEntityProperties()
    {
        return $this->entityProperties;
    }

    /**
     * @param Array $entityProperties
     */
    public function setEntityProperties($entityProperties)
    {
        $this->entityProperties = $entityProperties;
    }

    /**
     * @return Array
     */
    public function getVisibleTableColumns()
    {
        return $this->visibleTableColumns;
    }

    /**
     * @param Array $visibleTableColumns
     */
    public function setVisibleTableColumns($visibleTableColumns)
    {
        $this->visibleTableColumns = $visibleTableColumns;
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
}