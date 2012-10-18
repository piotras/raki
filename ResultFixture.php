<?php

require_once 'vendor/lespoilus/spyc/spyc.php';


class ResultFixture
{
    private $yamlDoc = null;

    public function __construct($yamlFile)
    {
        $yamlDoc = Spyc::YAMLLoad($yamlFile);   
    }

    public function getWorkspacesPaths()
    {

    }

    public function getWorkspacesNames()
    {

    }
}
?>
