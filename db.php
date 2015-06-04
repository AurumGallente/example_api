<?php

$username = "root";
$password = "";
$hostname = "localhost";
$db = 'example_api';
$mysqli = new mysqli($hostname, $username, $password, $db);

class db_user {

    private $mysqli;
    protected static $_instance;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function close_connection() {
        $this->mysqli->close();
    }

    private function __construct() {
        $username = "root";
        $password = "";
        $hostname = "localhost";
        $db = 'example_api';
        $this->mysqli = new mysqli($hostname, $username, $password, $db);
    }

    public function getOne($id) {
        $user_id = $id * 1;
        $result = $this->mysqli->query("SELECT * FROM  `user` WHERE  `id` =$user_id");
        $row = $result->fetch_assoc();
        return $row;
    }

    public function getAll() {
        $result = $this->mysqli->query("SELECT * FROM  `user`");
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function insert($user) {
        $name = $this->mysqli->real_escape_string($user->name);
        $email = isset($user->email) ? '"' . $this->mysqli->real_escape_string($user->email) . '"' : "NULL";
        $email = ($email == 'NULL') ? "NULL" : '"' . $email . '"';
        $phone = isset($user->phone) ? (int) $this->mysqli->real_escape_string($user->phone) : "NULL";
        $this->mysqli->query("INSERT INTO `user`(`name`, `email`, `phone`) VALUES ('$name',$email,$phone)");
        $inserted_id = $this->mysqli->insert_id;
        if ($inserted_id == 0) {
            return false;
        }
        $result = $this->mysqli->query("SELECT * FROM  `user` WHERE  `id` =$inserted_id");
        $row = $result->fetch_assoc();
        return $row;
    }

    public function update($id, $user) {
        $user_id = (int) $id;
        $name = $this->mysqli->real_escape_string($user->name);
        $email = isset($user->email) ? '"' . $this->mysqli->real_escape_string($user->email) . '"' : "NULL";
        $email = ($email == 'NULL') ? "NULL" : '"' . $email . '"';
        $phone = isset($user->phone) ? (int) $this->mysqli->real_escape_string($user->phone) : "NULL";
        $this->mysqli->query("UPDATE  `example_api`.`user` SET  `name` =  '$name',`email` =  $email, `phone` =  $phone WHERE  `user`.`id` =$user_id;");
        if (mysqli_error($this->mysqli)) {
            return false;
        } else {
            $result = $this->mysqli->query("SELECT * FROM  `user` WHERE  `id` =$user_id");
            $row = $result->fetch_assoc();
            return $row;
        }
    }

    public function delete($id) {
        $user_id = (int) $id;
        $this->mysqli->query("DELETE FROM  `user` WHERE id =$user_id");
    }

    public function login($name) {
        $user_name = $this->mysqli->real_escape_string($name);
        $result = $this->mysqli->query("SELECT * FROM  `user` WHERE  `name` ='$user_name'");
        $row = $result->fetch_assoc();
        return $row;
    }
    
    public function log() {
        $date = date("Y-m-d");
        $ip = $_SERVER['REMOTE_ADDR'];
        $result = $this->mysqli->query("SELECT * FROM  `log` WHERE  `date` ='$date' AND `ip` = '$ip'");
        $row = $result->fetch_assoc();        
        if (!$row) {
            $this->mysqli->query("INSERT INTO  `example_api`.`log` (`ip` ,`date`)VALUES ('$ip',  '$date')");
        } else {
            $count = $row['count']+1;
            $this->mysqli->query("UPDATE `example_api`.`log` SET  `count` = $count WHERE  `log`.`ip` =  '$ip' AND  `log`.`date` =  '$date'  LIMIT 1 ;");
        }
    }

}
