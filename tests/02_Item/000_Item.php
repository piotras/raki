<?php

require_once 'ResultFixture.php';

class StorableItemTest extends RakiTest 
{
    private $manager = null;
    private $workspaceManager = null;

    public function setUp() 
    {
        $this->manager = new RagnaroekContentManager();
        $this->workspaceManager = new RagnaroekWorkspaceManager();
    }

    private function getItems()
    {

    }

    public function testGetPath()
    {
        $rf = self::getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $types = $rf->getTypesByWorkspacePath($path);
            foreach ($types as $type) {
                $itemPaths = $rf->getItemsByWorkspacePath($path, $type);
                foreach ($itemPaths as $itemPath => $props) {
                    $ws = $this->workspaceManager->getStoredWorkspaceByPath($path);
                    $item = $this->manager->getItemByPath($ws, $type, $itemPath);
                    $this->assertInstanceOf('StorableItem', $item);
                    $this->assertEquals($itemPath, $item->getPath());
                }
            }
        }
    }

    public function testGetName()
    {
        $rf = self::getFixture(__FUNCTION__);
        $paths = $rf->getWorkspacePaths();
        foreach ($paths as $path) {
            $types = $rf->getTypesByWorkspacePath($path);
            foreach ($types as $type) {
                $itemPaths = $rf->getItemsByWorkspacePath($path, $type);
                foreach ($itemPaths as $itemPath => $props) {
                    $ws = $this->workspaceManager->getStoredWorkspaceByPath($path);
                    $item = $this->manager->getItemByPath($ws, $type, $itemPath);
                    $this->assertInstanceOf('StorableItem', $item);
                    $this->assertEquals($props['name'], $item->getName());
                }
            }
        }
    }
}

?>

