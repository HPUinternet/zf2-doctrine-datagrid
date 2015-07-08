<?php namespace Wms\Admin\DataGrid\Service;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Model\TableModel as Table;
use Wms\Admin\DataGrid\Service\Interfaces\TableBuilderInterface;

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
        $this->searchFilterHelper->prepareFilters($this->queryBuilder);
        $dataTypes = array_merge($this->queryBuilder->getTableColumnTypes(), $this->moduleOptions->getRenders());
        $filterValues = $this->resolveAssociationColumns ? $this->queryBuilder->preLoadAllAssociationFields() : array();

        $table = new Table();
        $table->setDataTypes($dataTypes);
        $table->addHeaders(
            $this->queryBuilder->getAvailableTableColumns(),
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
     * Sets default options and parameters, read from the module configuration
     */
    private function init()
    {
        $this->queryBuilder->refreshColumns($this->getModuleOptions()->getProhibitedColumns());
        $this->setPage($this->page, $this->getModuleOptions()->getItemsPerPage());
        if (empty($this->queryBuilder->getSelectedTableColumns())) {
            $this->selectColumns($this->getModuleOptions()->getDefaultColumns());
        }
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
