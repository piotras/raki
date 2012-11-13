<?php

interface WorkspaceManager 
{
    
    /**
     * Get transition associated with this manager.
     */
    public function getTransition();  

    /**
     * Get the name of default workspace
     */
    public function getDefaultWorkspaceName(); 

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
    public function createWorkspace($name, StorableWorkspace $parent = null);

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

    /**
     * Get stored workspace by given path
     *
     * @throws WorkspaceNotFoundException if workspace doesn't exists at given path
     */
    public function getStoredWorkspaceByPath($absPath); 

    /**
     * Get stored workspace by given name
     *
     * @throws WorkspaceNotFoundException if workspace identified by name is not found
     */
    public function getStoredWorkspaceByName($name); 

    /**
     * Test whether workspace is stored at given path.
     */
    public function storedWorkspacePathExists($absPath);
}

?>
