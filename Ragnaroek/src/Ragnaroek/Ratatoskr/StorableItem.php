<?php

namespace Ragnaroek\Ratatoskr;

class StorableItem implements \CRTransition\StorableItem
{
    private $midgardObject = null;

    public function __construct(\MidgardObject $contentObject)
    {
        $this->midgardObject = $contentObject;
    }

    public function getName()
    {
        return $this->midgardObject->name;
    }

    public function getPath()
    {
        return $this->midgardObject->name;
    }

    public function getProperty($property)
    {
        return $this->midgardObject->$property;
    }   
}

?>
