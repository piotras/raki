<?php

namespace Ragnaroek\PHPCR;

use \MidgardReflectorProperty;
use \MidgardReflectorObject;
use \MidgardQueryStorage;
use \MidgardQuerySelect;
use \MidgardQueryConstraint;
use \MidgardQueryProperty;
use \MidgardQueryValue;

class ContentManager implements \CRTransition\ContentManager 
{
    private $transition = null;
    private $xmlWriter = null;

    public function __construct($transition = null)
    {
        $this->transition = $transition;
    }

    public function getXmlWriter()
    {
        if ($this->xmlWriter === null) {
            $this->xmlWriter = new XmlMidgardObjectWriter();
        }

        return $this->xmlWriter;
    }

    public function getTransition()
    {
        return $this->transition;
    }

    public function getPossibleTypeNames()
    {
        $re = new \ReflectionExtension("midgard2");
        $names = array();
        foreach ($re->getClasses() as $class_ref) {

            if ($class_ref->isAbstract() || $class_ref->isInterface()) {
                continue;
            }

            $name = $class_ref->getName();
            if (!is_subclass_of ($name, 'MidgardDBObject')) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    private function getObjects($typename, $upProperty = null, $upValue = 0)
    {
        $qs = new MidgardQuerySelect(
            new MidgardQueryStorage($typename)
        );

        if ($upProperty != null) {
            $cnstr = new MidgardQueryConstraint(
                new MidgardQueryProperty($upProperty),
                "=", 
                new MidgardQueryValue($upValue)
            );
            $qs->set_constraint($cnstr);
        }

        $qs->execute();
        return $qs->list_objects();
    }

    private function serializeObjects($typeName, $upProperty, $upValue, $xmlParentNode)
    {
        $parentProperty = MidgardReflectorObject::get_property_parent($typeName);
        $childrenTypes = MidgardReflectorObject::list_children($typeName);

        /* Get all objects or root ones in case of tree */
        $objects = $this->getObjects($typeName, $upProperty, $upValue);
        foreach ($objects as $object) {
            /* Create xml node from object */
            $xmlNode = $this->getXmlWriter()->serializeObject($object);
            /* Append node to document */
            $xmlParentNode->appendChild($xmlNode);

            /* Serialize possible children objects of the same type */
            if ($upProperty != null) {
                $this->serializeObjects($typeName, $upProperty, $object->id, $xmlNode);
            }

            /* Serialize possible children objects of different type */
            if (!empty($childrenTypes)) {
                foreach ($childrenTypes as $childType => $v) {
                    $childParentProperty = MidgardReflectorObject::get_property_parent($childType);
                    $this->serializeObjects($childType, $childParentProperty, $object->id, $xmlNode);
                } 
            }
        }      
    }

    public function importType($typeName)
    {
        /* Determine if type can be parent or child in a tree.
         * If parent property is defined, we have to create subgraph 
         * while importing parent type */
        $parentProperty = MidgardReflectorObject::get_property_parent($typeName);
        if ($parentProperty != null) {
            return;
        }

        $upProperty = MidgardReflectorObject::get_property_up($typeName);

        $typeNode = $this->getXmlWriter()->addTypeNode($typeName);
        $this->serializeObjects($typeName, $upProperty, 0, $typeNode);

        /* Dump xml to a file */
        $this->getXmlWriter()->save();

        /* Import document using currrent session */
        /* TODO, create new session if data is imported per sitegroup */
        $this->getTransition()->getDefaultPHPCRSession()->importXML("/", $this->getXmlWriter()->getFilePath());
    }

    public function getStoredTypeNames()
    {
        throw new Exception("Not Implemented");
    }

    public function getItemByPath(\CRTransition\StorableWorkspace $workspace, $typeName, $relPath)
    {
        throw new Exception("Not Implemented");
    } 
}

?>
