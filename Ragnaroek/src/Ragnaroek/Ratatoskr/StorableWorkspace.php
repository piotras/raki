<?php

namespace Ragnaroek\Ratatoskr;

use \MidgardWorkspace;
use \MidgardConnection;
use \MidgardQueryStorage;
use \MidgardQuerySelect;
use \MidgardQueryConstraint;
use \MidgardQueryValue;
use \MidgardQueryProperty;
use \MidgardSqlQuerySelectData;
use \MidgardSqlQueryColumn;

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


    private function getChildrenMidgardWorkspaces()
    {
        $storage = new MidgardQueryStorage("midgard_workspace");
        $qs = new MidgardQuerySelect($storage);
        $qs->toggle_readonly(false);
        $qs->set_constraint(
            new MidgardQueryConstraint(
                new MidgardQueryProperty("up"),
                "=",
                new MidgardQueryValue($this->workspace->id)
            )
        );

        $qs->execute();
        return $qs->list_objects();
    }

    public function getChildrenNames()
    {
        # Enable this call, once Midgard2 core's bug is fixed and available
        # return $this->workspace->list_workspace_names();
         
        $children = $this->getChildrenMidgardWorkspaces();

        $names = array();
        foreach ($children as $child) {
            $names[] = $child->name;
        }

        return $names;
    }

    public function getChildren()
    {
        $children = $this->getChildrenMidgardWorkspaces();
        #$children = $this->workspace->list_children();
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
