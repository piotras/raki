<?php

interface StorableWorkspace extends Storable 
{
    /**
     * Returns array with the names of children workspaces
     */
    public function getChildrenNames();

    /**
     * Returns array of children workspaces
     */
    public function getChildren();
}

?>
