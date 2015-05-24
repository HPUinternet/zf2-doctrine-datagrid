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
    private $metaData;

    /**
     * @var EntityManager
     */
    private $entityManger;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManger($entityManager);
        $this->metaData = array();
    }

    #region SERVICE INTERACTIONS

    /**
     * Get EntityMapping by entityName
     *
     * @param $entityName
     * @return ClassMetadata|bool
     */
    public function getMetaData($entityName, $addIfNotExistent = true)
    {
        if (!array_key_exists($entityName, $this->metaData)) {
            if (!$addIfNotExistent) {
                return false;
            }
            $this->addMetadata($entityName);
        }

        return $this->metaData[$entityName];
    }

    /**
     * Set Entity Mapping data for an single entity
     *
     * @param $entityName
     * @param null $mappingData
     * @return $this
     */
    public function addMetadata($entityName, $mappingData = null)
    {
        if (is_null($mappingData)) {
            $mappingData = $this->getEntityManger()->getClassMetadata($entityName);
        }

        $this->metaData[$entityName] = $mappingData;

        return $this;
    }

    /**
     * Transform a class metaData object to a more user-friendly array
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
     * @return array
     * @throws \Exception
     */
    public function resolveAvailableTableColumns($entityName, $prohibitedColumns = array())
    {
        $entityProperties = $this->parseMetaDataToFieldArray(
            $this->getMetaData($entityName)
        );

        $returnData = array();
        foreach ($entityProperties as $property) {
            if (in_array($property['fieldName'], $prohibitedColumns)) {
                continue;
            }

            if ($property['type'] != "association") {
                $returnData[] = $property['fieldName'];
                continue;
            }

            if (!isset($property['targetEntity'])) {
                throw new \Exception(
                    sprintf('%s is configured as a association, but no target Entity found', $property['fieldName'])
                );
            }

            $targetEntity = $property['targetEntity'];
            $targetEntityProperties = $this->parseMetaDataToFieldArray(
                $this->getMetaData($targetEntity)
            );

            foreach ($targetEntityProperties as $targetEntityProperty) {
                if ($targetEntityProperty['type'] !== "association"
                    && !array_search($targetEntityProperty, $prohibitedColumns)
                ) {
                    $returnData[] = $property['fieldName'] . '.' . $targetEntityProperty['fieldName'];
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