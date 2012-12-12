<?php

namespace Ragnaroek\PHPCR;

class Transition implements \CRTransition\Transition
{
    private $mgd = null;
    private $contentManager = null;
    private $workspaceManager = null;
    private $fixturePath = null;
    private $phpcrRepositoryFactory = null;
    private $phpcrRepository = null;
    private $phpcrConfigurationKeys = null;
    private $defaultPHPCRSession = null;

    public function __construct($phpcrRepositoryFactory, $phpcrConfigurationKeys, $mgd, $fixturePath)
    {
        $this->phpcrRepositoryFactory = $phpcrRepositoryFactory;
        $this->phpcrConfigurationKeys = $phpcrConfigurationKeys;
        $this->mgd = $mgd;
        $this->fixturePath = $fixturePath;
    }

    public function getPHPCRRepository()
    {
        if ($this->phpcrRepository == null) {
            $factory = $this->phpcrRepositoryFactory;
            $this->phpcrRepository = $factory::getRepository($this->phpcrConfigurationKeys);
        }

        return $this->phpcrRepository;
    }

    public function getDefaultPHPCRSession()
    {
        if ($this->defaultPHPCRSession == null) {
            $this->defaultPHPCRSession = $this->getPHPCRRepository()->login(null, "default");
        }
        return $this->defaultPHPCRSession;
    }

    public function getMidgardConnection()
    {
        return $this->mgd;
    }

    public function getContentManager()
    {
        if ($this->contentManager == null) {
            $this->contentManager = new \Ragnaroek\PHPCR\ContentManager();
        } 

        return $this->contentManager;
    }

    public function getWorkspaceManager()
    {
        if ($this->workspaceManager == null) {
            $this->workspaceManager = new \Ragnaroek\PHPCR\WorkspaceManager($this);
        }

        return $this->workspaceManager;
    }

    public function getResultFixtureDirPath()
    {
        return $this->fixturePath;
    } 
}

?>
