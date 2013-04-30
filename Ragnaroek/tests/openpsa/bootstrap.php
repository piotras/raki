<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../src/Ragnaroek/Ratatoskr/Utils/StoragePrepare.php';

require __DIR__ . '/transition-config.php';

# prepare storage and import content
$executor = new StoragePrepare($transition_config);
$executor->execute();

# TODO, set values from transition_config array
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

            $this->transition = new \Ragnaroek\Ratatoskr\Transition(MidgardConnection::get_instance(), $config, __DIR__ . '/fixtures/', __DIR__ . '/../../data/ragnaroek/schema');
        }

        return $this->transition;
    }

    static public function getFixture($name = 'shared')
    {
        $yaml = get_called_class() . '.yaml';
        return new \CRTransition\ResultFixture( __DIR__ . '/fixtures/' . $yaml, $name);
    }
}
