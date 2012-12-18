<?php

namespace Ragnaroek\PHPCR;

class StorableItem implements \CRTransition\StorableItem 
{
    private $node;

    public function __construct($node)
    {
        $this->node = $node;
    }

    public function getProperty($name)
    {
        return $this->node->getProperty($name); 
    }

    public function getName()
    {
        return $this->node->getName();
    }

    public function getPath()
    {
        return $this->node->getPath();
    }  
}

?>
