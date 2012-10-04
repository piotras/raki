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
        $expected = array(
            'SG0' => array(
                'Raki SG1' => array(
                    'default' => array(),
                    'fi' => array(),
                    'ru' => array()
                )
            )
        );
        $this->assertEquals($this->manager->getPossibleWorkspacesNames(), $expected);
    }
}

?>

