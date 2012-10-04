<?php

interface WorkspaceManager {

    public function getPossibleWorkspacesNames($parent = null);
    public function dumpWorkspaces();
    public function createWorkspace($name, $parent = null);
}

?>
