<?php

if (gc_enabled()) {
    echo "Disabling Zend Garbage Collection to prevent segfaults, see:\n";
    echo "  https://bugs.php.net/bug.php?id=51091\n";
    echo "  https://github.com/midgardproject/midgard-php5/issues/50\n";
    gc_disable(); 
}

exec (__DIR__ . '/bootstrap.sh');

//PHPUnit 3.4 compat
if (method_exists('PHPUnit_Util_Filter', 'addDirectoryToFilter')) {
    require_once 'PHPUnit/Framework.php';
}

require_once 'RakiTestContent.php';

$config = new MidgardConfig();
$config->dbtype = $GLOBALS['midgard2.configuration.db.type'];
$config->database = $GLOBALS['midgard2.configuration.db.name'];
$config->database = $GLOBALS['midgard2.configuration.db.dbuser'];
$config->database = $GLOBALS['midgard2.configuration.db.dbpass'];
$config->dbdir = $GLOBALS['midgard2.configuration.db.dir'];
$config->blobdir = $GLOBALS['midgard2.configuration.blobdir'];
$config->loglevel = $GLOBALS['midgard2.configuration.loglevel'];

$mgd = midgard_connection::get_instance();
var_dump($mgd->open_config ($config));

/* Initialize storage and content */
RakiTestContent::prepareContent();

spl_autoload_register(function($class) {
    include dirname(__FILE__) . '/../raki/' . str_replace('Ragnaroek', 'Ragnaroek/', $class)  . '.php';
});

class RakiTest extends PHPUnit_Framework_TestCase
{
    public function testFake() 
    {   
    
    }
}
