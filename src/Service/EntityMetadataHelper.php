<?php namespace Wms\Admin\DataGrid\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class EntityMetadataHelper
 * @package Wms\Admin\DataGrid\Service
 *
 * Basically a normal class which is used to resolve (and cache) the Metadata for the provided Entity namespaces
 * This is done to prevent multiple calls to the performance sensitive method getClassMetadata of doctrine.
 */
class EntityMetadataHelper
{
    /**
     * @var Array
     */
    private $entityMetadata;

    /**
     * @var EntityManager
     */
    private $entityManger;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManger($entityManager);
        $this->entityMetadata = array();
    }

    #region SERVICE INTERACTIONS

    /**
     * Get EntityMapping by entityName
     *
     * @param $entityName
     * @param bool $addIfNotExistent
     * @return bool|ClassMetadata
     */
    public function getEntityMetadata($entityName, $addIfNotExistent = true)
    {
        if (!array_key_exists($entityName, $this->entityMetadata)) {
            if (!$addIfNotExistent) {
                return false;
            }
            $this->addEntityMetadata($entityName);
        }

        return $this->entityMetadata[$entityName];
    }

    /**
     * Set Entity Mapping data for an single entity
     *
     * @param $entityName
     * @param null $mappingData
     * @return $this
     */
    public function addEntityMetadata($entityName, $mappingData = null)
    {
        if (is_null($mappingData)) {
            $mappingData = $this->getEntityManger()->getClassMetadata($entityName);
        }

        $this->entityMetadata[$entityName] = $mappingData;

        return $this;
    }

    /**
     * Transform a class entityMetadata object to a more user-friendly array
     *
     * @param ClassMetadata $metaData
     * @return array
     * @throws \Exception
     */
    public function parseMetaDataToFieldArray(ClassMetadata $metaData)
    {
        $columns = array();
        foreach ($metaData->reflFields as $fieldName => $reflectionData) {
            if (array_key_exists($fieldName, $metaData->fieldMappings)) {
                $fieldData = $metaData->getFieldMapping($fieldName);
                $columns[$fieldName] = $fieldData;
            } elseif (array_key_exists($fieldName, $metaData->associationMappings)) {
                $fieldData = $metaData->getAssociationMapping($fieldName);
                $fieldData['associationType'] = $fieldData['type'];
                $fieldData['type'] = 'association';
                $columns[$fieldName] = $fieldData;
            } else {
                throw new \Exception(sprintf('Can\'t map %s in the %s Entity', $fieldName, $metaData->name));
            }
        }

        return $columns;
    }

    /**
     * Resolve the available columns based on the configured entity.
     * Will also resolve the available columns for the associated properties
     *
     * @param $entityName
     * @param array $prohibitedColumns
     * @return array
     * @throws \Exception
     */
    public function resolveAvailableTableColumns($entityName, $prohibitedColumns = array())
    {
        $entityProperties = $this->parseMetaDataToFieldArray(
            $this->getEntityMetadata($entityName)
        );

        $returnData = array();
        foreach ($entityProperties as $property) {
            $fieldName = $property['fieldName'];
            if (in_array($fieldName, $prohibitedColumns) || empty($fieldName)) {
                continue;
            }

            if ($property['type'] != "association") {
                $returnData[$fieldName] = $property['type'];
                continue;
            }

            if (!isset($property['targetEntity'])) {
                throw new \Exception(
                    sprintf('%s is configured as a association, but no target Entity found', $property['fieldName'])
                );
            }

            $targetEntity = $property['targetEntity'];
            $targetEntityProperties = $this->parseMetaDataToFieldArray(
                $this->getEntityMetadata($targetEntity)
            );

            foreach ($targetEntityProperties as $targetEntityProperty) {
                if ($targetEntityProperty['type'] !== "association"
                    && !array_search($targetEntityProperty, $prohibitedColumns)
                ) {
                    $returnData[$fieldName . '.' . $targetEntityProperty['fieldName']] = $targetEntityProperty['type'];
                }
            }
        }

        return $returnData;
    }
    #endregion

    #region GETTERS & SETTERS

    /**
     * @return EntityManager
     */
    public function getEntityManger()
    {
        return $this->entityManger;
    }

    /**
     * @param EntityManager $entityManger
     */
    public function setEntityManger($entityManger)
    {
        $this->entityManger = $entityManger;
    }
    #endregion
}