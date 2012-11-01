<?php

interface Transition
{
    
    /**
     * Get content manager
     */
    public function getContentManager();

    /**
     * Get workspace manager 
     */
    public function getWorkspaceManager();

    /**
     * Get result fixture directory path
     */
    public function getResultFixtureDirPath();   
}

?>
