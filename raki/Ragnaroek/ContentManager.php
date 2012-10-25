<?php

class RagnaroekContentManager implements ContentManager 
{
    public function __construct()
    {

    }

    public function getPossibleTypeNames()
    {
        return array();
    }

    public function importType($typename)
    {
        throw new Exception("Not implemented");
    }

    public function getStoredTypeNames()
    {
        return array();
    }

    public function getItemByPath(StorableWorkspace $workspace, $absPath)
    {
        return null;
    } 
}

?>
