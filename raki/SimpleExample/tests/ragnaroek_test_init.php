<?php

if (gc_enabled()) {
    echo "Disabling Zend Garbage Collection to prevent segfaults, see:\n";
    echo "  https://bugs.php.net/bug.php?id=51091\n";
    echo "  https://github.com/midgardproject/midgard-php5/issues/50\n";
    gc_disable(); 
}

if (!extension_loaded('mysql')) {
    throw new Exception('MySQL extension not loaded!');
}

$midgardTestDB = "midgard_raki";
$midgardTestDBUser = "midgard_raki";
$midgardTestDBPass = "midgard_raki";

echo "Dropping test database if exists \n";
exec("sudo mysqladmin -f drop {$midgardTestDB}");
echo "Preparing test database \n";
exec("sudo mysql -e 'CREATE DATABASE {$midgardTestDB} CHARACTER SET utf8'");
$cmdGrant = "GRANT all ON {$midgardTestDB}.*  to '{$midgardTestDBUser}'@'localhost' identified by '{$midgardTestDBPass}'";
echo "Grant all privileges \n";
exec("sudo mysql -e \"{$cmdGrant}\"");
echo "Flush privileges \n";
exec("sudo mysql -e 'FLUSH PRIVILEGES'");
$path = __DIR__ . "/midgard.sql";
echo "Import Sql dump {$path} \n";
exec("sudo mysql {$midgardTestDB} < {$path}");


//PHPUnit 3.4 compat
if (method_exists('PHPUnit_Util_Filter', 'addDirectoryToFilter')) {
    require_once 'PHPUnit/Framework.php';
}

//require_once 'RakiTestContent.php';

$config = new MidgardConfig();
$config->dbtype = $GLOBALS['midgard2.configuration.db.type'];
$config->database = $GLOBALS['midgard2.configuration.db.name'];
$config->dbuser = $GLOBALS['midgard2.configuration.db.dbuser'];
$config->dbpass = $GLOBALS['midgard2.configuration.db.dbpass'];
$config->dbdir = $GLOBALS['midgard2.configuration.db.dir'];
$config->blobdir = $GLOBALS['midgard2.configuration.blobdir'];
$config->loglevel = $GLOBALS['midgard2.configuration.loglevel'];

$mgd = midgard_connection::get_instance();
$mgd->enable_workspace(true);
$mgd->open_config ($config);

/* Initialize storage and content */
//RakiTestContent::prepareContent();

spl_autoload_register(function($class) {
    include dirname(__FILE__) . '/../../' . str_replace('Ragnaroek', 'Ragnaroek/', $class)  . '.php';
});

midgard_storage::create_base_storage();
$re = new ReflectionExtension("midgard2");
foreach ($re->getClasses() as $class_ref) {
    $class_mgd_ref = new midgard_reflection_class($class_ref->getName());
    $parent_class = $class_mgd_ref->getParentClass();

    $name = $class_mgd_ref->getName();
    if (!is_subclass_of ($name, 'MidgardDBObject')
        || $class_mgd_ref->isAbstract()
        || $class_mgd_ref->isInterface()) {
            continue;
        }

    if (strpos($name, "_abstract") != false) {
        continue;
    }

    echo 'midgard_storage: create_class_storage('.$name.")\n";
    midgard_storage::create_class_storage($name);
    midgard_storage::update_class_storage($name);
}

class RakiTest extends PHPUnit_Framework_TestCase
{
    public $transition = null;

    public function testFake() 
    {   
    
    }

    public function getTransition()
    {
        if ($this->transition == null) {

            $config = new MidgardConfig();
            $config->database = $GLOBALS['midgard2.configuration.db.name'];
            $config->dbuser = $GLOBALS['midgard2.configuration.db.dbuser'];
            $config->dbpass = $GLOBALS['midgard2.configuration.db.dbpass'];

            $this->transition = new RagnaroekTransition(MidgardConnection::get_instance(), $config, __DIR__ . '/fixtures/', __DIR__ . '/../data/ragnaroek/schema');
        }

        return $this->transition;
    }

    static public function getFixture($name = 'shared')
    {
        $yaml = get_called_class() . '.yaml';
        return new ResultFixture( __DIR__ . '/fixtures/' . $yaml, $name);
    }
}
