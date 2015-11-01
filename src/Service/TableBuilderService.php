<?php namespace Wms\Admin\DataGrid\Service;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Model\TableModel as Table;
use Wms\Admin\DataGrid\SearchFilter\SearchFilterHelper;

class TableBuilderService implements TableBuilderInterface
{
    /**
     * @var Int
     */
    protected $page = 1;

    /**
     * @var array
     */
    protected $usedFilters = array();

    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    /**
     * @var QueryBuilderService
     */
    private $queryBuilder;

    /**
     * @var SearchFilterHelper
     */
    private $searchFilterHelper;

    /**
     * @var Boolean since resolving association columns rapidly leads to more queries, you can turn it off here
     */
    public $resolveAssociationColumns = true;

    /**
     * @var Keeps track if the filters are prepared
     */
    private $filtersPrepared = false;

    /**
     * @param ModuleOptions $moduleOptions
     * @param QueryBuilderService $queryBuilderService
     * @param SearchFilterHelper $searchFilterHelper
     */
    public function __construct(
        ModuleOptions $moduleOptions,
        QueryBuilderService $queryBuilderService,
        SearchFilterHelper $searchFilterHelper
    ) {
        $this->setModuleOptions($moduleOptions);
        $this->queryBuilder = $queryBuilderService;
        $this->searchFilterHelper = $searchFilterHelper;
        $this->init();
    }

    /**
     * Retrieve an new TableModel
     * based on your data configuration in this object
     * @return Table
     */
    public function getTable()
    {
        if (empty($this->queryBuilder->getSelectedTableColumns())) {
            $this->filtersPrepared = false;
            $this->selectColumns($this->getModuleOptions()->getDefaultColumns());
        }

        $dataTypes = array_merge($this->queryBuilder->getTableColumnTypes(), $this->moduleOptions->getRenders());
        $filterValues = $this->resolveAssociationColumns ? $this->queryBuilder->preLoadAllAssociationFields() : array();
        $tableHeaders = $this->sortColumns(
            $this->queryBuilder->getAvailableTableColumns(),
            $this->moduleOptions->getDefaultColumns()
        );

        $table = new Table();
        $table->setDataTypes($dataTypes);
        $table->addHeaders(
            $tableHeaders,
            $this->queryBuilder->getSelectedTableColumns(),
            $this->moduleOptions->getColumnWidths()
        );

        $table->addRows($this->queryBuilder->getResultSet());
        $table->setPrefetchedFilterValues($filterValues);
        $table->addFilters($this->searchFilterHelper->getFilters(), $this->usedFilters);
        $table->setPageNumber($this->page);
        $table->setMaxPageNumber($this->calculateMaxPages());
        $table->setOptionRoutes($this->moduleOptions->getOptionRoutes());

        return $table;
    }

    /**
     * @param array $columns
     * @throws \Exception
     */
    public function selectColumns(array $columns)
    {
        $this->queryBuilder->select($columns);
        if (!$this->filtersPrepared) {
            $this->searchFilterHelper->prepareFilters($this->queryBuilder);
            $this->filtersPrepared = true;
        }
    }

    /**
     * @param $pageNumber
     */
    public function setPage($pageNumber)
    {
        $this->page = $pageNumber;
        $this->queryBuilder->limit($pageNumber, $this->getModuleOptions()->getItemsPerPage());
    }

    /**
     * @param $column
     * @param $order
     */
    public function orderBy($column, $order = 'asc')
    {
        // @todo: input valdiation should be handled by zend form
        if (in_array($column, $this->queryBuilder->getAvailableTableColumns())
            && (strtolower($order) == 'asc' || strtolower($order) == 'desc')
        ) {
            $this->queryBuilder->orderBy($column, $order);
        }
    }

    /**
     * search for entities by adding statements as a
     * where clause.
     *
     * @param array $searchParams
     */
    public function search(array $searchParams)
    {
        if (empty($this->queryBuilder->getSelectedTableColumns())) {
            $this->selectColumns($this->getModuleOptions()->getDefaultColumns());
        }

        foreach ($searchParams as $fieldName => $searchParam) {
            if ($searchParam == "") {
                continue;
            }
            $this->usedFilters[$fieldName] = $searchParam;

            if ($this->searchFilterHelper->hasFilter($fieldName)) {
                $this->searchFilterHelper->useFilter($fieldName, $searchParam, $this->queryBuilder);
                continue;
            }

            $this->queryBuilder->where($fieldName, "%" . $searchParam . "%");
        }
    }

    /**
     * @return float|int
     */
    protected function calculateMaxPages()
    {
        $maxResults = $this->queryBuilder->getMaxResultCount();
        $itemsPerPage = $this->getModuleOptions()->getItemsPerPage();
        if ($maxResults <= $itemsPerPage) {
            return 1;
        }

        return ceil($maxResults / $itemsPerPage);
    }

    /**
     * Sorts array values by an second array, containing the sort order
     *
     * @param $tableColumns
     * @param $defaultOrder
     * @return array
     */
    protected function sortColumns($tableColumns, $defaultOrder)
    {
        $orderedColumns = array();

        foreach ($defaultOrder as $column) {
            $orderedColumns[] = $column;
            if ($key = array_search($column, $tableColumns)) {
                unset($tableColumns[$key]);
            }
        }
        $orderedColumns = array_merge($orderedColumns, $tableColumns);

        return $orderedColumns;
    }

    /**
     * Sets default options and parameters, read from the module configuration
     */
    private function init()
    {
        $this->queryBuilder->refreshColumns($this->getModuleOptions()->getProhibitedColumns());
        $this->setPage($this->page, $this->getModuleOptions()->getItemsPerPage());
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
}
