<?php

require_once 'XmlMidgardObjectWriter.php';
require_once 'RagnaroekPHPCRContentExporter.php';

$config = new MidgardConfig();

$config->dbtype = "MySQL";
$config->database = "midgard_raki";
$config->dbuser = "midgard_raki";
$config->dbpass = "midgard_raki";

$mgd = MidgardConnection::get_instance();
$mgd->open_config($config);

$ce = new RagnaroekPHPCRContentExporter();

$sitegroups = $ce->getSitegroups();
$types = $ce->getStorableTypeNames();

/* Export each sitegroup */
foreach ($sitegroups as $sg) {
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
        echo "Exporting ({$sg->name}) : {$type}. Please wait...\n";
        $ce->exportType($sg, $type);
    }
}

?>
