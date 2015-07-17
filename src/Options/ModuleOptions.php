<?php namespace Wms\Admin\DataGrid\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * The DataGrid module cannot function without a fully qualified name of your entity.
     * Set that name in this option
     * @var string
     */
    protected $entityName = '';

    /**
     * Default visible properties of your entity in the dataGrid
     * @var array
     */
    protected $defaultColumns = array();

    /**
     * Columns/FieldNames of your entity that should be hidden from the UI
     * @var array
     */
    protected $prohibitedColumns = array();

    /**
     * How many items do you want to display on a single page?
     * @var int
     */
    protected $itemsPerPage = 10;

    /**
     * Got any Doctrine QueryFilters you want to configure before fetching data?
     * configure them here
     * @var array
     */
    protected $filters = array();

    /**
     * Most of our dataTypes are resolved from your entity configuration, but if you have explicit
     * wishes on how to render/parse certain fieldnames, you can configure them in this configuration value
     * E.G.: 'imagePath => image' will result in the string being parsed as an image.
     * @var array
     */
    protected $renders = array();

    /**
     * Override or configure additional searchFilters here.
     * Please advise the docs for more information about the requirements and security risks a custom searchFilter
     * has before implementing it in the DataGrid module.
     * @var array
     */
    protected $searchFilters = array();

    /**
     * Typical overview pages contain inline links to detail/edit pages of a single entity. You can configure
     * the DataGrid module in this key to automatically append a link in each row here.
     * @var array
     */
    protected $optionRoutes = array();

    /**
     * The shipped CSS of the DataGrid is set to "fill" the table within its container which might result in some
     * fields containing simple integers or yes/no strings being placed in ultra wide table cells. This option allows
     * inline editing of tableHeader widths.
     *
     * @var array
     */
    protected $columnWidths = array();

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

    /**
     * @return array
     */
    public function getSearchFilters()
    {
        return $this->searchFilters;
    }

    /**
     * @param array $searchFilters
     */
    public function setSearchFilters($searchFilters)
    {
        $this->searchFilters = $searchFilters;
    }

    /**
     * @return array
     */
    public function getOptionRoutes()
    {
        return $this->optionRoutes;
    }

    /**
     * @param array $optionRoutes
     */
    public function setOptionRoutes($optionRoutes)
    {
        $this->optionRoutes = $optionRoutes;
    }

    /**
     * @return array
     */
    public function getColumnWidths()
    {
        return $this->columnWidths;
    }

    /**
     * @param array $columnWidths
     */
    public function setColumnWidths($columnWidths)
    {
        $this->columnWidths = $columnWidths;
    }
}
