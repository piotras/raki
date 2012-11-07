<?php

class RagnaroekTransition implements Transition
{
    private $mgd = null;
    private $fixtureDir = null;
    private $schemaDir = null;
    private $contentManager = null;
    private $workspaceManager = null;
    
    public function __construct(MidgardConnection $mgd, $fixtureDir, $schemaDir)
    {
        $this->mgd = $mgd;
        $this->fixtureDir = $fixtureDir;
        $this->schemaDir = $schemaDir;
    } 

    private function getFilePaths($dir, &$paths)
    {
        if ($handle = opendir($dir)) {
            while (($entry = readdir($handle)) == true) {
                /* Ignore parent and self directory */
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $absPath = $dir . '/' . $entry;
                /* Get files from sub directory */
                if (is_dir($absPath)) {
                    $this->getFilePaths($absPath, $paths);
                    continue;
                }
                $info = pathinfo($absPath);
                /* Ignore non xml files */
                if ($info['extension'] != 'xml') {
                    continue;
                }
                $paths[] = $absPath;
            }
        } 
    }

    public function getSchemaPaths()
    {
        $paths = array();
        $this->getFilePaths($this->schemaDir, $paths);
        return $paths;
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
