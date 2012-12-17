<?php

namespace Ragnaroek\PHPCR;

use \MidgardReflectorObject;
use \MidgardQueryStorage;
use \MidgardQuerySelect;
use \MidgardQueryValue;
use \MidgardQueryProperty;
use \MidgardQueryConstraint;
use \MidgardQueryConstraintGroup;

class ContentExporter 
{
    private $exportDir = null;
    private $sitegroups = null;
    private $defaultSitegroup = null;
    private $mgd = null;

    public function __construct($exportDir = "/tmp/Midgard-Ragnaroek-PHPCR-Export", $defaultSitegroup = "SG0")
    {
        $this->exportDir = $exportDir;
        $this->defaultSitegroup = $defaultSitegroup;
        $this->mgd = \MidgardConnection::get_instance();
    }

    public function getStorableTypeNames()
    {
        $re = new \ReflectionExtension("midgard2");
        $names = array();
        foreach ($re->getClasses() as $class_ref) {
            $class_mgd_ref = new \midgard_reflection_class($class_ref->getName());
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

    private function getMultilangWorkspaces($targetName, $ws, &$names) 
    {
        $children = $ws->list_children();

        $path = substr(strstr($ws->path, $targetName), strlen($targetName)+1);
        $path = str_replace("/", "-", $path);
        if ($path != "") {
            $names[$path] = $ws; 
        }

        if (empty($children)) {
            return;
        }

        foreach ($children as $child) {
            $this->getMultilangWorkspaces($targetName, $child, $names);
        }
    }

    public function getWorkspaces()
    {
        $ws = new \MidgardWorkspace();
        $manager = new \MidgardWorkspaceManager($this->mgd);
        $manager->get_workspace_by_path($ws, "/" . $this->defaultSitegroup);

        return $ws->list_children();
    }

    public function exportWorkspace($workspace)
    {
        mkdir($this->exportDir . "/" . $workspace->name, 0700, true);
    }

    private function getObjects($typeName, $upProperty = null, $upValue = 0)
    {
        $qs = new MidgardQuerySelect(
            new MidgardQueryStorage($typeName)
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

    private function serializeObjects($workspace, $safeWsName, $xmlWriter, $typeName, $upProperty, $upValue)
    {
        $this->mgd->enable_workspace(true);   
        $this->mgd->set_workspace($workspace);

        $parentProperty = MidgardReflectorObject::get_property_parent($typeName);
        $upPropertyLocal = MidgardReflectorObject::get_property_up($typeName);
        $childrenTypes = MidgardReflectorObject::list_children($typeName);

        /* Get all objects or root ones in case of tree */
        $objects = $this->getObjects($typeName, $upProperty, $upValue);
        if (count($objects) == 0) return;
        foreach ($objects as $object) {
            /* Create xml node from object */
            $xmlWriter->serializeObject($object, $safeWsName);

            /* Serialize possible children objects of the same type */
            if ($upProperty != null) {
                $this->serializeObjects($workspace, $safeWsName, $xmlWriter, $typeName, $upPropertyLocal, $object->id);
            }

            /* Serialize possible children objects of different type */
            if (!empty($childrenTypes)) {
                foreach ($childrenTypes as $childType => $v) {
                    $childParentProperty = MidgardReflectorObject::get_property_parent($childType);
                    $this->serializeObjects($workspace, $safeWsName, $xmlWriter, $childType, $childParentProperty, $object->id);
                }
            }

            $xmlWriter->endElement();
        }

        $workspaces = array();
        $this->getMultilangWorkspaces($workspace->name, $workspace, $workspaces);
        foreach ($workspaces as $path => $ws) {
            /* pass wsA-wsB-wsC path as safe workspace name */ 
            $this->serializeObjects($ws, $path, $xmlWriter, $typeName, $upProperty, $upValue); 
        }
    }

    public function exportType($workspace, $typeName)
    {
        $sgDir = $this->exportDir . "/" . $workspace->name;
        if (!is_dir($sgDir)) {
            $this->exportWorkspace($workspace);
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
        $this->serializeObjects($workspace, "", $xmlWriter, $typeName, $upProperty, 0);

        /* Dump xml to a file */
        $xmlWriter->save();
    }
}

?>
