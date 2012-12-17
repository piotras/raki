<?php

namespace Ragnaroek\PHPCR;

class ContentManager implements \CRTransition\ContentManager 
{
    private $transition = null;
    private $sessions = null;

    public function __construct($transition)
    {
        $this->transition = $transition;
        $this->sessions = array();
    }

    public function getTransition()
    {
        return $this->transition;
    }

    public function getPossibleTypeNames()
    {
        $exportDir = $this->getTransition()->getExportDir();
        $names = array();

        $d = dir($exportDir);
        /* Read possible workspaces */
        while (($entry = $d->read()) !== false) {
            if ($entry == "." || $entry == "..") {
                continue;
            }
            /* Read possible types */
            $wsName = $exportDir . "/" . $entry;
            $t = dir($wsName);
            while (($e = $t->read()) !== false) {
                if ($e == "." || $e == "..") {
                    continue;
                }
                $names[$e] = $e;
            }
        }

        return $names;
    }

    private function getSessions()
    {
        if (empty($this->sessions)) {
            $workspaces = $this->getTransition()->getWorkspaceManager()->getPossibleWorkspacesNames();
            foreach ($workspaces as $name) {
                $this->sessions[$name] = $this->getTransition()->getPHPCRRepository()->login(null, $name);
            }
        }

        return $this->sessions;
    }

    public function importType($typeName)
    {
        $sessions = array();
        $exportDir = $this->getTransition()->getExportDir();

        foreach ($this->getSessions() as $name => $session) {
            $filePath = $exportDir . "/" . $name . "/" . $typeName;
            /* Import only xml files */
            $info = pathinfo($filePath);
            if ($info['extension'] != 'xml') {
                continue;
            }
            $session->importXML("/", $filePath, 0); /* FIXME, ImportUUIDBehavior */
        }
    }

    public function getStoredTypeNames()
    {
        throw new Exception("Not Implemented");
    }

    public function getItemByPath(\CRTransition\StorableWorkspace $workspace, $typeName, $relPath)
    {
        throw new Exception("Not Implemented");
    } 
}

?>
