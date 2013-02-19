<?php

namespace Ragnaroek\Ratatoskr;

use \MidgardConnection;
use \MidgardQueryStorage;
use \MidgardQuerySelect;
use \MidgardQueryProperty;
use \MidgardQueryValue;
use \MidgardQueryConstraint;
use \MidgardUser;

class ContentManager implements \CRTransition\ContentManager 
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
            $this->mgdSchemaToSQL = new \Ragnaroek\Ratatoskr\MgdSchemaToSQL();
            $files = $this->getTransition()->getSchemaPaths();
            foreach ($files as $file) {
                $this->mgdSchemaToSQL->addFile($file);
            }
        }

        return $this->mgdSchemaToSQL;
    }

    public function importType($typeName)
    {       
        $mysql = $this->getTransition()->getMySQL();

        $workspaceManager = $this->getTransition()->getWorkspaceManager();
        $sitegroups = $workspaceManager->getMidgardSitegroups(); 
        $dSGName = $workspaceManager->getDefaultWorkspaceName();
        $dLang = $workspaceManager->getDefaultLanguage();
        $sts = $this->getMgdSchemaToSQL();
        /* Copy content to one table - for every sitegroup and default language */
        foreach ($sitegroups as $sg) { 
            $mlPath = '/' . $dSGName . '/' . $sg->name . '/' . $dLang->code;
            $ws = $workspaceManager->getMidgardWorkspaceByPath($mlPath);
            $lang = $workspaceManager->getLegacyMidgardType($mlPath);
            $q = $sts->getSQLUpdateTypePre($typeName, $ws->id, $sg->id, $lang->id);
            //echo $q . "\n";
            $mysql->query($q);

        }
        /* Delete content with default language */
        /* Avoid duplicates in following bulk update */
        $q = $sts->getSQLDeleteTypePre($typeName, $dLang->id);
        //echo $q . "\n";
        $mysql->query($q);


        /* Get all workspaces which represent languages.
         * It has to be done explicitly cause one *named* language can be represented by 
         * different workspaces. 
         * e.g. /SG1/multilang/en /SG2/multilang/en */
        $paths = $workspaceManager->getStoredWorkspacesPaths();
        $languages = array();
        foreach ($paths as $path) {
            $legacy = $workspaceManager->getLegacyMidgardType($path);
            if ($legacy instanceof \midgard_language) {
                if ($legacy->id == 0) {
                    continue;
                }
                $mws = $workspaceManager->getMidgardWorkspaceByPath($path);
                $languages[$mws->id] = $legacy->id;
            }
        }

        /* For every sitegroup, create multilang content */        
        foreach ($sitegroups as $sg) {
            foreach ($languages as $workspaceID => $langID) {
                $q = $sts->getSQLInsertType($typeName, $sg->id, $workspaceID, $langID);
                //echo $q . "\n";
                $mysql->query($q);
            }
        }

        /* Set unique object's id in workspace */
        $q = $sts->getSQLUpdateTypePost($typeName);
        //echo $q . "\n";
        $mysql->query($q);

        if ($typeName == 'midgard_person') {
            $this->convertPersonToUser();
        }
    }

    public function convertPersonToUser()
    {
        $qs = new MidgardQuerySelect(
            new MidgardQueryStorage("midgard_person")
        );
        $qs->execute();

        foreach ($qs->list_objects() as $person)
        {
            $user = new MidgardUser();
            $user->active = true;
            $hasPlainText = false;
            $pass = $person->password;
            $plain = substr($pass, 0, 2);
            if ($plain == "**") {
                $hasPlainText = true;
                $pass = substr($pass, 2);
            }
            $user->authtype = $hasPlainText ? "Plaintext" : "Legacy";
            $user->login = $person->username;
            $user->password = $pass;
            $user->usertype = 0;

            $user->create();
        }
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

    public function getItemByPath(\CRTransition\StorableWorkspace $workspace, $typeName, $relPath)
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
            $item = new \Ragnaroek\Ratatoskr\StorableItem($objects[0]);
        }

        if ($wsInitial != null) {
            $mgd->set_workspace($wsInitial);
        }

        return $item;
    } 
}

?>
