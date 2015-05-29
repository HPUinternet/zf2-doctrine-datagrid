<?php namespace Wms\Admin\DataGrid\Service;

use Wms\Admin\DataGrid\Options\ModuleOptions;
use Wms\Admin\DataGrid\Model\TableModel as Table;
use Wms\Admin\DataGrid\Service\EntityMetadataHelper;
use Wms\Admin\DataGrid\Service\QueryBuilderHelper;

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
     * based on your data configuration in the object
     * @return Table
     */
    public function getTable()
    {
        $table = new Table();

        $table->setAvailableHeaders($this->queryBuilder->getAvailableTableColumns());
        $table->setUsedHeaders($table->calculateTableHeader($this->queryBuilder->getSelectedTableColumns()));
        $table->setDataTypes($this->queryBuilder->getTableColumnTypes());
        $table->setAndParseRows($this->queryBuilder->getResultSet());
        $table->setPageNumber($this->page);
        $table->setMaxPageNumber($this->calculateMaxPages());

        return $table;
    }

    public function selectColumns(array $columns)
    {
        $this->queryBuilder->select($columns);
    }

    public function setPage($pageNumber)
    {
        $this->page = $pageNumber;
        $this->queryBuilder->limit($pageNumber, $this->getModuleOptions()->getItemsPerPage());
    }

    public function orderBy($column, $order)
    {
        // @todo: input valdiation should be handled by zend form
        if (in_array($column, $this->queryBuilder->getAvailableTableColumns()) && (strtolower($order) == 'asc' || strtolower($order) == 'desc')) {
            $this->queryBuilder->orderBy($column, $order);
        }
    }

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
