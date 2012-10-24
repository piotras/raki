<?php

require_once 'ResultFixture.php';

class WorkspaceTest extends RakiTest 
{
    private $manager = null;
    private $midgardWorkspaceManager = null;
    private $defaultFixture = null;

    public function setUp() 
    {
        $this->manager = new RagnaroekWorkspaceManager();
        $this->midgardWorkspaceManager = new midgard_workspace_manager(MidgardConnection::get_instance());
    }

    private function getFixture($name = 'shared')
    {
        $yaml = __CLASS__ . '.yaml';
        return new ResultFixture( __DIR__ . '/' . $yaml, $name); 
    }

    public function testPossibleWorkspacesNames()
    {
        $rf = $this->getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspaceNames(), $this->manager->getPossibleWorkspacesNames());
    }

    public function testPossibleWorkspacesPaths()
    {
        $rf = $this->getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspacePaths(), $this->manager->getPossibleWorkspacesPaths());
    }

    public function testCreateWorkspaceSG0()
    {
        $this->manager->createWorkspace('SG0');   
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0'));
    }

    public function testCreateWorkspaceSG1()
    {
        $ws = new midgard_workspace();
        $this->midgardWorkspaceManager->get_workspace_by_path($ws, '/SG0');
        $this->manager->createWorkspace('Raki SG1', $ws);
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0/Raki SG1'));
    }

    public function testCreateWorkspaceMultilang()
    {
        $ws = new midgard_workspace();
        $this->midgardWorkspaceManager->get_workspace_by_path($ws, '/SG0/Raki SG1');
        $this->manager->createWorkspace('multilang', $ws);
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0/Raki SG1/multilang'));
    }

    public function testCreateWorkspaceAll()
    { 
        $this->manager->createWorkspacesAll();
        $rf = $this->getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $this->assertTrue($this->midgardWorkspaceManager->path_exists($path));
        }
    }

    public function testStoredWorkspacesNames()
    {
        $rf = $this->getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspaceNames(), $this->manager->getStoredWorkspacesNames());
    }

    public function testStoredWorkspacesPaths()
    {
        $rf = $this->getFixture(__FUNCTION__);
        $this->assertEquals($rf->getWorkspacePaths(), $this->manager->getStoredWorkspacesPaths());
    }

    public function testGetStoredWorkspaceByPath()
    {
        $rf = $this->getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $ws = $this->manager->getStoredWorkspaceByPath($path);
            $this->assertTrue($ws instanceof StorableWorkspace);
            $this->assertEquals($path, $ws->getPath());
        }
    }

    public function testGetStoredWorkspaceByName()
    {
        $rf = $this->getFixture(__FUNCTION__);
        $names = $rf->getWorkspaceNames();
        foreach ($names as $name) {
            $ws = $this->manager->getStoredWorkspaceByName($name);
            $this->assertTrue($ws instanceof StorableWorkspace);
            $this->assertEquals($name, $ws->getName());
        }
    }
}

?>

