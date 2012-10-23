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
        $this->defaultFixture = $this->getDefaultFixture();
    }

    private function getDefaultFixture()
    {
        $yaml = __CLASS__ . '.yaml';
        return new ResultFixture( __DIR__ . '/' . $yaml, 'shared'); 
    }

    public function testPossibleWorkspacesNames()
    {
        $this->assertEquals($this->defaultFixture->getWorkspaceNames(), $this->manager->getPossibleWorkspacesNames());
    }

    public function testPossibleWorkspacesPaths()
    {

        $this->assertEquals($this->defaultFixture->getWorkspacePaths(), $this->manager->getPossibleWorkspacesPaths());
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
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0'));
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0/Raki SG1'));
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0/Raki SG1/multilang'));
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0/Raki SG1/multilang/fi'));
        $this->assertTrue($this->midgardWorkspaceManager->path_exists('/SG0/Raki SG1/multilang/ru'));
    }

    public function testStoredWorkspacesNames()
    {
        $this->assertEquals($this->defaultFixture->getWorkspaceNames(), $this->manager->getStoredWorkspacesNames());
    }

    public function testStoredWorkspacesPaths()
    {
        $this->assertEquals($this->defaultFixture->getWorkspacePaths(), $this->manager->getStoredWorkspacesPaths());
    }

    public function testGetStoredWorkspaceByPath()
    {
        $this->assertEquals("/TODO", $this->manager->getStoredWorkspaceByPath("/TODO"));
    }

    public function testGetStoredWorkspaceByName()
    {
        $this->assertEquals("TODO", $this->manager->getStoredWorkspaceByName("TODO"));
    }
}

?>

