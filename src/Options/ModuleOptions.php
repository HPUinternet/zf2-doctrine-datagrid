<?php namespace Wms\Admin\DataGrid\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

    /**
     * @var string
     */
    protected $entityName = '';

    /**
     * Default visible columns in the dataGrid
     *
     * @var array
     */
    protected $defaultColumns = array();

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
}
