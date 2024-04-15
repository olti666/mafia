<?php

namespace Model;

require_once 'vendor/autoload.php'; 

class PlayerModel{

    private $db;
    private int $gameId;
    
    public function __construct(int $gameId) {
        $this->db = \Database::connect();
        $this->gameId = $gameId;
    }

    public function getPlayers()
    {
        $query = "SELECT players.*, roles.name as rolename FROM players INNER JOIN roles on players.role_id = roles.id WHERE players.game_id = '$this->gameId'";
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

    public function insertPlayers(array $players)
    {
        $loopCount = 0;
        foreach($players as $player => $role)
        {
            if($loopCount == 0){
                $query = "INSERT INTO players (game_id, name,role_id, alive, user_id) VALUES ('$this->gameId', '$player', '$role', '1', '".$_SESSION['user_id']."')";
            }else{
                $query = "INSERT INTO players (game_id, name,role_id, alive) VALUES ('$this->gameId', '$player', '$role', '1')";
            }
            $result = $this->db->query($query);
            $loopCount++;
        }
    }

    public function getAlivePlayers()
    {
        $query = "SELECT players.*, roles.name as rolename FROM players INNER JOIN roles on players.role_id = roles.id WHERE players.game_id = '$this->gameId' AND alive = '1'";
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
    
    public function getMafia()
    {
        $query = "SELECT players.*, roles.name as rolename FROM players INNER JOIN roles on players.role_id = roles.id WHERE players.game_id = '$this->gameId' AND alive = '1' AND roles.name = 'mafia'";
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

    public function getAlivePlayerExpect(int $expectId)
    {
        $query = "SELECT players.*, roles.name as rolename FROM players INNER JOIN roles on players.role_id = roles.id WHERE players.game_id = '$this->gameId' AND alive = '1' AND NOT players.id = '".$expectId."'";
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

    public function protect(int $playerId)
    {
        $query = "UPDATE players SET protected = 1 WHERE game_id = '".$this->gameId."' AND id = '".$playerId."'";
        $result = $this->db->query($query);
    }

    public function clearProtect()
    {
        $query = "UPDATE players SET protected = 0 WHERE game_id = '".$this->gameId."'";
        $result = $this->db->query($query);
    }

    public function isProtected(int $playerId)
    {
        $query = "SELECT protected FROM players WHERE game_id = '".$this->gameId."' AND id = ".$playerId."";
        $result = $this->db->query($query);
        
        if($result){
            $row = $result->fetch_assoc();
            if($row['protected'] == 1){
                return true;
            }else{
                return false;
            }
            
        }else{
            return false;
        }
    }

    public function eliminatePlayer(int $playerId)
    {
        $query = "UPDATE players SET alive = 0 WHERE game_id = ".$this->gameId." AND id = ".$playerId."";
        $result = $this->db->query($query);
    }

}

