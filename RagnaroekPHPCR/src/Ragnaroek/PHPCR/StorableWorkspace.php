<?php

namespace Ragnaroek\PHPCR;

class StorableWorkspace implements \CRTransition\StorableWorkspace
{
    public function __construct()
    {

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
