<?php

require __DIR__ . '/../../../vendor/autoload.php';

$config = new MidgardConfig();

$config->dbtype = "MySQL";
$config->database = "midgard_raki";
$config->dbuser = "midgard_raki";
$config->dbpass = "midgard_raki";

$mgd = MidgardConnection::get_instance();
$mgd->open_config($config);

$ce = new \Ragnaroek\PHPCR\ContentExporter();

$workspaces = $ce->getWorkspaces();
$types = $ce->getStorableTypeNames();

/* Export each sitegroup */
foreach ($workspaces as $ws) {
    /* Export each type in a sitegroup */
    foreach($types as $type) {
        if (MidgardReflectorObject::is_mixin($type)
            || MidgardReflectorObject::is_interface($type)
            || MidgardReflectorObject::is_abstract($type)) {
                continue;
            }
        $refClass = new ReflectionClass($type);
        if ($refClass->isAbstract() || $refClass->isInterface()) {
            continue;
        }
        echo "Exporting ({$ws->name}) : {$type}. Please wait...\n";
        $ce->exportType($ws, $type);
    }
}

?>
