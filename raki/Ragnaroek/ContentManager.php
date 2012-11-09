<?php

class RagnaroekContentManager implements ContentManager 
{
    private $transition = null;
    private $mgdSchemaToSQL = null;

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

        $sts = $this->getMgdSchemaToSQL();
        $types = $sts->getMidgardTypes();
        foreach ($types as $name) {
            $names[] = $name;
        }

        return $names;
    }

    private function getMgdSchemaToSQL()
    {
        if ($this->mgdSchemaToSQL == null) {
            $this->mgdSchemaToSQL = new RagnaroekMgdSchemaToSQL();
            $files = $this->getTransition()->getSchemaPaths();
            foreach ($files as $file) {
                $this->mgdSchemaToSQL->addFile($file);
            }
        }

        return $this->mgdSchemaToSQL;
    }

    public function importType($typeName)
    {       
        if ($typeName != 'midgard_topic') {
            return;
        }

        $workspaceManager = $this->getTransition()->getWorkspaceManager();
        $wsPaths = $workspaceManager->getStoredWorkspacesPaths();
        $sitegroups = $workspaceManager->getMidgardSitegroups(); 
        $dLang = $workspaceManager->getDefaultLanguage();
        $ws = $workspaceManager->getStoredWorkspaceByName($dLang->code)->getMidgardWorkspace();
        $sts = $this->getMgdSchemaToSQL();
        /* Copy content to one table - for every sitegroup and default language */
        foreach ($sitegroups as $sg) {  
            echo $sts->getSQLUpdateTypePre($typeName, $ws->id, $sg->id, $dLang->id);
        }
        /* Delete content with default language */
        /* Avoid duplicates in following bulk update */
        echo $sts->getSQLDeleteTypePre($typeName, $dLang->id);

        /* For every sitegroup, create multilang content */
        $languages = $workspaceManager->getMidgardLanguagesByType($sts, $typeName);
        foreach ($sitegroups as $sg) {
            echo $sts->getSQLInsertType($typeName, $sg->id, 0, 0);
        }

        /* Set unique object's id in workspace */
        echo $sts->getSQLUpdateTypePost($typeName);
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
