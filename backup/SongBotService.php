<?php
require_once __DIR__ . "/MySqliDBProcess.php";

/**
 * Created by PhpStorm.
 * User: Master
 * Date: 2017/5/9
 * Time: 上午 02:03
 */
class SongBotService
{
    private $userId;
    private $db_host;
    private $db_name;
    private $password;
    private $username;

    function __construct($userId)
    {
        $this->userId = $userId;
        $setting = Setting::geSetting();
        $this->db_host = $setting["Setting"]["DataBase"]["db_host"];
        $this->db_name = $setting["Setting"]["DataBase"]["db_name"];
        $this->username = $setting["Setting"]["DataBase"]["username"];
        $this->password = $setting["Setting"]["DataBase"]["password"];
    }

    public function removeSession($sessionKey)
    {
        $isSuccess = false;
        $sessionKey = addslashes($sessionKey);
        $dbProcess = new MySqliDBProcess($this->db_host, $this->username, $this->password, $this->db_name);
        $result = $dbProcess->execute("DELETE FROM `SongBotSession` WHERE `UserID` = '{$this->userId}' AND `SessionKey`='{$sessionKey}'");
        if ($result)
            $isSuccess = true;
        return $isSuccess;
    }

    public function setSession($sessionKey, $sessionValue)
    {
        $isSuccess = false;
        $sessionKey = addslashes($sessionKey);
        $sessionValue = addslashes($sessionValue);
        $dbProcess = new MySqliDBProcess($this->db_host, $this->username, $this->password, $this->db_name);
        $result = $dbProcess->execute("INSERT INTO `SongBotSession`(`UserID`, `SessionKey`, `SessionValue`) VALUES ('{$this->userId}','{$sessionKey}','{$sessionValue}')");
        if ($result)
            $isSuccess = true;
        return $isSuccess;
    }

    public function getSession($sessionKey)
    {
        $sessionKey = addslashes($sessionKey);
        $dbProcess = new MySqliDBProcess($this->db_host, $this->username, $this->password, $this->db_name);
        $dataSet = $dbProcess->query("SELECT * FROM `SongBotSession` WHERE `SessionKey`='{$sessionKey}' AND `UserID` = '{$this->userId}'");
        if (!isset($dataSet)) {
            return $dataSet[0][0];
        } else {
            return null;
        }
    }

    /**
     * @param $trigger
     * @return bool|mysqli_result|null
     */
    public function getSentence($trigger)
    {
        $trigger = addslashes($trigger);
        $dbProcess = new MySqliDBProcess($this->db_host, $this->username, $this->password, $this->db_name);
        $dataSet = $dbProcess->query("SELECT * FROM `tb_songbot` WHERE `fld_trigger`='{$trigger}' ");

        if (!isset($dataSet)) {
            return $dataSet;
        } else {
            return null;
        }
    }

    public function addNewSentence($fld_trigger, $fld_sentence, $fld_enable, $fld_type, $fld_userID)
    {
        $isSuccess = false;
        $fld_trigger = addslashes($fld_trigger);
        $fld_sentence = addslashes($fld_sentence);
        $fld_enable = addslashes($fld_enable);
        $fld_type = addslashes($fld_type);
        $fld_userID = addslashes($fld_userID);

        $dbProcess = new MySqliDBProcess($this->db_host, $this->username, $this->password, $this->db_name);
        $result = $dbProcess->execute("INSERT INTO `tb_songbot`(`fld_trigger`, `fld_sentence`, `fld_enable`, `fld_type`, `fld_userID`) VALUES ('{$fld_trigger}','{$fld_sentence}','{$fld_enable}','{$fld_type}','{$fld_userID}')");
        if ($result === true)
            $isSuccess = true;

        return $isSuccess;
    }

    /**
     * get sentence amount
     * @return mixed rowNum
     */
    public function getSongSentenceRow()
    {
        $dbProcess = new MySqliDBProcess($this->db_host, $this->username, $this->password, $this->db_name);
        $dataSet = $dbProcess->query("SELECT Count(*) AS rowNum FROM `tb_songbot`");

        if (!isset($dataSet)) {
            error_log("~~~");
            return $dataSet[0][0]["rowNum"];

        } else {
            return null;
        }
    }

    public function getPageData($pageNumber)
    {
        $rowsAmount = $this-> getSongSentenceRow();
        if ($pageNumber <= 0 || $rowsAmount < $pageNumber) {
            return -1;
        } else {
            $rowStart = $pageNumber * 10 - 10;
            $rowEnd = $pageNumber * 10;
            $dbProcess = new MySqliDBProcess($this->db_host, $this->username, $this->password, $this->db_name);
            $dataSet = $dbProcess->query(
                "SELECT @rowNum := @rowNum+1 AS rowNum, `fld_trigger`,`fld_sentence` FROM tb_songbot, (@rowNum = 0) AS row 
                 WHERE {$rowStart} <= numRow AND {$rowStart} <= {$rowEnd}"
            );
            if (!isset($dataSet)) {
                return $dataSet[0];
            } else {
                return null;
            }
            return $result;
        }

    }
}