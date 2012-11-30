<?php

class RagnaroekPHPCRTransition implements Transition
{
    private $defaultSession = null;
    private $mgd = null;
    private $contentManager = null;
    private $workspaceManager = null;
    private $fixturePath = null;

    public function __construct($defaultSession, $mgd, $fixturePath)
    {
        $this->defaultSession = $defaultSession;
        $this->mgd = $mgd;
        $this->fixturePath = $fixturePath;
    }

    public function getDefaultPHPCRSession()
    {
        return $this->defaultSession;
    }

    public function getMidgardConnection()
    {
        return $this->mgd;
    }

    public function getContentManager()
    {
        if ($this->contentManager == null) {
            $this->contentManager = new RagnaroekPHPCRContentManager();
        } 

        return $this->contentManager;
    }

    public function getWorkspaceManager()
    {
        if ($this->workspaceManager == null) {
            $this->workspaceManager = new RagnaroekPHPCRWorkspaceManager($this);
        }

        return $this->workspaceManager;
    }

    public function getResultFixtureDirPath()
    {
        return $this->fixturePath;
    } 
}

?>
