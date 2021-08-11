<?php
// Wrapper class for the mysqli
class DBConnection {
    private $conn;
    // Need to change this based on the db we are using.
    private $servername = "localhost"; 
    private $username = "root";
    private $password = "";
    private $dbname = "hso";

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            // output the error to the log file.
            $of = fopen("db_output.txt", 'a');
            fwrite($of, date("Y-m-d h:i:sa") . ": Failed to connect to $this->servername, $this->dbname! \n");
            fclose($of);
            throw new Exception("DB failed to connect");
        }
    }

    public function query($sql_query) {
        return $this->conn->query($sql_query);
    }

    public function getInfo()  {
        return $this->servername . ", " . $this->dbname;
    }
}