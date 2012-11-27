<?php

class RagnaroekPHPCRStorableWorkspace implements StorableWorkspace
{
    public function __construct()
    {

    }

    public function getProperty($name)
    {
        throw new Exception("Not implemented");
    }

    public function getName()
    {
        throw new Exception("Not implemented");
    }

    public function getPath()
    {
        throw new Exception("Not implemented");
    } 

    public function getChildrenNames()
    {
        throw new Exception("Not implemented");
    } 

    public function getChildren()
    {
        throw new Exception("Not implemented");
    }    
}

?>
