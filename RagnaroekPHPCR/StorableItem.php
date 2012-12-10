<?php

class RagnaroekPHPCRStorableItem implements StorableItem 
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
}

?>
