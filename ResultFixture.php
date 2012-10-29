<?php

require_once 'vendor/lespoilus/spyc/spyc.php';

class ResultFixture
{
    private $yaml = null;
    private $testName = null;

    const   WORKSPACE = 'workspace';
    const   PATHS = 'paths';
    const   NAMES = 'names';
    const   CHILDREN = 'children';
    const   TYPES = 'types';
    const   POSSIBLE = 'possible';
    const   STORED = 'stored';
    const   ITEMS = 'items';

    public function __construct($yamlFile, $name)
    {
        $this->testName = $name;
        $this->yaml = Spyc::YAMLLoad($yamlFile);  
    }

    private function getYamlKeyByName($name)
    {
        if (isset($this->yaml[$this->testName])) {
            return $this->yaml[$this->testName][$name];
        }
        if (isset($this->yaml['shared'])) {
            return $this->yaml['shared'][$name];
        }
        throw new Exception("Neither {$this->testName} nor 'shared' fixture found");
    }

    private function getYamlTypeKeyByName($type, $name)
    {
        $wsKey = $this->getYamlKeyByName($type);
        $values = $wsKey[$name];
        if (empty($values)) {
            return array();
        }
        return $values;
    }

    public function getWorkspacePaths()
    {
         return $this->getYamlTypeKeyByName(self::WORKSPACE, self::PATHS);
    }

    public function getWorkspaceNames()
    {
        return $this->getYamlTypeKeyByName(self::WORKSPACE, self::NAMES);
    }

    public function getTypesByWorkspacePath($path)
    {
        $items = $this->getYamlTypeKeyByName(self::WORKSPACE, self::ITEMS); 
        if (!isset($items)) {
            if (!is_array($items)) {
                throw new Exception("No items defined for '{$path}' workspace");
            }
        }
        if (!isset($items[$path])) {
            return array();
        }
        return array_keys($items[$path]);
    }

    public function getItemsByWorkspacePath($path, $typeName)
    {
        $items = $this->getYamlTypeKeyByName(self::WORKSPACE, self::ITEMS); 
        if (!isset($items)) {
            if (!is_array($items)) {
                throw new Exception("No items defined for '{$path}' workspace");
            }
        }
        if (!isset($items[$path][$typeName])) {
            return array();
        }
        return $items[$path][$typeName];
    }

    public function getWorkspaceChildrenNames($workspaceName)
    {
        $names = $this->getYamlTypeKeyByName(self::WORKSPACE, self::CHILDREN);
        return $names[$workspaceName];
    }

    public function getTypesPossible()
    {
        return $this->getYamlTypeKeyByName(self::TYPES, self::POSSIBLE);
    }

    public function getTypesStored()
    {
        return $this->getYamlTypeKeyByName(self::TYPES, self::STORED);
    }
}
?>
