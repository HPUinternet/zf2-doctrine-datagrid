<?php namespace Wms\Admin\DataGrid\Service;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Model\TableModel as Table;

class TableBuilderService
{

    /**
     * @var Int
     */
    protected $page = 1;

    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    /**
     * @var QueryBuilderHelper
     */
    private $queryBuilder;

    /**
     * @var Boolean since resolving association columns rapidly leads to more queries, you can turn it off here
     */
    public $resolveAssociationColumns = true;

    /**
     * @param ModuleOptions $moduleOptions
     * @param QueryBuilderHelper $queryBuilderHelper
     */
    public function __construct(ModuleOptions $moduleOptions, QueryBuilderHelper $queryBuilderHelper)
    {
        $this->setModuleOptions($moduleOptions);
        $this->queryBuilder = $queryBuilderHelper;

        // Make sure data retrieval is default when not configured
        $this->queryBuilder->refreshColumns($this->moduleOptions->getProhibitedColumns());
        $this->selectColumns($this->getModuleOptions()->getDefaultColumns());
        $this->setPage($this->page, $this->getModuleOptions()->getItemsPerPage());
    }

    /**
     * Retrieve an new TableModel
     * based on your data configuration in this object
     * @return Table
     */
    public function getTable()
    {
        $table = new Table();

        // configure the minimal TableModel
        $table->setAvailableHeaders($this->queryBuilder->getAvailableTableColumns());
        $table->setUsedHeaders($table->calculateTableHeader($this->queryBuilder->getSelectedTableColumns()));
        $table->setAndParseRows($this->queryBuilder->getResultSet());

        // configure "optional" data in the TableModel
        $table->setDataTypes($this->queryBuilder->getTableColumnTypes());
        $table->setPageNumber($this->page);
        $table->setMaxPageNumber($this->calculateMaxPages());
        if ($this->resolveAssociationColumns) {
            $table->setAvailableFilterValues($this->queryBuilder->preLoadAllAssociationFields());
        }

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
    public function orderBy($column, $order)
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
            if (!empty($searchParam)) {
                $this->queryBuilder->where($fieldName, "%".$searchParam."%");
            }
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
     * @return QueryBuilderHelper
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilderHelper $queryBuilder
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}
