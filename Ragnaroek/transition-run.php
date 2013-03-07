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
        # rename type to avoid collision
        if (strpos($old_name, "ratatoskr") === false) {
            $new_name = "ratatoskr_".$old_name;
            echo "Rename {$old_name} to {$new_name} \n";
            $xml->type[$i]->attributes()->name = $new_name;
        }
        # rename parent type if set 
        if (isset($xml->type[$i]->attributes()->parent)) {
            $old_parent = $xml->type[$i]->attributes()->parent;
            if (strpos($old_parent, "ratatoskr") === false) {
                $new_parent = "ratatoskr_".$old_parent;
                echo "Rename parent {$old_parent} to {$new_parent} \n";
                $xml->type[$i]->attributes()->parent = $new_parent;
            }
        }
        foreach($xml->type[$i]->children() as $child => $property) {
            # if there's a link, rename it 
            if (isset($property->attributes()->link)) {
                $old_link = $property->attributes()->link;
                if (strpos($old_link, "ratatoskr") === false) {
                    $new_link = "ratatoskr_".$old_link;
                    $property->attributes()->link = $new_link;
                    echo "Rename {$old_link} to {$new_link} \n";
                }
            }
            # check if sitegroup property is already registered 
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
