<?php

class WorkspaceTest extends RakiTest 
{
    private $manager = null;
    private $midgardWorkspaceManager = null;
    private $workspacesNames = array(
        'SG0' => array(
            'Raki SG1' => array(
                'multilang' => array(
                    'fi' => array(),
                    'ru' => array()
                )
            )
        )
    );
    private $workspacesPaths = array(
        '/SG0',
        '/SG0/Raki SG1',
        '/SG0/Raki SG1/multilang',
        '/SG0/Raki SG1/multilang/fi',
        '/SG0/Raki SG1/multilang/ru'
    );

    public function setUp() 
    {
        $this->manager = new RagnaroekWorkspaceManager();
        $this->midgardWorkspaceManager = new midgard_workspace_manager(MidgardConnection::get_instance());
    }

    public function testPossibleWorkspacesNames()
    {
        $this->assertEquals($this->workspacesNames, $this->manager->getPossibleWorkspacesNames());
    }

    public function testPossibleWorkspacesPaths()
    {
        $this->assertEquals($this->workspacesPaths, $this->manager->getPossibleWorkspacesPaths());
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
        $this->assertEquals($this->workspacesNames, $this->manager->getStoredWorkspacesNames());
    }

    public function testStoredWorkspacesPaths()
    {
        $this->assertEquals($this->workspacesPaths, $this->manager->getStoredWorkspacesPaths());
    }
}

?>

