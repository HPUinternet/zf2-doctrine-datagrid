<?php namespace Wms\Admin\DataGrid\Service;

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
    protected $selectedTableColumns;

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
        $this->selectColumns($this->getModuleOptions()->getDefaultColumns());
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
        $joinedProperties = array();
        $entityMetaData = $this->entityMetadataHelper->parseMetaDataToFieldArray(
            $this->entityMetadataHelper->getEntityMetadata($this->getModuleOptions()->getEntityName())
        );

        foreach ($columns as $selectColumn) {
            if (!in_array($selectColumn, $this->getAvailableTableColumns())) {
                continue;
            }

            $selectColumnParts = explode(".", $selectColumn);
            $selectColumn = reset($selectColumnParts);
            $columnMetadata = $entityMetaData[$selectColumn];
            $entityShortName = $this->getEntityShortName($this->getModuleOptions()->getEntityName());

            // Make sure associations are joined by looking at the targetEntity and sourceToTargetKeyColumns fields
            if ($columnMetadata['type'] === 'association') {
                if (!isset($columnMetadata['targetEntity']) || empty($columnMetadata['targetEntity'])) {
                    throw new \Exception(sprintf('Can\'t create join query parameters for %s in Entity %s',
                        $selectColumn['fieldName'], $entityShortName));
                }

                // @todo: OneToMany vanuit de huidige entity
                // @todo: ManyToMany: Deze wordt even geskipped omdat er onenigheid is geconstanteerd in de implementaties hiervan

                /*
                 * Bij een OneToMany is de waarde van de property niet aanwezig (omdat dit zich in een koppeltabel of de andere entity bevint)
                 * Dit zorgt er voor dat de volgende twee strategieën beschikbaar zijn om de data alsnog op te halen:
                 * 1. een subquery in SQL gemaakt moet worden om de resultaten te tonen
                 * 2. een tweede query achteraf afvuren om deze achteraf bij de results weer in te kunnen voeren
                 */
                if ($selectColumn == "productReviews") {
                    echo 'start debug oneToMany';
                }

                // Deal with OneToMany and ManyToMany associated fields
                if (!$columnMetadata['isOwningSide']) {

                    continue;
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
                continue;
            }

            $this->getQueryBuilder()->addSelect($entityShortName . '.' . $selectColumn);
        }

        return $this;
    }

    /**
     * Set the page for pagination
     *
     * @param $pageNumber
     * @param int $itemsPerPage
     * @return $this
     */
    public function setPage($pageNumber, $itemsPerPage = 30)
    {
        $offset = ($pageNumber == 0) ? 0 : ($pageNumber - 1) * $itemsPerPage;
        $this->getQueryBuilder()->setMaxResults($itemsPerPage);
        $this->getQueryBuilder()->setFirstResult($offset);

        return $this;
    }

    /**
     * Retrieve an new TableModel
     * based on your data configuration in the object
     * @return Table
     */
    public function getTable()
    {
        // Retrieve data from Doctrine and the dataprovider
        $tableData = $this->getQueryBuilder()->getQuery()->execute();

        echo '<pre>';
        var_dump($tableData);
        echo '</pre>';
//        die('einde dump in getTable');

        $table = new Table();
        $table->setAvailableHeaders($this->getAvailableTableColumns());
        $table->setAndParseRows($tableData);

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
            $this->entityMetadataHelper->getEntityMetadata($this->getModuleOptions()->getEntityName())
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
                $this->entityMetadataHelper->getEntityMetadata($targetEntity)
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

    protected function AddsubQuery($enityName)
    {

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->from($enityName, $this->getEntityShortName($enityName));

        $this->subQueries[$enityName] = $queryBuilder;
    }
}