<?php

namespace Model;

require_once 'vendor/autoload.php'; 

class GameModel{

    private $db;

    public int $gameId;

    
    public function __construct() {
        $this->db = \Database::connect();
    }

    public function insertGame()
    {
        $rand = rand(1,2);

        $query = "INSERT INTO games (state) VALUES ($rand)";
        $result = $this->db->query($query);
        $this->gameId = $this->db->insert_id;

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function isActive(int $gameId)
    {
        $gameInfo = $this->getGameInfo($gameId);
        if($gameInfo['state'] !== 'end')
        {
            return true;
        }else{
            return false;
        }
    }

    public function getGameInfo(int $gameId)
    {
        $query = "SELECT * FROM games WHERE id = '".$gameId."'";
        $result = $this->db->query($query);

        if ($result) {
            return $result->fetch_assoc();
        }else{
            return false;
        }

    }

    public function changePhase(int $gameId)
    {
        $gameInfo = $this->getGameInfo($gameId);
        if($gameInfo['state'] == 'night')
        {
            $newPhase = 'day';
        }else{
            $newPhase = 'night';
        }

        $newPhaseCount = $gameInfo['phase_count'] + 1;

        $query = "UPDATE games SET state = '".$newPhase."', phase_count = '".$newPhaseCount."' WHERE id = '".$gameId."'";
        $result = $this->db->query($query);
    }

    public function finishGame(int $gameId, string $winner)
    {
        $query = "UPDATE games SET winner = '".$winner."', state = 'end' WHERE id = '".$gameId."'";
        $result = $this->db->query($query);
    }

}

