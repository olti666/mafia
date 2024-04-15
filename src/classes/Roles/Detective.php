<?php

namespace Roles;

require_once 'vendor/autoload.php'; 

class Detective{


    private int $gameId;

    private int $detectiveId;


    public function __construct(int $gameId, int $detectiveId) {
        $this->gameId = $gameId;
        $this->detectiveId = $detectiveId;

    }
    
    /**
     * detective npc uses their special abilities to accuse a mafia
     *
     * @return void
     */
    public function accuseMafia()
    {
        $mafias         = (new \Model\PlayerModel($this->gameId))->getMafia();

        $chosenMafia    = $mafias[array_rand($mafias)];

        \Accuse::accusePlayer($this->gameId, $this->detectiveId, $chosenMafia['id']);
        \Tools\Logger::info('Detective used their skills!');

    }
}

