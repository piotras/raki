<?php

if (!isset($argv[1])) {
    throw new Exception("Config file needed as an argument");
}

$configFile = $argv[1];
include $configFile;

if (!isset($transition_config)) {
    throw new Exception("'transition_config' not set");
}

$schema_directory_ragnaroek = $transition_config['schema_directory_ragnaroek'];
$schema_directory_transition = $transition_config['schema_directory_transition'];
$schema_directory_sql = $transition_config['schema_directory_sql'];

# Copy schema files
echo "Copy schema files to generate SQL queries \n";
exec("cp {$schema_directory_ragnaroek}/*.xml  {$schema_directory_sql}");

echo "Copy schema files to generate PHP classes \n";
exec("cp {$schema_directory_ragnaroek}/*.xml  {$schema_directory_transition}");

echo "Updating schemas \n";
if ($handle = opendir($schema_directory_transition)) {
    while (($entry = readdir($handle)) == true) {
        /* Ignore parent and self directory */
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $absPath = $schema_directory_transition . '/' . $entry;
        $info = pathinfo($absPath);
        /* Ignore non xml files */
        if ($info['extension'] != 'xml') {
            continue;
        }
        $paths[] = $absPath;
    }
}

foreach ($paths as $path) {
    $xml = simplexml_load_file($path);
    $i = 0;
    foreach ($xml->type as $k => $type) {
        $hasSG = false;
        $old_name = $xml->type[$i]->attributes()->name;
        $new_name = "ratatoskr_".$old_name;
        echo "Rename {$old_name} to {$new_name} \n";
        $xml->type[$i]->attributes()->name = $new_name;
        # check if sitegroup property is already registered 
        foreach($xml->type[$i]->children() as $child => $property) {
            print_r($property);
            if ($property->attributes()->name == "sitegroup") {
                $hasSG = true;
                continue;
            }
        }
        if ($hasSG == false) {
            $ch = $type->addChild('property');
            $ch->addAttribute('name', 'sitegroup');
            $ch->addAttribute('type', 'unsigned integer');
        }
        $i++;
    }
    $xml->asXml($path);
}

# finaly, execute real transition

exec("php -c midgard2.ini ./RagnaroekTransition.php {$configFile}");

?>
