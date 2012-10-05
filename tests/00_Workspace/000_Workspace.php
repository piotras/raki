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
                    'multilang' => array(
                        'fi' => array(),
                        'ru' => array()
                    )
                )
            )
        );

        $this->assertEquals($expected, $this->manager->getPossibleWorkspacesNames());
    }

    public function testWorkspacesDump()
    {
        $expected = array(
            '/SG0',
            '/SG0/Raki SG1',
            '/SG0/Raki SG1/multilang',
            '/SG0/Raki SG1/multilang/fi',
            '/SG0/Raki SG1/multilang/ru'
        );

        $this->assertEquals($expected, $this->manager->getPossibleWorkspacesPaths());
    }
}

?>

