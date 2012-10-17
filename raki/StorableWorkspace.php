<?php

interface StorableWorkspace extends Storable 
{
    /**
     * Returns the names of children workspaces
     */
    public function getWorkspacesNames();

    /**
     * Returns children workspaces
     */
    public function getWorkspaces();
}

?>
