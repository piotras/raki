<?php

if (!extension_loaded('mysql')) {
    throw new Exception('MySQL extension not loaded!');
}

require __DIR__ . '/vendor/autoload.php';

/*
require_once __DIR__ . '/../raki/Transition.php';
require_once __DIR__ . '/../raki/Ragnaroek/Transition.php';
require_once __DIR__ . '/../raki/Ragnaroek/MySQL.php';
require_once __DIR__ . '/../raki/Ragnaroek/MgdSchemaToSQL.php';
require_once __DIR__ . '/../raki/WorkspaceManager.php';
require_once __DIR__ . '/../raki/Ragnaroek/WorkspaceManager.php';
require_once __DIR__ . '/../raki/Storable.php';
require_once __DIR__ . '/../raki/StorableWorkspace.php';
require_once __DIR__ . '/../raki/Ragnaroek/StorableWorkspace.php';
require_once __DIR__ . '/../raki/ContentManager.php';
require_once __DIR__ . '/../raki/Ragnaroek/ContentManager.php';
 */

$midgardTestDB = "midgard_raki";
$midgardTestDBUser = "midgard_raki";
$midgardTestDBPass = "midgard_raki";

$MySQLDumpFile = "midgard.sql";

//echo "Dropping test database if exists \n";
//exec("sudo mysqladmin -f drop {$midgardTestDB}");
//echo "Preparing test database \n";
exec("sudo mysql -e 'CREATE DATABASE {$midgardTestDB} CHARACTER SET utf8'");
$cmdGrant = "GRANT all ON {$midgardTestDB}.*  to '{$midgardTestDBUser}'@'localhost' identified by '{$midgardTestDBPass}'";
echo "Grant all privileges \n";
exec("sudo mysql -e \"{$cmdGrant}\"");
echo "Flush privileges \n";
exec("sudo mysql -e 'FLUSH PRIVILEGES'");
$path = __DIR__ . "/" . $MySQLDumpFile;
echo "Import Sql dump {$path} \n";

exec("sudo mysql {$midgardTestDB} < {$path}", $out, $returnValue);

if ($returnValue != 0) {
    throw new Exception("Failed to import data from sql file");
}

$config = new MidgardConfig();
$config->dbtype = 'MySQL';
$config->database = $midgardTestDB;
$config->dbuser = $midgardTestDBUser;
$config->dbpass = $midgardTestDBPass;

$mgd = midgard_connection::get_instance();
$mgd->enable_workspace(true);
$mgd->open_config ($config);

midgard_storage::create_base_storage();
$re = new ReflectionExtension("midgard2");
foreach ($re->getClasses() as $refClass) {
    $name = $refClass->getName();
    if (!is_subclass_of ($name, 'MidgardDBObject')
        || $refClass->isAbstract()
        || $refClass->isInterface()) {
            continue;
        }

    if (strpos($name, "_abstract") != false) {
        continue;
    }

    echo 'midgard_storage: prepare class storage('.$name.")\n";
    midgard_storage::create_class_storage($name);
    midgard_storage::update_class_storage($name);
}

$transition = new \Ragnaroek\Ratatoskr\Transition(MidgardConnection::get_instance(), $config, __DIR__ . '/fixtures/', __DIR__ . '/data/ragnaroek/schema');

$workspaceManager = $transition->getWorkspaceManager();
$workspaceManager->createWorkspacesAll();

$contentManager = $transition->getContentManager();
$types = $contentManager->getPossibleTypeNames();
foreach ($types as $type) {
    echo "Import '{$type}' content \n";
    $contentManager->importType($type);
}

