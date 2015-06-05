<?php namespace Wms\Admin\DataGrid\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

    /**
     * @var string
     */
    protected $entityName = '';

    /**
     * Default visible properties of your entity in the dataGrid
     *
     * @var array
     */
    protected $defaultColumns = array();

    /**
     * @var array
     */
    protected $prohibitedColumns = array();

    /**
     * @var int
     */
    protected $itemsPerPage = 10;

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var array
     */
    protected $renders = array();

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return array
     */
    public function getDefaultColumns()
    {
        return $this->defaultColumns;
    }

    /**
     * @param array $defaultColumns
     */
    public function setDefaultColumns(array $defaultColumns)
    {
        $this->defaultColumns = $defaultColumns;
    }

    /**
     * @return array
     */
    public function getProhibitedColumns()
    {
        return $this->prohibitedColumns;
    }

    /**
     * @param array $prohibitedColumns
     */
    public function setProhibitedColumns($prohibitedColumns)
    {
        $this->prohibitedColumns = $prohibitedColumns;
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return array
     */
    public function getRenders()
    {
        return $this->renders;
    }

    /**
     * @param array $renders
     */
    public function setRenders($renders)
    {
        $this->renders = $renders;
    }
}
