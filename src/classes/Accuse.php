<?php

require_once 'vendor/autoload.php'; 


class Accuse{
    
    /**
     * This function accuses a player based on game id and players ids
     *
     * @param  int $gameId
     * @param  int $fromPlayerId 
     * @param  int $toPlayerId
     * @return void
     */
    public static function accusePlayer(int $gameId , int $fromPlayerId, int $toPlayerId)
    {
        $gameInformation = (new Model\GameModel)->getGameInfo($gameId);
        $accuseModel     = (new Model\AccuseModel($gameId))->accuse($gameInformation['phase_count'], $fromPlayerId, $toPlayerId);
        Tools\Logger::info('Player with ID: '.$fromPlayerId.' accused player with ID: '.$toPlayerId);
    }
}