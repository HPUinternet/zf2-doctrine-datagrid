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

}
