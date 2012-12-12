<?php

namespace Ragnaroek\PHPCR;

use \MidgardReflectorObject;
use \MidgardQueryStorage;
use \MidgardQuerySelect;

class WorkspaceManager implements \CRTransition\WorkspaceManager 
{
    private $transition = null;
    private $defaultWorkspaceName = null;
    private $possibleWorkspaces = null;
    private $workspaces = null;

    public function __construct($transition, $workspaceName = "Root")
    {
        $this->transition = $transition;
        $this->defaultWorkspaceName = $workspaceName;
    }
    
    public function getTransition()
    {
        return $this->transition;
    } 

    public function getDefaultWorkspaceName()
    {
        return $this->defaultWorkspaceName;
    }

    public function getStorableTypeNames()
    {
        $re = new ReflectionExtension("midgard2");
        $names = array();
        foreach ($re->getClasses() as $class_ref) {
            $class_mgd_ref = new \midgard_reflection_class($class_ref->getName());
            $name = $class_mgd_ref->getName();
            /* Ignore non MidgardDBObject derived types */
            if (!is_subclass_of ($name, 'MidgardDBObject')) {
                continue;
            }
            /* Ignore interfaces, abstract and mixin types */
            if (MidgardObjectReflector::is_abstract($name) 
                || MidgardObjectReflector::is_mixin($name)
                || MidgardObjectReflector::is_interface($name)) {
                    continue;
                }

            $names[] = $name;
        }
        return $names;
    }

    public function getPossibleWorkspacesNames()
    {
        if (!empty($this->possibleWorkspaces)) {
            return $this->possibleWorkspaces;
        }

        $d = dir($this->getTransition()->getExportDir());
        while (($entry = $d->read()) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $this->possibleWorkspaces[] = $entry;
        }
        $d->close();

        return $this->possibleWorkspaces;
    }

    public function getPossibleWorkspacesPaths()
    {
        $names = $this->getPossibleWorkspacesNames();
        $paths = array();
        foreach ($names as $name) {
            $paths[] = "/" . $name;
        }

        return $paths;
    }

    public function createWorkspace($name, \CRTransition\StorableWorkspace $parent = null)
    {
        $phpcrSession = $this->getTransition()->getDefaultPHPCRSession();
        $phpcrSession->getWorkspace()->createWorkspace($name);
    }

    public function createWorkspacesAll()
    {
        $names = $this->getPossibleWorkspacesNames();
        foreach ($names as $name) {
            $this->createWorkspace($name);
        }
    }

    public function getStoredWorkspacesNames()
    {
        return $this->getTransition()->getDefaultPHPCRSession->getWorkspace()->getAccessibleWorkspaceNames();
    }

    public function getStoredWorkspacesPaths()
    {
        $names = $this->getStoredWorkspacesNames();
        $paths = array();
        if (!empty($names)) {
            foreach ($names as $name) {
                $paths[] = "/" . $name;
            }
        }
        return $paths;      
    }

    public function getStoredWorkspaceByPath($absPath)
    {
        throw new \Exception("Not implemented");
    }

    public function getStoredWorkspaceByName($name)
    {
        throw new \Exception("Not implemented");
    }

    public function storedWorkspacePathExists($absPath)
    {
        throw new \Exception("Not implemented");
    }
}
?>
