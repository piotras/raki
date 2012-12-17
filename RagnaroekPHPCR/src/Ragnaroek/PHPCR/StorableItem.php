<?php

class RagnaroekPHPCRStorableItem implements StorableItem 
{
    private $node;

    public function __construct(\PHPCR\Node $node)
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
