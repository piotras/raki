<?php

if (!isset($argv[1])) {
    throw new Exception("Config file needed as an argument");
}

$configFile = $argv[1];
include $configFile;

if (!isset($transition_config)) {
    throw new Exception("'transition_config' not set");
}

# Load mandatory files

if (!extension_loaded('mysql')) {
    throw new Exception('MySQL extension not loaded!');
}
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/RagnaroekTransitionAbstract.php';

# Create class

class RagnaroekTransition extends RagnaroekTransitionAbstract
{
    public function __construct(array $config) 
    {
        $this->db_live_name = $config['ratatoskr_db_name'];
	    $this->db_live_username = $config['ratatoskr_db_username'];
	    $this->db_live_password = $config['ratatoskr_db_password'];
	    $this->db_live_dump_file = $config['ratatoskr_db_dump_file'];

	    $this->db_tmp_name = $config['temporary_database_name'];
	    $this->db_tmp_username = $config['temporary_database_username'];
	    $this->db_tmp_password = $config['temporary_database_password'];

        $this->schema_ragnaroek_directory = $config['schema_directory_ragnaroek'];
        $this->schema_ratatoskr_directory = $config['schema_directory_transition'];
	    $this->schema_tmp_directory = $config['schema_directory_sql'];

        $this->src_top_dir = $config['working_directory'];
    }

    public function importContent()
    {
        $transition = new \Ragnaroek\Ratatoskr\Transition(MidgardConnection::get_instance(), $this->Midgard2Config, $this->src_top_dir . '/fixtures/', $this->schema_tmp_directory);

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

$transition = new RagnaroekTransition($transition_config);
$transition->execute();

?>
