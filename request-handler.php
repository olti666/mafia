<?php

require_once 'vendor/autoload.php';

if ($_POST['action'] == 'getAllPlayers') {
    Game::getAllPlayers($_POST['gameId']);
}

if ($_POST['action'] == 'changeGamePhase') {
    Game::changeGamePhase($_POST['gameId']);
}

if ($_POST['action'] == 'specialAbilities') {
    echo NpcPlayer::specialAbilities($_POST['gameId']);
}

if ($_POST['action'] == 'discussing') {
    echo NpcPlayer::discussing($_POST['gameId']);
}

if ($_POST['action'] == 'voting') {
    echo NpcPlayer::voting($_POST['gameId']);
}

if ($_POST['action'] == 'checkWin') {
    echo Game::checkForWin($_POST['gameId']);
}

if ($_POST['action'] == 'updateActions') {
    echo Action::get($_POST['gameId'], $_POST['type']);
}

if ($_POST['action'] == 'checkGamePhase') {
    echo Game::checkGamePhase($_POST['gameId']);
}

if ($_POST['action'] == 'protectPlayer') {
    $playerModel = (new Model\PlayerModel($_POST['gameId']))->clearProtect();
    $doctorClass = new Roles\Doctor($_POST['gameId'], $_POST['doctorId']);
    $doctorClass->protectPlayer($_POST['playerId']);
}

if ($_POST['action'] == 'accusePlayer') {
    Accuse::accusePlayer($_POST['gameId'], $_POST['fromPlayerId'], $_POST['toPlayerId']);
}

if ($_POST['action'] == 'votePlayer') {
    Vote::vote($_POST['gameId'], $_POST['fromPlayerId'], $_POST['toPlayerId']);
}