<?php

namespace Ragnaroek\PHPCR;

use \MidgardReflectorProperty;
use \MidgardReflectorObject;

class XmlMidgardObjectWriter 
{
    private $mgdNS = "http://www.midgard-project.org/repligard/1.4";
    private $mgdPrefix = "mgd";
    private $ns = "http://www.jcp.org/jcr/sv/1.0";
    private $prefix = "sv";
    private $filePath = null;
    private $xmlWriter = null;

    public function __construct($filePath, $typeName)
    {
        $this->filePath = $filePath;

        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openURI($this->filePath);
        $this->xmlWriter->startDocument("1.0", "UTF-8");

        $this->xmlWriter->setIndent(4);

        $this->xmlWriter->startElement($this->prefix . ':node');
        $this->xmlWriter->writeAttribute('xmlns:' . $this->prefix, $this->ns);
        $this->xmlWriter->writeAttribute('xmlns:' . $this->mgdPrefix, $this->mgdNS);
        $this->xmlWriter->writeAttribute($this->prefix . ':name', $typeName);

        /* Add jcr:primaryType to the main node*/
        $this->createPropertyNode("jcr:primaryType", "Name", "nt:unstructured");
    }

    private function createPropertyNode($name, $type = 'String', $value)
    {
        $this->xmlWriter->startElement($this->prefix . ":property");
        $this->xmlWriter->writeAttribute($this->prefix . ":name", $name);
        $this->xmlWriter->writeAttribute($this->prefix . ":type", $type);
        $this->xmlWriter->text($value);
        $this->xmlWriter->endElement(); /* property */
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

    private function serializeProperties($object)
    {
        /* Add jcr:primaryType */
        $this->createPropertyNode("jcr:primaryType", "Name", "nt:unstructured");

        $reflector = new MidgardReflectorProperty(get_class($object));
        foreach ($object as $property => $value) {
            if (is_object($value)) {
                continue;
            } 
            $this->createPropertyNode($property, $this->getPHPCRPropertyType($reflector, $property), $value);
        } 

        if (MidgardReflectorObject::has_metadata_class(get_class($object)) === false) {
            return;
        }

        $metadata = $object->metadata;

        /* Add mix:created properties */
        $pType = $this->createPropertyNode("jcr:created", "Date", $metadata->created->format("c"));

        /* FIXME, get person firstname and lastname */
        $pType = $this->createPropertyNode("jcr:createdBy", "String", $metadata->creator);

        /* Add mix:lastModified properties */
        $pType = $this->createPropertyNode("jcr:lastModified", "Date", $metadata->revised->format("c"));

        /* FIXME, get person firstname and lastname */
        $pType = $this->createPropertyNode("jcr:lastModifiedBy", "String", $metadata->revisor);
    }

    public function serializeObject($object)
    {
        $uniqueProperty = \midgard_reflector_object::get_property_unique(get_class($object));
        $name = "";
        if ($uniqueProperty) {
            $name = $object->$uniqueProperty;
        } else {
            if (property_exists($object, "name")) {
                $name = $object->name;
            } else {
                $name = $object->guid;
            }
        } 

        $this->xmlWriter->startElement($this->prefix . ":node" );
        $this->xmlWriter->writeAttribute($this->prefix . ":name", $name);

        $this->serializeProperties($object); 
    }

    public function endElement()
    {
        $this->xmlWriter->endElement();
    }

    public function save()
    {
        $this->xmlWriter->endElement(); /* First node created in constructor */
        $this->xmlWriter->endDocument();
        $this->xmlWriter->flush();
    } 
}

?>

