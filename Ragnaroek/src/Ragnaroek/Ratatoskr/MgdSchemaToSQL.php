<?php

namespace Ragnaroek\Ratatoskr;

use \DomDocument;

class MgdSchemaToSQL extends DomDocument
{
    const ATTR_NAME = 'name';
    const ATTR_TYPE = 'type';
    const ATTR_PROP = 'property';

    public function __construct()
    {
        parent::__construct('1.0', 'UTF-8');
    }

    public function addFile($filePath)
    {
        if ($this->documentElement == null) {
            $this->load($filePath);
            return;
        }

        $dd = new DomDocument();
        $dd->load($filePath);

        foreach ($dd->documentElement->getElementsByTagName(self::ATTR_TYPE) as $node) {
            $n = $this->importNode($node, true);
            $this->documentElement->appendChild($n);
        }
    }

    public function getMidgardTypes()
    {
        $nodes = $this->documentElement->getElementsByTagName(self::ATTR_TYPE);
        $types = array();
        foreach ($nodes as $node) {
            $types[] = $node->getAttribute(self::ATTR_NAME);
        }
        return $types; 
    }

    public function isMultilang($typeName)
    {
        $node = $this->getNodeByMidgardType($typeName);
        if ($node == null) {
            return false;
        }
        $nodes = $node->getElementsByTagName(self::ATTR_PROP);
        foreach ($nodes as $prop) {
            $attr = $prop->getAttribute('multilang');
            if (!$attr) {
                continue;
            }
            if ($attr == 'true' || $attr == 'yes') {
                return true;
            }
        }
        return false;
    }

    protected function getTable($node) 
    {
        return $node->getAttribute('table');
    }

    public function getTableByType($typeName)
    {
        $node = $this->getNodeByMidgardType($typeName);
        if ($node == null) {
            return null;
        }    
        return $this->getTable($node);
    }

    protected function getField($node)
    {
        $attr = $node->getAttribute('field');
        if (!$attr) {
            $attr = $node->getAttribute('upfield');
        }
        if (!$attr) {
            $attr = $node->getAttribute('parentfield');
        }
        return $attr;
    }

    protected function getNodeByMidgardType($type)
    {
        $_rf_name = substr($type, 10);
        $nodes = $this->documentElement->getElementsByTagName(self::ATTR_TYPE);
        foreach ($nodes as $node) {
            if ($node->getAttribute(self::ATTR_NAME) == $type) {
                return $node;
            }
            /* type name might have 'ragnaroek_' prefix, try without it */
            if (strpos($type, "ragnaroek") !== false) { 
                if ($node->getAttribute(self::ATTR_NAME) == $_rf_name) {
                    return $node;
                }
            }
        }
        return null;
    }

    protected function getProperties($typeName)
    {
        $node = $this->getNodeByMidgardType($typeName);
        $properties = $node->getElementByTagName(self::ATTR_PROP);
        $names = array();
        foreach ($properties as $property) {
            $names[] = $property->getAttribute(self::ATTR_NAME); 
        }
        return $names;
    }

    protected function getNodes($node)
    {
        return array();
    }

    public function getSQLUpdateTypePre($typeName, $workspaceID = 0, $sitegroupID = 0, $languageID = 0)
    {
        /* Get named node */
        $node = $this->getNodeByMidgardType($typeName);

        /* Determine multilang */
        $isMultilang = $this->isMultilang($typeName);

        /* No need to move content in case of non multilang type */
        if ($isMultilang === false) {
            return null;
        }

        /* Get table */
        $typeTable = $this->getTable($node);
        if ($typeTable === null || $typeTable === '') {
            $typeTable = $typeName;
        }

        $sql = "UPDATE " . $typeTable . " SET \n";

        /* Add Sql part for every property */
        $props = $node->getElementsByTagName(self::ATTR_PROP);
        foreach ($props as $property) {
            /* we need to move multilang content only */
            $ml = $property->getAttribute('multilang');
            if ($ml === null || $ml === '') {
                continue;
            }
            $field = $this->getField($property);
            if ($field === '' || $field === null) {
                $field = $property->getAttribute('name');
            }
            $table = $this->getTable($property);
            $sql .= $field . " = ";
            $_select = "(SELECT  {$table}.{$field} FROM {$table} WHERE {$table}.lang = {$languageID} AND {$table}.sid = {$typeTable}.id)";
            $sql .= "IF ($_select IS NULL, {$typeTable}.{$field}, $_select)";
            $sql .= ",\n";
        }

        /* Also, set workspace id */
        $sql .= "midgard_ws_id = {$workspaceID} \n";

        /* Set constraint */
        $sql .= "WHERE {$typeTable}.lang = {$languageID} AND {$typeTable}.sitegroup = {$sitegroupID} \n";

        return $sql;
    }

    public function getSQLDeleteTypePre($typeName, $languageID = 0)
    {
        /* Get named node */
        $node = $this->getNodeByMidgardType($typeName);

        /* Determine multilang */
        $isMultilang = $this->isMultilang($typeName);

        /* No need to move content in case of non multilang type */
        if ($isMultilang === false) {
            return null;
        }

        /* Get table */
        $typeTable = $this->getTable($node);
        if ($typeTable === null || $typeTable === '') {
            $typeTable = $typeName;
        }

        $sql = "DELETE FROM {$typeTable}_i WHERE lang = {$languageID} \n";

        return $sql;
    }

    private function getMetadataFields()
    {
        $fields = array(
            "metadata_creator",
            "metadata_created",
            "metadata_revisor",
            "metadata_revised",
            "metadata_revision",
            "metadata_locker",
            "metadata_locked", 
            "metadata_approver",
            "metadata_approved",
            "metadata_authors",
            "metadata_owner",
            "metadata_schedule_start",
            "metadata_schedule_end",
            "metadata_hidden",
            "metadata_nav_noentry",
            "metadata_size",
            "metadata_published",
            "metadata_score",
            "metadata_exported",
            "metadata_imported",
            "metadata_deleted" 
        );

        return $fields;
    }

    public function getSQLInsertType($typeName, $sitegroupID, $workspaceID, $languageID)
    {
        /* TODO & FIXME 
         * Set metadata fields */

        /* Get named node */
        $node = $this->getNodeByMidgardType($typeName);

        /* Determine multilang */
        $isMultilang = $this->isMultilang($typeName);

        /* No need to move content in case of non multilang type */
        if ($isMultilang === false) {
            return null;
        }

        /* Get table */
        $typeTable = $this->getTable($node);
        if ($typeTable === null || $typeTable === '') {
            $typeTable = $typeName;
        }

        $metadataFieldsInsert = "";
        $metadataFieldsSelect = "";
        foreach ($this->getMetadataFields() as $field) {
            $metadataFieldsInsert .= "{$field}, ";
            $metadataFieldsSelect .= "{$typeTable}.{$field}, ";
        }

        $sql = "INSERT INTO {$typeTable} \n";
        $sql .= "\t\t({$metadataFieldsInsert} guid, sitegroup, ";
       
        /* Add Sql part for every property */
        $select = "{$metadataFieldsSelect} {$typeTable}.guid, {$typeTable}.sitegroup, ";
        $props = $node->getElementsByTagName(self::ATTR_PROP);

        foreach ($props as $property) {
            $table = $this->getTable($property);
            if ($table === null || $table === '') {
                $table = $typeTable;
            }
 
            $field = $this->getField($property);
            if ($field === '' || $field === null) {
                $field = $property->getAttribute('name');
            }

            /* Ignore id field */
            if ($field === 'id') {
                continue;
            }

            $sql .= $field . ", ";
            $select .= $table . "." . $field . ", ";
        }

        /* Add workspaces fields */
        $sql .= "midgard_ws_oid_id, midgard_ws_id) \n";

        /* Select data to insert */
        $sql .= "\tSELECT {$select} {$typeTable}.id, {$workspaceID}\n";

        $table = $this->getTable($node); 

        /* Select workspace id */
        /*$sql .= "\t\t(SELECT
                    midgard_workspace.id
                FROM
                    midgard_workspace, midgard_language
                WHERE
                midgard_workspace.name = midgard_language.code AND midgard_language.id = {$table}_i.lang)";*/

        /* Add FROM and constraint */ 
        $sql .= "\n\tFROM
            {$table}, {$table}_i
            WHERE
            {$table}.sitegroup = {$sitegroupID} AND {$table}.id = {$table}_i.sid AND {$table}_i.lang = {$languageID} \n";

        return $sql;
    }

    public function getSQLUpdateTypePost($typeName, $workspaceID = 0, $sitegroupID = 0)
    {
        /* Set unique object's id in workspace */

        /* Get named node */
        $node = $this->getNodeByMidgardType($typeName);

        if ($node === null) {
            return null;
        }

        /* Get table */
        $table = $this->getTable($node);
        
        /* Check if type is multilang */
        $isMultilang = $this->isMultilang($typeName);

        if ($isMultilang === true) {
            return "UPDATE {$table} SET midgard_ws_oid_id = {$table}.id";
        }

        return "UPDATE {$table} SET midgard_ws_oid_id = {$table}.id, midgard_ws_id = (SELECT midgard_workspace.id FROM midgard_workspace, sitegroup WHERE midgard_workspace.name = sitegroup.name and sitegroup.id = {$table}.sitegroup)";
    }
}

?>
