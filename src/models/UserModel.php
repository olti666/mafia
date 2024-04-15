<?php

namespace Model;

require_once 'vendor/autoload.php'; 

class UserModel{

    private $db;

    
    public function __construct() {
        $this->db = \Database::connect();
    }

    public function loggedUser()
    {
        $query = "SELECT id, name, email FROM users WHERE id = '".$_SESSION['user_id']."'";
        $result = $this->db->query($query);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }else{
            return false;
        }
    }

    public function retrieveUserByEmail(string $email)
    {
        $escapedEmail = $this->db->real_escape_string($email);

        $query = "SELECT * FROM users WHERE email = '$escapedEmail'";
        $result = $this->db->query($query);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }

    }

    public function addUser(string $name, string $hashedPassword, string $email)
    {
        $query = "INSERT INTO users (name, password, email) VALUES ('$name', '$hashedPassword', '$email')";
        $result = $this->db->query($query);

        if ($result) {
            return true; 
        } else {
            return false;
        }

    }


}

