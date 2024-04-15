<?php

require_once 'vendor/autoload.php'; 


class Action{
    
    /**
     * This function gets the actions based on type(vote, accuse)
     *
     * @param  int $gameId
     * @param  string $actionType
     * @return void
     */
    public static function get(int $gameId, string $actionType)
    {
        $gameInformation = (new Model\GameModel)->getGameInfo($gameId);
        if($gameInformation['state'] == 'day')
        {
            if($actionType == 'accuse')
            {
                $accusations     = (new Model\AccuseModel($gameId))->getAccusations($gameInformation['phase_count']);
    
                foreach($accusations as $accusation)
                {
                    $formattedAccuses[] = $accusation['from_name']." accused ".$accusation['to_name'];
                }
                print_r(json_encode(['rows' => $formattedAccuses, 'success' => true]));
            }elseif($actionType == 'vote')
            {
                $votes = (new Model\VoteModel($gameId))->getVotes($gameInformation['phase_count']);

                foreach($votes as $vote)
                {
                    $formattedVotes[] = $vote['from_name']." voted ".$vote['to_name'];
                }

                print_r(json_encode(['rows' => $formattedVotes, 'success' => true]));
            }
        }else{
            print_r(json_encode(['success' => false]));
        }
    }
}