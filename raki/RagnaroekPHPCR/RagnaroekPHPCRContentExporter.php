<?php

class RagnaroekPHPCRContentExporter 
{
    private $exportDir = null;
    private $sitegroups = null;

    public function __construct($exportDir = "/tmp/Midgard-Ragnaroek-PHPCR-Export")
    {
        $this->exportDir = $exportDir;
    }

    public function getStorableTypeNames()
    {
        $re = new ReflectionExtension("midgard2");
        $names = array();
        foreach ($re->getClasses() as $class_ref) {
            $class_mgd_ref = new midgard_reflection_class($class_ref->getName());
            $name = $class_mgd_ref->getName();
            /* Ignore non MidgardDBObject derived types */
            if (!is_subclass_of ($name, 'MidgardDBObject')) {
                continue;
            }
            /* Ignore interfaces, abstract and mixin types */
            if (MidgardReflectorObject::is_abstract($name)
                || MidgardReflectorObject::is_mixin($name)
                || MidgardReflectorObject::is_interface($name)) {
                    continue;
                }

            $names[] = $name;
        }
        return $names;
    }

    public function getSitegroups()
    {
        if (!empty($this->sitegroups)) {
            return $this->sitegroups;
        }

        $this->sitegroups = array();

        $storage = new MidgardQueryStorage("ragnaroek_sitegroup");
        $qs = new MidgardQuerySelect($storage);
        $qs->execute();

        if ($qs->get_results_count() > 1) {
            foreach ($qs->list_objects() as $sg) {
                $this->sitegroups[] = $sg;
            }
        }

        return $this->sitegroups;
    }

    public function exportSitegroup($sitegroup)
    {
        mkdir($this->exportDir . "/" . $sitegroup->name, 0700, true);
    }

    private function getObjects($typeName, $sitegroupID, $upProperty = null, $upValue = 0)
    {
        $qs = new MidgardQuerySelect(
            new MidgardQueryStorage($typeName)
        );

        /* Add sitegroup constraint */
        $cnstrGrp = new MidgardQueryConstraint(
            new MidgardQueryProperty("sitegroup"),
            "=",
            new MidgardQueryValue($sitegroupID)
        );

        if ($upProperty != null) {
            $cnstr = new MidgardQueryConstraint(
                new MidgardQueryProperty($upProperty),
                "=",
                new MidgardQueryValue($upValue)
            );

            $cnstrSG = $cnstrGrp;
            $cnstrGrp = new MidgardQueryConstraintGroup("AND");
            $cnstrGrp->add_constraint($cnstr);
            $cnstrGrp->add_constraint($cnstrSG);
        }

        $qs->set_constraint($cnstrGrp);

        $qs->execute();
        return $qs->list_objects();
    }

    private function serializeObjects($sitegroupID, $xmlWriter, $typeName, $upProperty, $upValue, $xmlParentNode)
    {
        $parentProperty = MidgardReflectorObject::get_property_parent($typeName);
        $upPropertyLocal = MidgardReflectorObject::get_property_up($typeName);
        $childrenTypes = MidgardReflectorObject::list_children($typeName);

        /* Get all objects or root ones in case of tree */
        $objects = $this->getObjects($typeName, $sitegroupID, $upProperty, $upValue);
        if (count($objects) == 0) return;
        foreach ($objects as $object) {
            /* Create xml node from object */
            $xmlNode = $xmlWriter->serializeObject($object);
            /* Append node to document */
            $xmlParentNode->appendChild($xmlNode);

            /* Serialize possible children objects of the same type */
            if ($upProperty != null) {
                $this->serializeObjects($sitegroupID, $xmlWriter, $typeName, $upPropertyLocal, $object->id, $xmlNode);
            }

            /* Serialize possible children objects of different type */
            if (!empty($childrenTypes)) {
                foreach ($childrenTypes as $childType => $v) {
                    $childParentProperty = MidgardReflectorObject::get_property_parent($childType);
                    $this->serializeObjects($sitegroupID, $xmlWriter, $childType, $childParentProperty, $object->id, $xmlNode);
                }
            }
        }
    }

    public function exportType($sitegroup, $typeName)
    {
        $sgDir = $this->exportDir . "/" . $sitegroup->name;
        if (!is_dir($sgDir)) {
            $this->exportSitegroup($sitegroup);
        }

        /* Determine if type can be parent or child in a tree.
         * If parent property is defined, we have to create subgraph 
         * while importing parent type */
        $parentProperty = MidgardReflectorObject::get_property_parent($typeName);
        if ($parentProperty != null) {
            return;
        }

        $upProperty = MidgardReflectorObject::get_property_up($typeName);

        $obj = new $typeName;
        if (!property_exists($obj, "sitegroup")) {
            echo "Ignoring {$typeName} due to missed sitegroup property \n";
            return;
        }

        $typeDir = $sgDir . "/" . $typeName;
        $xmlWriter = new XmlMidgardObjectWriter($typeDir, $typeName);
        $typeNode = $xmlWriter->getRootNode();
        $this->serializeObjects($sitegroup->id, $xmlWriter, $typeName, $upProperty, 0, $typeNode);

        /* Dump xml to a file */
        $xmlWriter->save();
    }
}

?>
