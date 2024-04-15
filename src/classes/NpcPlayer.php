<?php

require_once 'vendor/autoload.php';

class NpcPlayer
{

    /**
     * This function is used for NPC to use their special abilities (doctor ->protect player, detective -> accuse mafia)
     *
     * @param  int $gameId
     * @return void
     */
    public static function specialAbilities(int $gameId)
    {
        try {
            $gameInformation = (new Model\GameModel)->getGameInfo($gameId);

            if ($gameInformation['state'] == 'day') {
                $alivePlayers = (new Model\PlayerModel($gameId))->getAlivePlayers();

                foreach ($alivePlayers as $alivePlayer) {
                    if ($alivePlayer['rolename'] == 'detective' && $alivePlayer['user_id'] == null) {
                        $detectiveClass = new Roles\Detective($gameId, $alivePlayer['id']);
                        $detectiveClass->accuseMafia();
                    }

                    if ($alivePlayer['rolename'] == 'doctor' && $alivePlayer['user_id'] == null) {
                        $detectiveClass = new Roles\Doctor($gameId, $alivePlayer['id']);
                        $detectiveClass->protectPlayer();
                    }
                }
            }
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }

    /**
     * This function is used for NPCs to accuse others players
     *
     * @param  int $gameId
     * @return void
     */
    public static function discussing(int $gameId)
    {
        try {
            // Retrieve game information
            $gameInformation = (new Model\GameModel)->getGameInfo($gameId);

            // Retrieve all alive players in the game
            $playerModel = new Model\PlayerModel($gameId);
            $alivePlayers = $playerModel->getAlivePlayers();

            // Shuffle the order of alive players
            shuffle($alivePlayers);

            // Separate alive players into mafia, non-mafia, and all NPCs
            $mafia = [];
            $nonMafia = [];
            $allNPCs = [];

            foreach ($alivePlayers as $alivePlayer) {
                if ($alivePlayer['user_id'] === null) {
                    if ($alivePlayer['rolename'] === 'mafia') {
                        $mafia[] = $alivePlayer;
                    } else {
                        $nonMafia[] = $alivePlayer;
                    }
                }
                $allNPCs[] = $alivePlayer;
            }

            // Mafia accusing logic
            if (!empty($mafia)) {
                // Determine whom the mafia will accuse
                if (empty($nonMafia) && count($alivePlayers) === 2) {
                    foreach ($alivePlayers as $alivePlayer) {
                        if ($alivePlayer['user_id'] !== null) {
                            $accusedByMafia = $alivePlayer;
                        }
                    }
                } else {
                    shuffle($nonMafia);
                    $accusedByMafia = empty($nonMafia) ? null : $nonMafia[array_rand($nonMafia)];
                }

                // Get accused player information
                $accusedPlayer = isset($accusedByMafia) ? (new Model\AccuseModel($gameId))->getAccusedPlayers($gameInformation['phase_count']) : null;

                // Check if mafia accused one of their own
                $mafiaAccused = $accusedPlayer && in_array($accusedPlayer['to_player_id'], array_column($mafia, 'id'));
                $accusePlayerId = $mafiaAccused ? $accusedPlayer['from_player_id'] : ($accusedByMafia ? $accusedByMafia['id'] : null);

                // Perform accusation by mafia members
                foreach ($mafia as $mafiaMember) {
                    if ($accusePlayerId !== null) {
                        Accuse::accusePlayer($gameId, $mafiaMember['id'], $accusePlayerId);
                    }
                }
            }

            // NPC accusing logic during the day phase
            if ($gameInformation['state'] === 'day' && !empty($nonMafia)) {
                shuffle($allNPCs);

                foreach ($nonMafia as $npc) {
                    // Select a random player to accuse
                    $randomAccuse = $allNPCs[array_rand($allNPCs)];

                    // Exclude detectives from accusing other players
                    if ($npc['rolename'] !== 'detective') {
                        $npcAccuseId = isset($accusedPlayer) ? $accusedPlayer['to_player_id'] : $randomAccuse['id'];

                        // Perform accusation by NPCs
                        Accuse::accusePlayer($gameId, $npc['id'], $npcAccuseId);
                    }
                }
            }
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }

    /**
     * This function is used for NPCs to vote other players based on their accusations
     *
     * @param  int $gameId
     * @return void
     */
    public static function voting(int $gameId)
    {
        try {
            // Retrieve game information
            $gameInformation = (new Model\GameModel)->getGameInfo($gameId);

            // Instantiate necessary models
            $accuseModel = new Model\AccuseModel($gameId);
            $playerModel = new Model\PlayerModel($gameId);

            // Voting logic during the day
            if ($gameInformation['state'] == 'day') {
                // Retrieve the most accused player during the day
                $mostAccusedPlayer = $accuseModel->getMostAccusedPlayer($gameInformation['phase_count']);

                if ($mostAccusedPlayer !== null) {
                    // Get all alive players
                    $alivePlayers = $playerModel->getAlivePlayers();

                    // Loop through each alive player
                    foreach ($alivePlayers as $alivePlayer) {
                        // Exclude players controlled by users
                        if ($alivePlayer['user_id'] === null) {
                            // Determine the player to be voted by NPCs
                            $votedPlayerId = ($alivePlayer['id'] == $mostAccusedPlayer['to_player_id']) ?
                                $alivePlayers[array_rand($playerModel->getAlivePlayerExpect($alivePlayer['id']))]['id'] :
                                $mostAccusedPlayer['to_player_id'];

                            // Perform voting by NPCs
                            Vote::vote($gameId, $alivePlayer['id'], $votedPlayerId);
                        }
                    }
                }
            } else {
                // Voting logic during the night (only mafia votes)
                $mostAccusedPlayerByMafia = $accuseModel->getMostAccusedPlayerByMafia($gameInformation['phase_count']);

                if ($mostAccusedPlayerByMafia !== null) {
                    // Get all mafia players
                    foreach ($playerModel->getMafia() as $mafia) {
                        // Perform voting by mafia members
                        Vote::vote($gameId, $mafia['id'], $mostAccusedPlayerByMafia['to_player_id']);
                    }
                }
            }

            // Perform post-vote processing
            self::postVoteProcess($gameId);

            // Log that players have finished voting
            Tools\Logger::info('Players finished voting');
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }

    /**
     * This is function is called to execute/kick player based on votes
     *
     * @param  int $gameId
     * @return void
     */
    private static function postVoteProcess(int $gameId)
    {
        try {
            // Retrieve game information
            $gameInformation = (new Model\GameModel)->getGameInfo($gameId);

            // Instantiate the VoteModel to handle voting
            $voteModel = new Model\VoteModel($gameId);

            // Get the player with the most votes to be eliminated
            $eliminatePlayer = $voteModel->getMostVotedPlayer($gameInformation['phase_count']);

            // If there's a player to eliminate
            if ($eliminatePlayer !== null) {
                // Instantiate the PlayerModel to handle player data
                $playerModel = new Model\PlayerModel($gameId);

                // Check the game state for the elimination process
                if ($gameInformation['state'] == 'night') {
                    // During the night, check if the player is protected by a doctor
                    if (!$playerModel->isProtected($eliminatePlayer['voted_player_id'])) {
                        // If not protected, eliminate the player
                        $playerModel->eliminatePlayer($eliminatePlayer['voted_player_id']);

                        // Log the elimination by mafia
                        Tools\Logger::info('Mafia killed Player ID: ' . $eliminatePlayer['voted_player_id']);
                    } else {
                        // Log that the player was protected and not killed by mafia
                        Tools\Logger::info('Mafia tried to kill but player with ID: ' . $eliminatePlayer['voted_player_id'] . ' was protected!');
                    }
                } else {
                    // During the day, eliminate the player
                    $playerModel->eliminatePlayer($eliminatePlayer['voted_player_id']);

                    // Log the elimination by the town
                    Tools\Logger::info('Town kicked Player ID: ' . $eliminatePlayer['voted_player_id']);
                }
            }
        } catch (Exception $e) {
            Tools\Logger::error($e->getMessage());
        }
    }
}