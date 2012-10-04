<?php

interface WorkspaceManager 
{

    /**
     * Returns array of all storable type names.
     */
    public function getStorableTypeNames();

    /**
     * Returns array of possible workspaces names.
     *
     * Every key is workspace name and value is an array which holds
     * child workspaces.
     */ 
    public function getPossibleWorkspacesNames($parent = null);

    /**
     * Returns array of all possible workspaces paths.
     * 
     */ 
    public function dumpWorkspaces();

    /**
     * Create MidgardWorskpace.
     *
     * No exception should be thrown if workspace with given name and 
     * under given parent workspace already exists.
     */ 
    public function createWorkspace($name, $parent = null);
}

?>
