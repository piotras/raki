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


$paths = array();

function updateSchemas($directory, &$paths)
{
    if ($handle = opendir($directory)) {
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
}

echo "Updating schemas \n";
updateSchemas($schema_directory_transition, $paths);
updateSchemas($schema_directory_sql, $paths);

function renameElement($name, $msg) 
{
    $prefix = "ragnaroek_";
    if (strpos($name, $prefix) !== false) {
        return $name;
    }
    $old_name = $name;
    $new_name = $prefix . $name;
    echo "Rename {$msg} {$old_name} to {$new_name} \n";
    return $new_name;    
}

foreach ($paths as $path) {
    $xml = simplexml_load_file($path);
    $i = 0;
    foreach ($xml->type as $k => $type) {
        $hasSG = false;
        $old_name = $xml->type[$i]->attributes()->name;
        # rename type to avoid collision
        $xml->type[$i]->attributes()->name = renameElement($xml->type[$i]->attributes()->name, "type");
        # rename parent type if set 
        if (isset($xml->type[$i]->attributes()->parent)) {
            $xml->type[$i]->attributes()->parent = renameElement($xml->type[$i]->attributes()->parent, "parent");
        }
        foreach($xml->type[$i]->children() as $child => $property) {
            # if there's a link, rename it 
            if (isset($property->attributes()->link)) {
                $property->attributes()->link = renameElement($property->attributes()->link, "link");
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

?>
