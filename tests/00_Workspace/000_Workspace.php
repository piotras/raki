<?php

require_once 'ResultFixture.php';

class WorkspaceTest extends RakiTest 
{
    private $manager = null;
    private $midgardWorkspaceManager = null;

    public function setUp() 
    {
        $this->manager = $this->getTransition()->getWorkspaceManager();
        $this->midgardWorkspaceManager = new midgard_workspace_manager(MidgardConnection::get_instance());
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
        $childName = key(next($names[$this->manager->getDefaultWorkspaceName()]));
        echo $childName;
        $this->manager->createWorkspace($childName, $ws);
        $this->assertTrue($this->manager->storedWorkspacePathExists('/' . $dName . '/' . $wsName . '/' . $childName));


        //$ws = new midgard_workspace();
        //$this->midgardWorkspaceManager->get_workspace_by_path($ws, '/SG0/Raki SG1');
        //$this->manager->createWorkspace('multilang', $ws);
        //$this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0/Raki SG1/multilang'));
    }

    public function testCreateWorkspaceAll()
    { 
        $this->manager->createWorkspacesAll();
        $rf = self::getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $this->assertTrue($this->midgardWorkspaceManager->path_exists($path));
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

