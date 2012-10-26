<?php

class RagnaroekContentManager implements ContentManager 
{
    public function __construct()
    {

    }

    public function getPossibleTypeNames()
    {
        $re = new ReflectionExtension("midgard2");
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

    public function importType($typename)
    {
        $command = "sudo mysql midgard_raki < temporary_topic_update.sql";
        exec($command);
    }

    public function getStoredTypeNames()
    {
        $storedTypes= array();
        $allTypes = $this->getPossibleTypeNames();
        foreach ($allTypes as $type) {
            $qs = new MidgardQuerySelect(
                new MidgardQueryStorage($type)
            );
            $qs->set_limit(1);
            $qs->execute();

            if ($qs->get_results_count() > 0) {
                $storedTypes[] = $type;
            }
        }
        return $stroedTypes;
    }

    public function getItemByPath(StorableWorkspace $workspace, $relPath)
    {
        return null;
    } 
}

?>
