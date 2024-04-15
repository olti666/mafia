<?php

require_once 'vendor/autoload.php'; 


class Vote{
    
    public static function vote(int $gameId , int $fromPlayerId, int $toPlayerId)
    {
      
        $gameInformation = (new Model\GameModel)->getGameInfo($gameId);
        $voteModel       = (new Model\voteModel($gameId))->addVote($gameInformation['phase_count'], $fromPlayerId, $toPlayerId);
        Tools\Logger::info('Player with ID: '.$fromPlayerId.' voted player with ID: '.$toPlayerId);

    }
}