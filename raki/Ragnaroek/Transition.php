<?php

class RagnaroekTransition implements Transition
{
    private $mgd = null;
    private $fixtureDir = null;
    private $schemaDirs = null;
    private $contentManager = null;
    private $workspaceManager = null;
    
    public function __construct(MidgardConnection $mgd, $fixtureDir, array $schemaDirs)
    {
        $this->mgd = $mgd;
        $this->fixtureDir = $fixtureDir;
        $this->schemaDirs = $schemaDirs;
    } 

    public function getSchemaDirs()
    {
        return $this->schemaDirs;
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
            $this->workspaceManager = new RagnaroekWorkspaceManager($this);
        }

        return $this->workspaceManager;
    }

    public function getResultFixtureDirPath()
    {
        return $this->fixtureDir;
    } 
}

?>  
