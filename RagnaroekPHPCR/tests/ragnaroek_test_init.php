<?php

if (gc_enabled()) {
    /* https://github.com/midgardproject/midgard-php5/issues/50\n */
    gc_disable(); 
}

require __DIR__ . '/../vendor/autoload.php';

/* Export Ragnaroek content */
$schemaDir = __DIR__ . "/../data";
$exportScript = __DIR__ . "/../src/Ragnaroek/PHPCR/ExportMidgardCR.php";
$command = "php -d midgard.schema_path={$schemaDir}  -d memory_limit=-1 {$exportScript}";
passthru($command);

//PHPUnit 3.4 compat
if (method_exists('PHPUnit_Util_Filter', 'addDirectoryToFilter')) {
    require_once 'PHPUnit/Framework.php';
}

class RakiTest extends PHPUnit_Framework_TestCase
{
    public $transition = null;

    public function testFake() 
    {   
    
    }

    public function getTransition()
    {
        $exportDir = "/tmp/Midgard-Ragnaroek-PHPCR-Export";

        if ($this->transition == null) {
            $this->transition = new \Ragnaroek\PHPCR\Transition(
                '\Midgard\PHPCR\RepositoryFactory', $GLOBALS, MidgardConnection::get_instance(), __DIR__ . '/fixtures/', $exportDir);
        }

        return $this->transition;
    }

    static public function getFixture($name = 'shared')
    {
        $yaml = get_called_class() . '.yaml';
        return new \CRTransition\ResultFixture( __DIR__ . '/fixtures/' . $yaml, $name);
    }
}
