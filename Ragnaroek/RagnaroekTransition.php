<?php

if (!isset($argv[1])) {
    throw new Exception("Config file needed as an argument");
}

$configFile = $argv[1];
include $configFile;

if (!isset($transition_config)) {
    throw new Exception("'transition_config' not set");
}

# Set variables from given configuration

# Database to transform
$DB_LIVE_NAME=$transition_config['ratatoskr_db_name'];
$DB_LIVE_USERNAME=$transition_config['ratatoskr_db_username'];
$DB_LIVE_PASSWORD=$transition_config['ratatoskr_db_password'];
$DB_LIVE_DUMP_FILE=$transition_config['ratatoskr_db_dump_file'];

# Temporary database
$DB_TMP_NAME=$transition_config['temporary_database_name'];
$DB_TMP_USERNAME=$transition_config['temporary_database_username'];
$DB_TMP_PASSWORD=$transition_config['temporary_database_password'];

# Directory with schemas which hold information about types to transform
$SCHEMA_RAGNAROEK_DIRECTORY=$transition_config['schema_directory_ragnaroek'];

# Directory for updated schemas (used for transition only)
$SCHEMA_TMP_DIRECTORY=__DIR__ . $transition_config['schema_directory_sql']; 

# Directory for schemas which should be used to generate classes
$SCHEMA_RATATOSKR_DIRECTORY=__DIR__ . $transition_config['schema_directory_transition'];

# working directory
$SRC_TOP_DIR = $transition_config['working_directory'];

# Load mandatory files

if (!extension_loaded('mysql')) {
    throw new Exception('MySQL extension not loaded!');
}
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/RagnaroekTransitionAbstract.php';

# Create class

class RagnaroekTransition extends RagnaroekTransitionAbstract
{
    public function __construct($db_live_name, $db_live_username, $db_live_password, $db_live_dump_file, $db_tmp_name, $db_tmp_username, $db_tmp_password, $schema_ragnaroek_directory, $schema_ratatoskr_directory, $schema_tmp_directory, $src_top_dir) 
    {
        $this->db_live_name = $db_live_name;
	    $this->db_live_username = $db_live_username;
	    $this->db_live_password = $db_live_password;
	    $this->db_live_dump_file = $db_live_dump_file;

	    $this->db_tmp_name = $db_tmp_name;
	    $this->db_tmp_username = $db_tmp_username;
	    $this->db_tmp_password = $db_tmp_password;

        $this->schema_ragnaroek_directory = $schema_ragnaroek_directory;
        $this->schema_ratatoskr_directory = $schema_ratatoskr_directory;
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
    $SCHEMA_RAGNAROEK_DIRECTORY,
    $SCHEMA_RATATOSKR_DIRECTORY,
    $SCHEMA_TMP_DIRECTORY,
    $SRC_TOP_DIR
    );
$transition->execute();

?>
