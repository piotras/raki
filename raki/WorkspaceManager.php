<?php

interface WorkspaceManager 
{

    /**
     * Returns array of all storable type names.
     */
    public function getStorableTypeNames();

    /**
     * Returns array of all possible workspaces names.
     *
     * Every key is workspace name and value is an array which holds
     * child workspaces.
     */ 
    public function getPossibleWorkspacesNames();

    /**
     * Returns array of all possible workspaces paths.
     * 
     */ 
    public function getPossibleWorkspacesPaths();

    /**
     * Create MidgardWorskpace.
     *
     * Throws an exception if workspace with given name exists.
     */ 
    public function createWorkspace($name, MidgardWorkspace $parent = null);

    /**
     * Create all possible workspaces.
     */ 
    public function createWorkspacesAll();

    /**
     * Returns array of names of all exisiting workspaces.
     */
    public function getStoredWorkspacesNames();

    /**
     * Returns array of paths of all exising workspaces.
     */
    public function getStoredWorkspacesPaths();  
}

?>
