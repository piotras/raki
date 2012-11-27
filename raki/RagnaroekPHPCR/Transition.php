<?php

class RagnaroekPHPCRTransition implements Transition
{
    private $session = null;
    private $mgd = null;
    private $contentManager = null;
    private $workspaceManager = null;
    private $fixturePath = null;

    public function __construct($session, $mgd, $fixturePath)
    {
        $this->session = $session;
        $this->mgd = $mgd;
        $this->fixturePath = $fixturePath;
    }

    public function getPHPCRSession()
    {
        return $this->session;
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
            $this->workspaceManager = new RagnaroekPHPCRContentManager();
        }

        return $this->workspaceManager;
    }

    public function getResultFixtureDirPath()
    {
        return $this->fixturePath;
    } 
}

?>
