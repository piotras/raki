<?php

class StorableWorkspaceTest extends RakiTest 
{
    private $manager = null;

    public function setUp() 
    {
        $this->manager = $this->getTransition()->getWorkspaceManager();
    }

    public function testGetName()
    {
        $rf = self::getFixture(__FUNCTION__);
        $names = $rf->getWorkspaceNames();
        foreach ($names as $name) {
            $ws = $this->manager->getStoredWorkspaceByName($name);
            $this->assertEquals($name, $ws->getName());
        }
    }

    public function testGePath()
    {
        $rf = self::getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $ws = $this->manager->getStoredWorkspaceByPath($path); 
            $this->assertEquals($path, $ws->getPath());
        }
    }

    public function testGetChildrenNames()
    {
        $rf = self::getFixture(__FUNCTION__);
        /* Get names defined in result fixture */
        $names = $rf->getWorkspaceNames();
        foreach ($names as $name) {
            /* Get all children names for defined workspace's name */
            $childrenNames = $rf->getWorkspaceChildrenNames($name);

            if (empty($childrenNames)) {
                continue;
            }

            $ws = $this->manager->getStoredWorkspaceByName($name);
            /* Test if every child name is valid for stored workspace ones */
            foreach ($childrenNames as $childName) {
                $this->assertContains($childName, $ws->getChildrenNames());
            }
        } 
    }

    public function testGetChildren()
    {
        $rf = self::getFixture(__FUNCTION__);
        /* Get names defined in result fixture */
        $names = $rf->getWorkspaceNames();
        foreach ($names as $name) {
            /* Get stored workspace by name and all its children */
            $ws = $this->manager->getStoredWorkspaceByName($name);
            $children = $ws->getChildren();

            if (empty($children)) {
                continue;
            }
            $childrenNames = $rf->getWorkspaceChildrenNames($name);

            /* Test if stored child name is defined in fixture */
            foreach ($children as $child) {
                $this->assertNotNull($child->getName());
                $this->assertContains($child->getName(), $childrenNames, "Not found as child of '{$name}'");
            }
        } 
    }
}

?>

