<?php

class RagnaroekTransition implements Transition
{
    private $mgd = null;
    private $fixtureDir = null;
    private $contentManager = null;
    private $workspaceManager = null;
    
    public function __construct(MidgardConnection $mgd, $fixtureDir)
    {
        $this->mgd = $mgd;
        $this->fixtureDir = $fixtureDir;
    } 

    public function getContentManager()
    {
        if ($this->contentManager == null) {
            $this->contentManager = new RagnaroekContentManager($this);
        }

        return $this->contentManager;
    }

    public function getWorkspaceManager()
    {
        if ($this->workspaceManager == null) {
            $this->workspaceManager == new RagnaroekWorkspaceManager($this);
        }

        return $this->workspaceManager;
    }

    public function getResultFixtureDirPath()
    {
        return $this->fixtureDir;
    } 
}

?>  
