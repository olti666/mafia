<?php

namespace Model;

require_once 'vendor/autoload.php'; 

class AccuseModel{

    private $db;

    public int $gameId;

    
    public function __construct(int $gameId) {
        $this->db = \Database::connect();
        $this->gameId = $gameId;
    }

    public function accuse(int $phaseCount, int $fromPlayerId, int $toPlayerId)
    {
        $query = "INSERT INTO accusations (game_id, phase_count,from_player_id, to_player_id) VALUES ('$this->gameId', '$phaseCount', '$fromPlayerId', '$toPlayerId')";
        $result = $this->db->query($query);
    }

    public function getAccusedPlayers(int $phaseCount, array $returnArray = null)
    {
       
        $query = "SELECT * FROM accusations WHERE game_id = '".$this->gameId."' AND phase_count = '".$phaseCount."'";
        $result = $this->db->query($query);

        if($returnArray !== null)
        {
            if ($result && $result->num_rows > 0) {
                $rows = array();
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                return $rows;
            } else {
                return null;
            }
        }else{
            return $result->fetch_assoc();
        }
    }

    public function getMostAccusedPlayer(int $phaseCount)
    {
        $query = "SELECT to_player_id, COUNT(to_player_id) AS player_count
        FROM accusations
        WHERE game_id = ".$this->gameId." AND phase_count = ".$phaseCount."
        GROUP BY to_player_id
        HAVING COUNT(to_player_id) = (
            SELECT MAX(player_count)
            FROM (
                SELECT COUNT(to_player_id) AS player_count
                FROM accusations
                WHERE game_id = ".$this->gameId." AND phase_count = ".$phaseCount."
                GROUP BY to_player_id
            ) AS subquery
        );";

        $result = $this->db->query($query);
        if ($result && $result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function getMostAccusedPlayerByMafia(int $phaseCount)
    {
        $query = "SELECT to_player_id, COUNT(*) AS accusation_count
        FROM accusations
        JOIN players ON accusations.from_player_id = players.id
        JOIN roles ON players.role_id = roles.id
        WHERE roles.name = 'mafia' 
        AND accusations.game_id = ".$this->gameId."
        AND accusations.phase_count = ".$phaseCount."
        GROUP BY to_player_id
        ORDER BY accusation_count DESC
        LIMIT 1;";
        $result = $this->db->query($query);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function getAccusations(int $phaseCount)
    {
        
        $query = "SELECT fromPlayer.name as from_name, toPlayer.name as to_name FROM accusations
        INNER JOIN players as fromPlayer ON accusations.from_player_id = fromPlayer.id
        INNER JOIN players as toPlayer ON accusations.to_player_id = toPlayer.id 
        WHERE accusations.game_id =".$this->gameId." AND accusations.phase_count = ".$phaseCount."";

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

