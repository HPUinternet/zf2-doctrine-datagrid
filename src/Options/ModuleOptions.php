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
     * Columns available in the associated properties of your entity in the dataGrid
     *
     * @var array
     */
    protected $joinableColumns = array();

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
    public function getJoinableColumns()
    {
        return $this->joinableColumns;
    }

    /**
     * @param array $joinableColumns
     */
    public function setJoinableColumns($joinableColumns)
    {
        $this->joinableColumns = $joinableColumns;
    }

}
