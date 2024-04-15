<?php

namespace Model;

require_once 'vendor/autoload.php'; 

class VoteModel{

    private $db;

    private int $gameId;
    
    public function __construct(int $gameId) {
        $this->db = \Database::connect();
        $this->gameId = $gameId;
    }

    public function addVote(int $phaseCount, int $fromPlayerId, int $toPlayerId)
    {
        $query = "INSERT INTO votes (game_id, phase_count,voter_player_id, voted_player_id) VALUES ('$this->gameId', '$phaseCount', '$fromPlayerId', '$toPlayerId')";
        $result = $this->db->query($query);
    }

    public function getMostVotedPlayer(int $phaseCount)
    {
        $query = "SELECT voted_player_id, COUNT(voted_player_id) AS player_count
        FROM votes
        WHERE game_id = ".$this->gameId." AND phase_count = ".$phaseCount."
        GROUP BY voted_player_id
        HAVING COUNT(voted_player_id) = (
            SELECT MAX(player_count)
            FROM (
                SELECT COUNT(voted_player_id) AS player_count
                FROM votes
                WHERE game_id = ".$this->gameId." AND phase_count = ".$phaseCount."
                GROUP BY voted_player_id
            ) AS subquery
        );";

        $result = $this->db->query($query);
        if ($result && $result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function getVotes(int $phaseCount)
    {
        $query = "SELECT fromPlayer.name as from_name, toPlayer.name as to_name FROM votes
        INNER JOIN players as fromPlayer ON votes.voter_player_id = fromPlayer.id
        INNER JOIN players as toPlayer ON votes.voted_player_id = toPlayer.id 
        WHERE votes.game_id =".$this->gameId." AND votes.phase_count = ".$phaseCount."";

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

