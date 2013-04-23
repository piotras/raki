<?php

if (!isset($argv[1])) {
    throw new Exception("Config file needed as an argument");
}

$configFile = $argv[1];

# prepare schemas
passthru("php ragnaroek-prepare-schemas.php {$configFile}");

# execute real transition
passthru("php -c midgard2.ini ./RagnaroekTransition.php {$configFile}");

?>
