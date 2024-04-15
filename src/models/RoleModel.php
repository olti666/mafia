<?php

namespace Model;

require_once 'vendor/autoload.php'; 

class RoleModel{

    private $db;

    
    public function __construct() {
        $this->db = \Database::connect();
    }

    public function getRoles()
    {
        $query = "SELECT * FROM roles";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $rows = array();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        } else {
            return null;
        }
    }

}

