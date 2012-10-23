<?php

require_once 'vendor/lespoilus/spyc/spyc.php';

class ResultFixture
{
    private $yaml = null;
    private $testName = null;

    const   WORKSPACE = 'workspace';
    const   PATHS = 'paths';
    const   NAMES = 'names';

    public function __construct($yamlFile, $name)
    {
        $this->testName = $name;
        $this->yaml = Spyc::YAMLLoad($yamlFile);   
    }

    private function getYamlWorkspaceKey()
    {
        return $this->yaml[$this->testName][self::WORKSPACE];
    }

    private function getYamlWorkspaceKeyByName($name)
    {
        $wsKey = $this->getYamlWorkspaceKey();
        $values = $wsKey[$name];
        if (empty($values)) {
            return array();
        }
        return $values;
    }

    public function getWorkspacePaths()
    {
         return $this->getYamlWorkspaceKeyByName(self::PATHS);
    }

    public function getWorkspaceNames()
    {
        return $this->getYamlWorkspaceKeyByName(self::NAMES);
    }
}
?>
