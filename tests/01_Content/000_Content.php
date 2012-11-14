<?php

require_once 'ResultFixture.php';

class ContentManagerTest extends RakiTest 
{
    private $manager = null;
    private $workspaceManager = null;

    public function setUp() 
    {
        $this->manager = $this->getTransition()->getContentManager();
        $this->workspaceManager = $this->getTransition()->getWorkspaceManager();
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
        $types = $this->manager->getPossibleTypeNames();
        foreach ($types as $type) {
            $this->manager->importType($type);
        }
    }

    public function testZGetItemByPath()
    {
        $rf = self::getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $types = $rf->getTypesByWorkspacePath($path);
            foreach ($types as $type) {
                $itemPaths = $rf->getItemsByWorkspacePath($path, $type);
                foreach ($itemPaths as $itemPath => $properties) {
                    $ws = $this->workspaceManager->getStoredWorkspaceByPath($path);
                    $item = $this->manager->getItemByPath($ws, $type, $itemPath);
                    $this->assertInstanceOf('StorableItem', $item, "Type: '{$type}' at path: '{$itemPath}'");
                }
            }
        }
    }
}

?>

