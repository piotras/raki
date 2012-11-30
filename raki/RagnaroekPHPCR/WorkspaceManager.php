<?php

class RagnaroekPHPCRWorkspaceManager implements WorkspaceManager 
{
    private $transition = null;
    private $defaultWorkspaceName = null;
    private $sitegroups = array();
    private $possibleWorkspaces = null;

    public function __construct($transition, $workspaceName = "")
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
            $class_mgd_ref = new midgard_reflection_class($class_ref->getName());
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

    private function populateSitegroups()
    {
        if (!empty($this->sitegroups)) {
            return $this->sitegroups;
        }

        $storage = new MidgardQueryStorage("ragnaroek_sitegroup");
        $qs = new MidgardQuerySelect($storage);
        $qs->execute();

        if ($qs->get_results_count() < 1) {
            return;
        }

        foreach ($qs->list_objects() as $sg) {
            $this->sitegroups[] = $sg;
        }
    }

    public function getMidgardSitegroups()
    {
        $this->populateSitegroups();
        return $this->sitegroups;
    }

    public function getPossibleWorkspacesNames()
    {
        $this->populateSitegroups();
        $names = array();

        foreach ($this->sitegroups as $sg) {
            $names[] = $sg->name;
        }    

        return $names;
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

    public function createWorkspace($name, StorableWorkspace $parent = null)
    {
        $phpcrSession = $this->getTransition()->getSession();
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
        return $this->getTransition()->getSession->getWorkspace()->getAccessibleWorkspaceNames();
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
        throw new Exception("Not implemented");
    }

    public function getStoredWorkspaceByName($name)
    {
        throw new Exception("Not implemented");
    }

    public function storedWorkspacePathExists($absPath)
    {
        throw new Exception("Not implemented");
    }
}
?>
