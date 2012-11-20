<?php

require_once 'ResultFixture.php';

class WorkspaceTest extends RakiTest 
{
    private $manager = null;

    public function setUp() 
    {
        $this->manager = $this->getTransition()->getWorkspaceManager();
    }

    public function testPossibleWorkspacesNames()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspaceNames(), $this->manager->getPossibleWorkspacesNames());
    }

    public function testPossibleWorkspacesPaths()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspacePaths(), $this->manager->getPossibleWorkspacesPaths());
    }

    public function testCreateWorkspaceDefault()
    {
        $dName = $this->manager->getDefaultWorkspaceName();
        $this->manager->createWorkspace($dName);   
        $this->assertTrue($this->manager->storedWorkspacePathExists('/' . $dName));
    }

    public function testCreateWorkspaceChild()
    {
        $rf = self::getFixture(__FUNCTION__);
        $names = $rf->getWorkspaceNames();
        $dName = $this->manager->getDefaultWorkspaceName();
        $wsName = key($names[$this->manager->getDefaultWorkspaceName()]);
        $ws = $this->manager->getStoredWorkspaceByPath($dName);
        $this->manager->createWorkspace($wsName, $ws);
        $this->assertTrue($this->manager->storedWorkspacePathExists('/' . $dName . '/' . $wsName));
    }

    public function testCreateWorkspaceChildChild()
    {
        $rf = self::getFixture(__FUNCTION__);
        $names = $rf->getWorkspaceNames();
        $dName = $this->manager->getDefaultWorkspaceName();
        $wsName = key($names[$this->manager->getDefaultWorkspaceName()]);
        $ws = $this->manager->getStoredWorkspaceByPath('/' . $dName . '/' . $wsName);
        $next = next($names[$this->manager->getDefaultWorkspaceName()]);
        if ($next === false) {
            return; /* Nothing to test */
        }
        $childName = key($next);
        $this->manager->createWorkspace($childName, $ws);
        $this->assertTrue($this->manager->storedWorkspacePathExists('/' . $dName . '/' . $wsName . '/' . $childName));
    }

    public function testCreateWorkspaceAll()
    { 
        $this->manager->createWorkspacesAll();
        $rf = self::getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $this->assertTrue($this->manager->storedWorkspacePathExists($path));
        }
    }

    public function testStoredWorkspacesNames()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspaceNames(), $this->manager->getStoredWorkspacesNames());
    }

    public function testStoredWorkspacesPaths()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspacePaths(), $this->manager->getStoredWorkspacesPaths());
    }

    public function testGetStoredWorkspaceByPath()
    {
        $rf = self::getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $ws = $this->manager->getStoredWorkspaceByPath($path);
            $this->assertInstanceOf(StorableWorkspace, $ws); 
        }
    }

    public function testGetStoredWorkspaceByName()
    {
        $rf = self::getFixture(__FUNCTION__);
        $names = $rf->getWorkspaceNames();
        foreach ($names as $name) {
            $ws = $this->manager->getStoredWorkspaceByName($name);
            $this->assertInstanceOf(StorableWorkspace, $ws);
        }
    }
}

?>

