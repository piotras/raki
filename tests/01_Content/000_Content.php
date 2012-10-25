<?php

require_once 'ResultFixture.php';

class ContentManagerTest extends RakiTest 
{
    private $manager = null;

    public function setUp() 
    {
        $this->manager = new RagnaroekWorkspaceManager();
    }

    public function testGetPossibleTypeNames()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals("DONE", "TODO");
    }

    public function testGetStoredTypeNames()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals("DONE", "TODO");
    }

    public function testImportType()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals("DONE", "TODO");
    }

    public function testZGetItemByPath()
    {
        $rf = self::getFixture(__FUNCTION__);
        $this->assertEquals("DONE", "TODO");
    }
}

?>

