<?php

class XmlMidgardObjectWriter 
{
    private $xmlDoc = null;
    private $ns = "http://www.midgard-project.org/repligard/1.4";
    private $prefix = "mgd";
    // private $ns = "http://www.jcp.org/jcr/sv/1.0";
    // private $prefix = "sv";
    private $filePath = null;
    private $xmlRootNode = null; 

    public function __construct($filePath, $typeName)
    {
        $this->xmlDoc = new DOMDocument('1.0', 'UTF-8');
        $this->xmlDoc->formatOutput = true;

        /* FIXME, this might be sitegroup or language */
        $this->xmlRootNode = self::createNodeElement();
        $this->xmlDoc->appendChild($this->xmlRootNode);
        $nodeAttr = $this->xmlDoc->createAttributeNS($this->ns, $this->prefix . ":" . 'name');
        $nodeAttr->value = $typeName;
        $this->xmlRootNode->appendChild($nodeAttr);
        $this->filePath = $filePath;
    }

    private function createNodeElement()
    {
        return $this->xmlDoc->createElementNS($this->ns, $this->prefix . ":" . 'node');
    }

    public function getRootNode()
    {
        return $this->xmlRootNode; 
    }   

    public function addTypeNode($typeName)
    {
        $xmlNode = self::createNodeElement();
        $this->xmlRootNode->appendChild($xmlNode);
        $nodeAttr = $this->xmlDoc->createAttributeNS($this->ns, $this->prefix . ":" . 'name');
        $nodeAttr->value = $typeName;
        $xmlNode->appendChild($nodeAttr);

        return $xmlNode;
    }

    private function createPropertyNode($name, $type = 'String')
    {
        $pNode = $this->xmlDoc->createElementNS($this->ns, $this->prefix . ":" . 'property');

        /* Add name attribute */
        $nodeAttr = $this->xmlDoc->createAttributeNS($this->ns, $this->prefix . ":" . 'name');
        $nodeAttr->value = $name;
        $pNode->appendChild($nodeAttr);

        /* Add type attribute */
        $nodeAttr = $this->xmlDoc->createAttributeNS($this->ns, $this->prefix . ":" . 'type');
        $nodeAttr->value = $type;
        $pNode->appendChild($nodeAttr);

        return $pNode;
    }

    private function addValue($xmlNode, $value)
    {
        $pValue = $this->xmlDoc->createElementNS($this->ns, $this->prefix . ":" . 'value', htmlentities($value));
        $xmlNode->appendChild($pValue);
    }

    private function getPHPCRPropertyType($reflector, $property)
    {
        switch($reflector->get_midgard_type($property)) 
        {
            case MGD_TYPE_STRING:
            case MGD_TYPE_LONGTEXT:
                return "String";

            case MGD_TYPE_GUID:
                return "Reference";

            case MGD_TYPE_UINT:
            case MGD_TYPE_INT:
                return "Long";

            case MGD_TYPE_TIMESTAMP:
                return "Date";

            case MGD_TYPE_FLOAT:
                return "Double";

            case MGD_TYPE_BOOLEAN:
                return "Boolean";

            default:
                return "String";
        }
    }

    private function serializeProperties($xmlNode, $object)
    {
        /* Add jcr:primaryType */
        $pType = $this->createPropertyNode("jcr:primaryType", "Name");
        $this->addValue($pType, "nt:unstructured");
        $xmlNode->appendChild($pType);

        $reflector = new MidgardReflectorProperty(get_class($object));

        foreach ($object as $property => $value) {
            $pNode = $this->createPropertyNode($property, $this->getPHPCRPropertyType($reflector, $property));
            if (is_object($value)) {
                continue;
            } 
            $this->addValue($pNode, $value);
            $xmlNode->appendChild($pNode);
        } 

        if (midgard_reflector_object::has_metadata_class(get_class($object)) === false) {
            return;
        }

        $metadata = $object->metadata;

        /* Add mix:created properties */
        $pType = $this->createPropertyNode("jcr:created", "Date");
        $this->addValue($pType, $metadata->created->format("c"));
        $xmlNode->appendChild($pType);

        /* FIXME, get person firstname and lastname */
        $pType = $this->createPropertyNode("jcr:createdBy");
        $this->addValue($pType, $metadata->creator);
        $xmlNode->appendChild($pType);

        /* Add mix:lastModified properties */
        $pType = $this->createPropertyNode("jcr:lastModified", "Date");
        $this->addValue($pType, $metadata->revised->format("c"));
        $xmlNode->appendChild($pType);

        /* FIXME, get person firstname and lastname */
        $pType = $this->createPropertyNode("jcr:lastModifiedBy");
        $this->addValue($pType, $metadata->revisor);
        $xmlNode->appendChild($pType);
    }

    public function serializeObject($object)
    {
        $objectNode = self::createNodeElement();
        $nodeAttr = $this->xmlDoc->createAttributeNS($this->ns, $this->prefix . ":" . 'name');
        $uniqueProperty = MidgardReflectorObject::get_property_unique(get_class($object));
        if ($uniqueProperty) {
            $nodeAttr->value = $object->$uniqueProperty;
        } else {
            $nodeAttr->value = $object->guid;
        } 
        $objectNode->appendChild($nodeAttr);

        $this->serializeProperties($objectNode, $object);
  
        return $objectNode;
    }

    public function getFilePath()
    {
        if ($this->filePath == null) {
            $this->filePath = tempnam("/tmp", "mgd_phpcr_");
        }

        return $this->filePath;
    }

    public function save()
    {
        //echo $this->xmlDoc->saveXML();
        $this->xmlDoc->save($this->getFilePath());
    } 
}

?>

