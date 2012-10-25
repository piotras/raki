<?php

class StorableWorkspaceTest extends RakiTest 
{
    private $manager = null;
    private $midgardWorkspaceManager = null;

    public function setUp() 
    {
        $this->manager = new RagnaroekWorkspaceManager();
        $this->midgardWorkspaceManager = new midgard_workspace_manager(MidgardConnection::get_instance());
    }

    public function testGetName()
    {
        $this->assertEquals("TODO", "DONE");
    }

    public function testGePath()
    {
        $this->assertEquals("TODO", "DONE");
    }

    public function testGetWorkspacesNames()
    {
        $this->assertEquals("TODO", "DONE");
    }

    public function testGetWorkspaces()
    {
        $this->assertEquals("TODO", "DONE");
    }
}

?>

