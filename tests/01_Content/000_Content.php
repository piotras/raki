<?php

require_once 'ResultFixture.php';

class ContentManagerTest extends RakiTest 
{
    private $manager = null;

    public function setUp() 
    {
        $this->manager = new RagnaroekContentManager();
    }

    public function testGetPossibleTypeNames()
    {
        $rf = self::getFixture(__FUNCTION__);
        /* Get defined and expected type names */
        $expected = $rf->getTypesPossible();
        /* Get actual type names */
        $actual = $this->manager->getPossibleTypeNames();
        foreach ($expected as $type) {
            $this->assertContains($type, $actual);
        }
    }

    public function testGetStoredTypeNames()
    {
        $this->markTestSkipped('');
        $rf = self::getFixture(__FUNCTION__);
        /* Get defined and expected type names */
        $expected = $rf->getTypesStored();
        /* Get actual type names */
        $actual = $this->manager->getStoredTypeNames();
        foreach ($expected as $type) {
            $this->assertContains($type, $actual);
        }
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

