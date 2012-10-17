<?php

interface Storable 
{
    /**
     * Returns the name of the workspace
     */
    public function getName();


    /**
     * Returns the path of the workspace 
     */
    public function getPath();
}

?>
