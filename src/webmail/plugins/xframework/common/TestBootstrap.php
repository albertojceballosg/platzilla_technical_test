<?php
namespace XFramework;

require_once(__DIR__ . "/DatabaseMysql.php");

new TestBootstrap();

class TestBootstrap
{
    public function __construct()
    {
        $_SERVER['SCRIPT_FILENAME'] = realpath(__DIR__ ."/../../../index.php");
        require_once(__DIR__ . "/../../../program/include/iniset.php");
        $this->rcmail = \rcmail::get_instance(0, "test");

        // check if database name ends with test (just to make sure we're not running tests on a production database)
        if (substr($this->rcmail->config->get("db_dsnw"), -4, 4) != "test") {
            exit("\n\nError: The database name specified in config-test.inc.php should end with 'test'.\n\n");
        }

        $this->db = new DatabaseMysql();
        $this->createDatabase();
    }

    protected function createDatabase()
    {
         // drop all database tables
        foreach ($this->db->all("SHOW TABLES") as $table) {
            $table = array_shift(array_values($table));
            $this->db->query("DROP TABLE IF EXISTS $table");
        }

        if (count($this->db->all("SHOW TABLES"))) {
            exit("Error dropping database tables.");
        }

        $host = $this->rcmail->db->db_dsnw_array['hostspec'];
        $username = $this->rcmail->db->db_dsnw_array['username'];
        $password = $this->rcmail->db->db_dsnw_array['password'];
        $database = $this->rcmail->db->db_dsnw_array['database'];
        $filename = __DIR__. "/../../../SQL/mysql.initial.sql";

        // import database (not using -p because we'll get a warning that using password in command line is insecure,
        // we put it in the env variable instead to avoid the warning
        exec("export MYSQL_PWD=$password\nmysql -h$host -u$username $database < $filename", $output, $result);

        if (!count($this->db->all("SHOW TABLES"))) {
            exit("Error creating database tables.");
        }

        // add prefix to all tables if needed
        if ($prefix = $this->rcmail->config->get("db_prefix")) {
            foreach ($this->db->all("SHOW TABLES") as $table) {
                $table = array_shift(array_values($table));
                $this->db->query("RENAME TABLE $table TO $prefix$table");
            }
        }

        $this->db->insert(
            "users",
            [
                "user_id" => 1,
                "username" => "maya",
                "mail_host" => "localhost",
                "created" => date("Y-m-d H:i:s"),
                "last_login" => date("Y-m-d H:i:s"),
                "language" => "en_US",
                "preferences" => "",
            ]
        );

        $this->db->insert(
            "identities",
            [
                "identity_id" => 1,
                "user_id" => 1,
                "changed" => date("Y-m-d H:i:s"),
                "name" => "Maya",
                "email" => "maya@roundcubeplus.com",
            ]
        );
    }
}

