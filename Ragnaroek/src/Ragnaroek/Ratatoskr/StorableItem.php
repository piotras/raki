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
        if (property_exists($this->midgardObject, "name")) {
            return $this->midgardObject->name;
        }
        if (property_exists($this->midgardObject, "title")) {
            return $this->midgardObject->title;
        }
        return $this->midgardObject->guid;
    }

    public function getProperty($property)
    {
        return $this->midgardObject->$property;
    }   
}

?>
