<?php

interface ContentManager 
{
    /**
     * Get transition associated with this manager.
     */ 
    public function getTransition();

    /**
     * Returns array of all possible type names.
     */
    public function getPossibleTypeNames();

    /**
     * Import content of a given type
     */
    public function importType($typename);

    /**
     * Returns array of names of all exisiting types.
     */
    public function getStoredTypeNames();

    /**
     * Get the content which is accessible by given path.
     *
     * The path given as argument should be relative to given workspace's one.
     */
    public function getItemByPath(StorableWorkspace $workspace, $typeName, $relPath); 
}

?>
