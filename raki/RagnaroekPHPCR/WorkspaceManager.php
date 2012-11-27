<?php

class RagnaroekPHPCRWorkspaceManager implements WorkspaceManager 
{
    private $transition = null;
    private $defaultWorkspaceName = null;

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
        throw new Exception("Not implemented");
    }

    public function getPossibleWorkspacesNames()
    {
        throw new Exception("Not implemented");
    }

    public function getPossibleWorkspacesPaths()
    {
        throw new Exception("Not implemented");
    }

    public function createWorkspace($name, StorableWorkspace $parent = null)
    {
        throw new Exception("Not implemented");
    }

    public function createWorkspacesAll()
    {
        throw new Exception("Not implemented");
    }

    public function getStoredWorkspacesNames()
    {
        throw new Exception("Not implemented");
    }

    public function getStoredWorkspacesPaths()
    {
        throw new Exception("Not implemented");
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
