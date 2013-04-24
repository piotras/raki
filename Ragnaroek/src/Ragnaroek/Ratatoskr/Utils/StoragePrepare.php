<?php

class StoragePrepare 
{
    protected $db_live_name = null;
    protected $db_live_username = null;
    protected $db_live_password = null;
    protected $db_live_dump_file = null;

    protected $db_tmp_name = null;
    protected $db_tmp_username = null;
    protected $db_tmp_password = null;

    protected $schema_ragnaroek_directory = null;
    protected $schema_ratatoskr_directory = null;
    protected $schema_tmp_directory = null;

    protected $scr_top_dir = null;

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

    public function validate()
    {
        if ($this->db_live_name == null) {
            throw new Exception("Invalid value");
        }

        if ($this->db_live_username == null) {
            throw new Exception("Invalid value");
        }

        if ($this->db_live_password == null) {
            throw new Exception("Invalid value");
        }

        if ($this->db_live_dump_file == null) {
            throw new Exception("Invalid value");
        }

        if ($this->db_tmp_name == null) {
            throw new Exception("Invalid value");
        }

        if ($this->db_tmp_username == null) {
            throw new Exception("Invalid value");
        }

        if ($this->db_tmp_password == null) {
            throw new Exception("Invalid value");
        }

        if ($this->schema_ragnaroek_directory == null) {
            throw new Exception("Invalid value");
        }

        if ($this->schema_ratatoskr_directory == null) {
            throw new Exception("Invalid value");
        }

        if ($this->schema_tmp_directory == null) {
            throw new Exception("Invalid value");
        }
    
        if ($this->src_top_dir == null) {
            throw new Exception("Invalid value");
        }
    }
   
    public function importContent()
    {
        $transition = new \Ragnaroek\Ratatoskr\Transition(MidgardConnection::get_instance(), $this->Midgard2Config, $this->src_top_dir . '/fixtures/', $this->schema_tmp_directory);

        $workspaceManager = $transition->getWorkspaceManager();
        $workspaceManager->createWorkspacesAll();

        $contentManager = $transition->getContentManager();
        $types = $contentManager->getPossibleTypeNames();
        foreach ($types as $type) {
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

    public function executeAndImport()
    {
        $this->execute();
        $this->importContent();
    }

    public function prepareTransitionDatabase()
    {
        echo "Create temporary database \n";
        exec("sudo mysql -e 'CREATE DATABASE {$this->db_tmp_name} CHARACTER SET utf8'");
        $cmdGrant = "GRANT all ON {$this->db_tmp_name}.*  to '{$this->db_tmp_username}'@'localhost' identified by '{$this->db_tmp_password}'";

        echo "Grant all privileges \n";
        exec("sudo mysql -e \"{$cmdGrant}\"");

        echo "Flush privileges \n";
        exec("sudo mysql -e 'FLUSH PRIVILEGES'");
    }

    public function dumpLiveDatabase()
    {
        echo "Dump live database \n";
        $path = $this->src_top_dir . "/" . $this->db_live_dump_file;
        $cmd = "sudo mysqldump -u {$this->db_live_username} -p{$this->db_live_password} {$this->db_live_name} > $path";
        exec($cmd); 
    }

    public function importDumpedDatabase()
    {
        $path = $this->src_top_dir . "/" . $this->db_live_dump_file;
        $testDB = $this->db_tmp_name;
        echo "Import Sql dump {$path} \n";

        exec("sudo mysql {$testDB} < {$path}", $out, $returnValue);

        if ($returnValue != 0) {
            throw new Exception("Failed to import data from sql file");
        }
    }

    public function prepareMidgard2Connection()
    {
        $this->Midgard2Config = new MidgardConfig();
        $this->Midgard2Config->dbtype = 'MySQL';
        $this->Midgard2Config->database = $this->db_tmp_name;
        $this->Midgard2Config->dbuser = $this->db_tmp_username;
        $this->Midgard2Config->dbpass = $this->db_tmp_password;

        $mgd = midgard_connection::get_instance();
        if ($mgd->open_config ($this->Midgard2Config) == false) {
            throw new Exception("Failed to connect. " . $mgd->get_error_string());
        }
        $mgd->enable_workspace(true);
    }

    public function prepareMidgard2Storage()
    {
        midgard_storage::create_base_storage();
        $re = new ReflectionExtension("midgard2");
        foreach ($re->getClasses() as $refClass) {
            $name = $refClass->getName();
            if (!is_subclass_of ($name, 'MidgardDBObject')
                || $refClass->isAbstract()
                || $refClass->isInterface()) {
                    continue;
                }

            if (strpos($name, "_abstract") != false) {
                continue;
            }

            echo 'midgard_storage: prepare class storage('.$name.")\n";
            midgard_storage::create_class_storage($name);
            midgard_storage::update_class_storage($name);
        }
    }

    public function addSitegroupPropertyToSchema($file)
    {
        $tree = simplexml_load_file($file);
        foreach ($tree->type as $k => $typedef)
        {
            $ch = $typedef->addChild('property');
            $ch->addAttribute('name', 'sitegroup');
            $ch->addAttribute('type', 'unsigned integer');
        }
        $tree->asXML($file);
    }

    protected static function copyXmlFiles($srcDir, $destDir)
    {
        if ($handle = opendir($srcDir)) {
            while (($entry = readdir($handle)) == true) {
                /* Ignore parent and self directory */
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                $absSrcPath = $srcDir . '/' . $entry;
                $absDestPath = $destDir . '/' . $entry;
                $info = pathinfo($absSrcPath);
                /* Ignore non xml files */
                if ($info['extension'] != 'xml') {
                    continue;
                }
                copy($absSrcPath, $absDestPath);
            }
        }
    }

    /*
     * Copy schemas to data/schema directory (These schemas should contain all types you want to convert).
     * Update every type (from copied schemas) so it registers 'sitegroup' property explicitly
     */
    public function copySchemasTypeConvert()
    {
        self::copyXmlFiles($this->schema_ragnaroek_directory, $this->schema_ratatoskr_directory);
    }

    /*
     *  Copy all schemas to data/ragnaroek/schema directory.
     *  Rename types. E.g. add ragnaroek_transition prefix.
     *  (These schemas are required to generate correct SQL queries, and are not used by extension itself)
     */
    public function copySchemasGenerateSQL()
    {
        self::copyXmlFiles($this->schema_ragnaroek_directory, $this->schema_tmp_directory);
    }
}

?>
