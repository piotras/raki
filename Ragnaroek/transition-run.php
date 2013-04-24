<?php

if (!isset($argv[1])) {
    throw new Exception("Config file needed as an argument");
}

# Load mandatory files

if (!extension_loaded('mysql')) {
    throw new Exception('MySQL extension not loaded!');
}
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Ragnaroek/Ratatoskr/Utils/StoragePrepare.php';

$configFile = $argv[1];
include $configFile;

# prepare schemas
passthru("php ./src/Ragnaroek/Ratatoskr/Utils/SchemasCopy.php {$configFile}");

# prepare storage and import content
$executor = new StoragePrepare($transition_config);
$executor->executeAndImport();

?>
