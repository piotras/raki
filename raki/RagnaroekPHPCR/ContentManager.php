<?php

class RagnaroekPHPCRContentManager implements ContentManager 
{
    private $transition = null;

    public function __construct($transition)
    {
        $this->transition = $transition;
    }

    public function getTransition()
    {
        return $this->transition;
    }

    public function getPossibleTypeNames()
    {
        throw new Exception("Not Implemented");
    }

    public function importType($typename)
    {
        throw new Exception("Not Implemented");
    }

    public function getStoredTypeNames()
    {
        throw new Exception("Not Implemented");
    }

    public function getItemByPath(StorableWorkspace $workspace, $typeName, $relPath)
    {
        throw new Exception("Not Implemented");
    } 
}

?>
