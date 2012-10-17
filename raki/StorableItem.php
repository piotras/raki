<?php

interface StorableItem extends Storable 
{
    /**
     * Returns the value of named property
     */
    public function getProperty($name);
}

?>
