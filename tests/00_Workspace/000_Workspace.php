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

    public function testWorkspacesDump()
    {
        $expected = array(
            '/SG0',
            '/SG0/Raki SG1',
            '/SG0/Raki SG1/default',
            '/SG0/Raki SG1/fi',
            '/SG0/Raki SG1/ru'
        );

        $this->assertEquals($this->manager->dumpWorkspaces(), $expected);
    }
}

?>

