<?php

class WorkspaceTest extends RakiTest 
{
    private $manager = null;

    public function setUp() 
    {
        $this->manager = new RagnaroekWorkspaceManager();
    }

    public function testWorkspacesNames()
    {
        print_r($this->manager->getPossibleWorkspacesNames());
    }
}

?>

