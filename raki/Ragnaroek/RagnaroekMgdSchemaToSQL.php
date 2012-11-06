<?php

class RagnaroekMgdSchemaToSQL extends DomDocument
{
    protected $filePath;

    const ATTR_NAME = 'name';
    const ATTR_TYPE = 'type';
    const ATTR_PROP = 'property';

    public function __construct($filePath)
    {
        parent::__construct('1.0', 'UTF-8');
        $this->load($filePath);
        $this->filepath = $filePath;
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
        $nodes = $this->documentElement->getElementsByTagName(self::ATTR_TYPE);
        foreach ($nodes as $node) {
            if ($node->getAttribute(self::ATTR_NAME) == $type) {
                return $node;
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
            return '';
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
            $sql .= "(SELECT  {$table}.{$field} FROM {$table} WHERE {$table}.lang = {$languageID} AND {$table}.sid = {$typeTable}.id)";
            $sql .= ",\n";
        }

        /* Also, set workspace id */
        $sql .= "midgard_ws_id = {$workspaceID} \n";

        /* Set constraint */
        $sql .= "WHERE {$typeTable}.lang = {$languageID} AND {$typeTable}.sitegroup = {$sitegroupID} \n";

        return $sql;
    }

    public function getSQLDeleteTypePre($typename, $languageID = 0)
    {
        /* Get named node */
        $node = $this->getNodeByMidgardType($typeName);

        /* Determine multilang */
        $isMultilang = $this->isMultilang($typeName);

        /* No need to move content in case of non multilang type */
        if ($isMultilang === false) {
            return '';
        }

        /* Get table */
        $typeTable = $this->getTable($node);
        if ($typeTable === null || $typeTable === '') {
            $typeTable = $typeName;
        }

        $sql = "DELETE FROM {$typeTable} WHERE lang = {$languageID} \n";
    }

    public function getSQLInsertType($typename, $workspace, $language)
    {

    }

    public function getSQLUpdateTypePost($typename, $workspace, $language)
    {

    }
}

?>
