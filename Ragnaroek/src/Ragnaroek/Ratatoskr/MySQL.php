<?php

namespace Ragnaroek\Ratatoskr;

class MySQL 
{
    private $cnc = null;
    private $result = null;

    public function __construct($host, $dbname, $dbuser, $dbpass)
    {
        $this->cnc = mysql_connect($host, $dbuser, $dbpass)
            or die('Could not connect to host: ' . mysql_error());

       mysql_select_db($dbname) or die("Could not select {$dbname} database"); 
    }

    public function query($sql)
    {
        if ($sql === null) {
            return;
        }

        if ($this->result != null && (!is_bool($this->result))) {
            mysql_free_result($this->result);
        }

        $this->result = mysql_query($sql);
        if ($this->result === false) {
            $this->result = null;
            throw new \Exception("Query failed: " . mysql_error() . "\n {$sql} \n");
        }
    }

    public function getQueryResult()
    {
        return mysql_fetch_array($this->result); 
    }
}

?>
