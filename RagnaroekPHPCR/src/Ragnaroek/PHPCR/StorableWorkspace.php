<?php

namespace Ragnaroek\PHPCR;

class StorableWorkspace implements \CRTransition\StorableWorkspace
{
    private $name = null;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath()
    {
        return "/" . $this->getName();
    } 

    public function getChildrenNames()
    {
        return array();
    } 

    public function getChildren()
    {
        return null;
    }    
}

?>
