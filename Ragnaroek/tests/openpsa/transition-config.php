<?php

$transition_config = array (

    # Database to transform
    'ratatoskr_db_name' => 'midgard_live',
    'ratatoskr_db_username' => 'midgard',
    'ratatoskr_db_password' => 'midgard',
    'ratatoskr_db_dump_file' => 'midgard_transition_dump.sql', 

    # Temporary database
    'temporary_database_name' => 'midgard_transition',
    'temporary_database_username' => 'midgard',
    'temporary_database_password' => 'midgard',
    
    # Directory with schemas which hold information about types to transform
    'schema_directory_ragnaroek' => '/usr/share/midgard/schema',
    
    # Directory for updated schemas (used for transition only)
    'schema_directory_sql' => __DIR__ . "/data/ragnaroek/schema",

    # Directory for schemas which should be used to generate classes
    'schema_directory_transition' => __DIR__ . "/data/schema",

    # Directory for ratatoskr schemas
    'schema_directory_ratatoskr' => "/usr/share/midgard2/schema",

    # working directory
    'working_directory' => __DIR__
);

?>
