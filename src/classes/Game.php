<?php

require_once 'vendor/autoload.php';

class Game
{


    private static int $playersCount = 10;

    private static array $players;

    private static array $playersWithRoles;
  
    
    /**
     * start a new game
     *
     * @return void
     */
    public static function newGame()
    {

        $model = new Model\GameModel;

        if ($model->insertGame()) {
            self::generatePlayers($model->gameId);
            Tools\Logger::info('-====Game started ID: ' . $model->gameId . ' ====-');
            return $model->gameId;
        } else {
            return false;
        }
    }
    
    /**
     * generate players based on game id
     *
     * @param  int $gameId
     * @return void
     */
    private static function generatePlayers(int $gameId) 
    {
        try {
            $count = self::$playersCount;
            while ($count > 0) {
                $players[] = uniqid('Player_');
                $count--;
            }

            Tools\Logger::info('Players Generated!');

            self::$players = $players;
            self::assignRoles();

            $playerModel = new Model\PlayerModel($gameId);
            $playerModel->insertPlayers(self::$playersWithRoles);

        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
    
    /**
     * assign roles randomly to the players
     *
     * @return void
     */
    private static function assignRoles() 
    {
        try {
            $rolesModel = new Model\RoleModel();

            $roles = ['mafia', 'mafia', 'mafia', 'doctor', 'detective', 'villager', 'villager', 'villager', 'villager', 'villager'];

            foreach ($roles as $role) {
                foreach ($rolesModel->getRoles() as $roleRow) {
                    if ($role == $roleRow['name']) {
                        $formattedRoles[] = $roleRow['id'];
                    }
                }
            }


            shuffle($formattedRoles);
            shuffle(self::$players);

            self::$playersWithRoles = array_combine(self::$players, $formattedRoles);

            Tools\Logger::info('Roles assigned!');

        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
    
    /**
     * get players that are alive based on game id
     *
     * @param  int $gameId
     * @return void
     */
    public static function getAlivePlayers(int $gameId)
    {
        try {
            $model = new Model\PlayerModel($gameId);
            print_r(json_encode($model->getAlivePlayers()));
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
    
    /**
     * get all players based on game id
     *
     * @param  int $gameId
     * @return void
     */
    public static function getAllPlayers(int $gameId)
    {
        try {
            $model = new Model\PlayerModel($gameId);
            print_r(json_encode($model->getPlayers()));
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
    
    /**
     * check if a game is active
     *
     * @param  mixed $gameId
     * @return boolean
     */
    public static function isActive(int $gameId)
    {
        try {
            $model = new Model\GameModel;
            if ($model->isActive($gameId)) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
    
    /**
     * change the phase of the game (night/day)
     *
     * @param  int $gameId
     * @return void
     */
    public static function changeGamePhase(int $gameId)
    {
        try {
            $model = new Model\GameModel;

            $model->changePhase($gameId);

            $gameInformation = $model->getGameInfo($gameId);

            if ($gameInformation['state'] == 'day') {
                $playerModel = (new Model\PlayerModel($gameId))->clearProtect();
            }

            Tools\Logger::info('Game Phase Changed: ' . $gameInformation['state']);

        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
    
    /**
     * get the current game phase
     *
     * @param  int $gameId
     * @return void
     */
    public static function checkGamePhase(int $gameId)
    {
        $gameInformation = (new Model\GameModel)->getGameInfo($gameId);
        echo json_encode(["phase" => $gameInformation['state']]);
    }

    
    /**
     * check the game if the win conditions are met
     *
     * @param  int $gameId
     * @return void
     */
    public static function checkForWin(int $gameId)
    {
        try {
            $playerModel = new Model\PlayerModel($gameId);

            $gameOver = false;
            if (self::isMafiaDead($playerModel)) {
                $gameOver = true;
                $winner = 'town';
            } elseif (self::mafiaOutnumbersOthers($playerModel)) {
                $gameOver = true;
                $winner = 'mafia';
            }

            if ($gameOver) {
                self::gameOver($gameId, $winner);
                Tools\Logger::info('-====Game Over! Winner: ' . $winner . ' ====-');
                echo json_encode(["success" => true, "winner" => $winner]);

            } else {
                echo json_encode(["success" => false]);
            }

        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
    
    /**
     * check if mafia outnumbers the rest of the roles
     *
     * @param  Model\PlayerModel $playerModel
     * @return boolean
     */
    private static function mafiaOutnumbersOthers(Model\PlayerModel $playerModel)
    {

        $alivePlayers = $playerModel->getAlivePlayers();

        $mafiaCount = 0;
        $othersCount = 0;
        foreach ($alivePlayers as $alivePlayer) {
            if ($alivePlayer['rolename'] == 'mafia') {
                $mafiaCount++;
            } else {
                $othersCount++;
            }
        }

        if ($mafiaCount > $othersCount) {
            return true;
        }
        return false;
    }
    
    /**
     * check if mafia is dead
     *
     * @param  Model\PlayerModel $playerModel
     * @return boolean
     */
    private static function isMafiaDead(Model\PlayerModel $playerModel)
    {

        $alivePlayers = $playerModel->getAlivePlayers();

        $mafiaCount = 0;
        foreach ($alivePlayers as $alivePlayer) {
            if ($alivePlayer['rolename'] == 'mafia') {
                $mafiaCount++;
            }
        }

        if ($mafiaCount == 0) {
            return true;
        }
        return false;
    }
    
    /**
     * set game as over
     *
     * @param  int $gameId
     * @param  string $winner
     * @return void
     */
    private static function gameOver(int $gameId, string $winner)
    {
        $gameModel = new Model\GameModel();
        $gameModel->finishGame($gameId, $winner);

    }





}