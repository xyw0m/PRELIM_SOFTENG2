<?php

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'school_newspaper';

    public $link;

    public function __construct() {
        // Attempt to connect to the database
        $this->link = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        // Check connection
        if ($this->link->connect_error) {
            die("Connection failed: " . $this->link->connect_error);
        }
    }
}
