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
        $re = new \ReflectionExtension("midgard2");
        $names = array();
        foreach ($re->getClasses() as $class_ref) {

            if ($class_ref->isAbstract() || $class_ref->isInterface()) {
                continue;
            }

            $name = $class_ref->getName();
            if (!is_subclass_of ($name, 'MidgardDBObject')) {
                continue;
            }

            $names[] = $name;
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
