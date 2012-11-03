<?php

class RagnaroekContentManager implements ContentManager 
{
    private $transition = null;

    public function __construct($transition)
    {
        $this->transition = $transition;
    }

    public function getTransition()
    {
        return $this->transition;
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
        if ($typename != 'ragnaroek_topic') {
            return;
        }
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

    public function getItemByPath(StorableWorkspace $workspace, $typeName, $relPath)
    {
        $mgd = MidgardConnection::get_instance();

        /* Get workspace so we can set it back */
        $wsInitial = $mgd->get_workspace();
        $mgd->set_workspace($workspace->getMidgardWorkspace());
        
        $item = null;
        $qs = new MidgardQuerySelect(
            new MidgardQueryStorage($typeName)
        );

        $qs->set_constraint(
            new MidgardQueryConstraint(
                new MidgardQueryProperty("name"), 
                "=", 
                new MidgardQueryValue($relPath)
            )
        );
        //$mgd->set_loglevel("debug");
        $qs->execute();
        //$mgd->set_loglevel("warn");
        if ($qs->get_results_count() > 0) {
            $objects = $qs->list_objects();
            $item = new RagnaroekStorableItem($objects[0]);
        }

        if ($wsInitial != null) {
            $mgd->set_workspace($wsInitial);
        }

        return $item;
    } 
}

?>
