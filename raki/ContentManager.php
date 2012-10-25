<?php

interface ContentManager 
{
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
     * Get the content which is accessible by given path
     */
    public function getItemByPath(StorableWorkspace $workspace, $absPath); 
}

?>
