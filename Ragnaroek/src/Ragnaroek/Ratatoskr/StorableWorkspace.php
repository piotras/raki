<?php

namespace Ragnaroek\Ratatoskr;

use \MidgardWorkspace;

class StorableWorkspace implements \CRTransition\StorableWorkspace
{
    private $workspace = null;

    public function __construct(MidgardWorkspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getName()
    {
        return $this->workspace->name;
    }

    public function getPath()
    {
        return $this->workspace->path;
    }

    public function getChildrenNames()
    {
        return $this->workspace->list_workspace_names();
    }

    public function getChildren()
    {
        $children = $this->workspace->list_children();
        if (empty($children)) {
            return array();
        }

        $ret = array();
        foreach ($children as $child) {
            $ret[] = new \Ragnaroek\Ratatoskr\StorableWorkspace($child);
        }

        return $ret;
    }

    public function getMidgardWorkspace()
    {
        return $this->workspace;
    }
}

?>
