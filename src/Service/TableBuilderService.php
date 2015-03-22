<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\Mapping\ClassMetadata;
use Wms\Admin\DataGrid\Options\ModuleOptions;
use Doctrine\ORM\EntityManager;

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
     * @var Array
     */
    private $hiddenColumns;

    /**
     * @var Array
     */
    private $columns;

    public function __construct(ModuleOptions $moduleOptions, EntityManager $entityManager)
    {
        $this->setModuleOptions($moduleOptions);
        $this->setEntityManager($entityManager);
    }

    public function getTable()
    {
        $this->setColumns($this->getColumnsFromEntity());
    }

    public function getColumnsFromEntity()
    {
        $entityClass = $this->getModuleOptions()->getEntityName();
        if (!$entityClass) {
            throw \Exception("No Entity found for the dataGrid module");
        }

        $metaData = $this->getEntityManager()->getClassMetadata($entityClass);
        return $this->parseMetaDataToFieldArray($metaData);
    }

    protected function parseMetaDataToFieldArray(ClassMetadata $metaData){
        $columns = array();
        foreach ($metaData->reflFields as $fieldName => $reflectionData) {
            if (array_key_exists($fieldName, $metaData->fieldMappings)) {
                $fieldData = $metaData->getFieldMapping($fieldName);
                $columns[$fieldName] = $fieldData;
            } elseif (array_key_exists($fieldName, $metaData->associationMappings)) {
                $fieldData = $metaData->getAssociationMapping($fieldName);
                $fieldData['type'] = 'association';
                $columns[$fieldName] = $fieldData;
            } else {
                throw new \Exception(sprintf('Can\'t map %s in the %s Entity', $fieldName, $metaData->name));
            }
        }
        return $columns;
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
     * @return Array
     */
    public function getHiddenColumns()
    {
        return $this->hiddenColumns;
    }

    /**
     * @param Array $hiddenColumns
     */
    public function setHiddenColumns($hiddenColumns)
    {
        $this->hiddenColumns = $hiddenColumns;
    }

    /**
     * @return Array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
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
}