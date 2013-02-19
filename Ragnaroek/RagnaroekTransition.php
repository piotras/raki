<?php

# Database to transform
$DB_LIVE_NAME='midgard';
$DB_LIVE_USERNAME='midgard';
$DB_LIVE_PASSWORD='midgard';
$DB_LIVE_DUMP_FILE='midgard.sql';

# Temporary database
$DB_TMP_NAME='midgard_raki';
$DB_TMP_USERNAME='midgard_raki';
$DB_TMP_PASSWORD='midgard_raki';

# Directory with schemas which hold information about types to transform
$SCHEMA_LIVE_DIRECTORY='/usr/share/midgard/schema';

# Directory for updated schemas (used for transition only)
$SCHEMA_TMP_DIRECTORY=__DIR__ . "/data/ragnaroek/schema";

# working directory
$SRC_TOP_DIR = __DIR__;

# Load mandatory files

if (!extension_loaded('mysql')) {
    throw new Exception('MySQL extension not loaded!');
}
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/RagnaroekTransitionAbstract.php';

# Create class

class RagnaroekTransition extends RagnaroekTransitionAbstract
{
    public function __construct($db_live_name, $db_live_username, $db_live_password, $db_live_dump_file, $db_tmp_name, $db_tmp_username, $db_tmp_password, $schema_live_directory, $schema_tmp_directory, $src_top_dir) 
    {
        $this->db_live_name = $db_live_name;
	    $this->db_live_username = $db_live_username;
	    $this->db_live_password = $db_live_password;
	    $this->db_live_dump_file = $db_live_dump_file;

	    $this->db_tmp_name = $db_tmp_name;
	    $this->db_tmp_username = $db_tmp_username;
	    $this->db_tmp_password = $db_tmp_password;

	    $this->schema_live_directory = $schema_live_directory;
	    $this->schema_tmp_directory = $schema_tmp_directory;

        $this->src_top_dir = $src_top_dir;
    }

    public function importContent()
    {
        $transition = new \Ragnaroek\Ratatoskr\Transition(MidgardConnection::get_instance(), $this->Midgard2Config, __DIR__ . '/fixtures/', __DIR__ . '/data/ragnaroek/schema');

        $workspaceManager = $transition->getWorkspaceManager();
        $workspaceManager->createWorkspacesAll();

        $contentManager = $transition->getContentManager();
        $types = $contentManager->getPossibleTypeNames();
        foreach ($types as $type) {
            echo "Import '{$type}' content \n";
            $contentManager->importType($type);
        }
    }

    public function execute()
    {
        $this->validate();
        $this->prepareTransitionDatabase();
        $this->dumpLiveDatabase();
        $this->importDumpedDatabase();
        $this->prepareMidgard2Connection();
        $this->prepareMidgard2Storage();
        $this->importContent();
    }
}

# Run transition

$transition = new RagnaroekTransition(
	$DB_LIVE_NAME,
    $DB_LIVE_USERNAME,
    $DB_LIVE_PASSWORD,
    $DB_LIVE_DUMP_FILE,
    $DB_TMP_NAME,
    $DB_TMP_USERNAME,
    $DB_TMP_PASSWORD,
    $SCHEMA_LIVE_DIRECTORY,
    $SCHEMA_TMP_DIRECTORY,
    $SRC_TOP_DIR
    );
$transition->execute();

?>
