<?php

namespace Roles;

require_once 'vendor/autoload.php'; 

class Doctor{


    private int $gameId;

    private int $doctorId;


    public function __construct(int $gameId, int $doctorId) {
        $this->gameId = $gameId;
        $this->doctorId = $doctorId;
    }
    
    /**
     * doctor uses their special ability to protect a player
     *
     * @param  int $playerId
     * @return void
     */
    public function protectPlayer($playerId = null)
    {
        $playerModel    = new \Model\PlayerModel($this->gameId);

        if($playerId == null)
        {
            $players        = $playerModel->getAlivePlayerExpect($this->doctorId);

            $protectPlayer  = $players[array_rand($players)];
            $protectThisPlayerId = $protectPlayer['id'];
            \Tools\Logger::info('Doctor used their skills!');
        }else{
            $protectThisPlayerId = $playerId;
        }
    
        $playerModel->protect($protectThisPlayerId);
    }

}

