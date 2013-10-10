<?php
class Storm_Correios_Model_Catalog_Resource_Setup extends Mage_Catalog_Model_Resource_Setup
{
    /**
     * Gets the list of all attributes sets
     * 
     * @param string $entityTypeId
     * @return array
     */
    public function getAllAttributeSets($entityTypeId = null)
    {
	$select = $this->_conn->select()
            ->from($this->getTable('eav/attribute_set'));

        $bind = array();
        if ($entityTypeId !== null) {
            $bind['entity_type_id'] = $this->getEntityTypeId($entityTypeId);
            $select->where('entity_type_id = :entity_type_id');
        }

        return $this->_conn->fetchAll($select, $bind);
    }
}