<?php

require_once __DIR__ . "/Setting.php";
require_once __DIR__ . "/UtilityService.php";

class MySqliDBProcess
{

    private $host;
    private $username;
    private $password;
    private $db_name;

    private $conn;

    /**
     * MySqliDBProcess constructor.
     * @param $host
     * @param $username
     * @param $password
     * @param $db_name
     */
    function __construct($host, $username, $password, $db_name)
    {
        if (empty($host)) {
            new Exception('$host is not allow null or empty');
        } else if (empty($username)) {
            new Exception('$username is not allow null or empty');
        } else if (empty($password)) {
            new Exception('$password is not allow null or empty');
        } else if (empty($db_name)) {
            new Exception('$db_name is not allow null or empty');
        }
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->db_name = $db_name;
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        mysqli_set_charset($this->conn, "utf8");
    }

    /**
     *  Close connection
     */
    function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    /**
     *  Use to query DataBase by select sql command
     * @param $selectSql
     * @return bool|mysqli_result|null
     * @throws Exception
     */
    public function query($selectSql)
    {
        // check connection
        if (!isset($this->conn)) {
            $this->getConnection();
        }
        // validate data
        if (empty($selectSql) || !is_string($selectSql)) {
            throw new Exception('$selectSql is a illegal type. it should be not empty and string type.');
        }

        if ($result = $this->conn->query($selectSql)) {
            if ($result->num_rows > 0) {
                $dataSet = array();
                while ($row = $result->fetch_array()) {
                    array_push($dataSet, $row);
                }
                return $dataSet;
            } else {
                return null;
            }
        } else {
            $datetime = UtilityService::getDateTime();
            error_log("{$datetime} Error: " . $selectSql . "\n" . $this->conn->error);
        }
    }

    /**
     * Get a connection
     * @return \mysqli connection
     */
    public function getConnection()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        mysqli_set_charset($this->conn, "utf8");
    }

    /**
     *  Use to
     * @param $sql
     * @return bool
     * @throws Exception
     */
    public function execute($sql)
    {
        // check connection
        if (!isset($this->conn)) {
            $this->getConnection();
        }
        if (empty($sql) || !is_string($sql)) {
            throw new Exception('$sql is a illegal type. it should be not empty and string type.');
        }
        $isSuccess = false;
        if ($this->conn->query($sql)) {
            $isSuccess = true;
        } else {
            $datetime = UtilityService::getDateTime();
            error_log("{$datetime} Error: " . $sql . "\n" . $this->conn->error);
        }
        return $isSuccess;
    }

}

