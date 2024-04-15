<?php

require_once 'vendor/autoload.php';

// Turn off error display
ini_set('display_errors', 0);

// Log errors to a file
ini_set('log_errors', 1);

// Specify the error log file path
ini_set('error_log', 'logs/app_logs.log');

$userAuthInstance = new UserAuth;

if (!$userAuthInstance->getLoggedUser()) {
    header('location:login.php');
}

if (isset($_GET['game_id'])) {

    if (!Game::isActive($_GET['game_id'])) {
        echo 'This game session is over!';
        die();
    }

    $playerModel = new Model\PlayerModel($_GET['game_id']);
    $currentPlayers = $playerModel->getPlayers();

    foreach ($currentPlayers as $currentPlayer) {
        if ($currentPlayer['user_id'] == $_SESSION['user_id']) {
            $realPlayer = $currentPlayer;
        }
    }

    if (!isset($realPlayer)) {
        echo 'This is not your game!';
        die();
    }

} else {
    $gameId = Game::newGame();
    if (is_numeric($gameId)) {

        header('location:game.php?game_id=' . $gameId);
    }
    die();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mafia Game</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" rel="noopener" target="_blank" href="/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <div class="container">

        <h1 class="text-center" id="game-state"></h1>
        <div class="row mt-5">
            <div class="col-12 text-center">
                <?php if (isset($realPlayer))
                ?>Your Player name is: <strong><?php echo $realPlayer['name']
                    ?></strong>. Your role is: <strong><?php echo $realPlayer['rolename']
                    ?></strong>
            </div>
        </div>

        <div class="row players mt-5">

        </div>

        <h3 class="text-center mt-5 mb-4">GAME UPDATES</h3>
        <div class="game-updates"></div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="gameOverModal" tabindex="-1" role="dialog" aria-labelledby="gameOverModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

    <script>

        // When the document is ready, adjust players and the game phase
        $(document).ready(function () {
            adjustPlayers(); // Adjust players based on game state
            adjustPhase(); // Adjust the current phase of the game
        });

        // Get the modal body element
        var modalBody = document.querySelector('.modal-body');

        // Initialize the current game phase
        var currentPhase = 1;

        // Function to adjust players based on the game state
        function adjustPlayers() {
            // Get the current game phase
            currentGamePhase = getGamePhase();

            // Send an AJAX request to retrieve all players
            $.ajax({
                url: 'request-handler.php',
                type: 'POST',
                data: {
                    action: 'getAllPlayers',
                    gameId: <?php echo $_GET['game_id'] ?>
                },
                success: function (response) { // Handle the response
                    var responseObject = JSON.parse(response);
                    $('.players').empty(); // Clear the player container

                    // Loop through each player object
                    responseObject.forEach(function (player) {
                        // Create a new div element for the player
                        var newDiv = document.createElement('div');
                        newDiv.className = 'col-md text-center';

                        // Create an image element for the player
                        var img = document.createElement('img');

                        // Set the image source based on player status
                        if (player.alive == 1) {
                            if ('<?php echo $realPlayer['user_id'] ?>' == player.user_id) {
                                img.src = 'assets/images/real-player.png'; // Set image for real player
                            } else {
                                img.src = 'assets/images/player.png'; // Set image for other players
                            }
                        } else {
                            // Handle eliminated players
                            if (player.user_id == '<?php echo $realPlayer['user_id'] ?>') {
                                // Display modal message for eliminated real player
                                clearIntervals();
                                displayEliminationModal();
                            }
                            img.src = 'assets/images/eliminated-player.png'; // Set image for eliminated players
                        }

                        // Append the image to the player div
                        newDiv.appendChild(img);

                        // Add detective or mafia tags based on real player role
                        addRoleTags(newDiv, player);

                        // Add player name to the player div
                        addPlayerName(newDiv, player);

                        // Add buttons for actions based on game phase and player role
                        addActionButtons(newDiv, player);

                        // Append the player div to the container
                        var container = document.querySelector('.players');
                        container.appendChild(newDiv);
                    });
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Function to handle transition to the next phase
        function transitionToNextPhase() {
            currentPhase++; // Increment the current phase
            disableAllButtons(); // Disable all action buttons
            if (currentPhase !== 4) {
                adjustPlayers(); // Adjust players for the next phase
            }
        }

        // Function to disable all action buttons
        function disableAllButtons() {
            var buttons = document.querySelectorAll('button');
            buttons.forEach(function (button) {
                button.disabled = true; // Disable each button
            });
        }

        // Function to adjust the game phase
        function adjustPhase() {
            return new Promise(function (resolve, reject) {
                // Send an AJAX request to check the current game phase
                $.ajax({
                    url: 'request-handler.php',
                    type: 'POST',
                    data: {
                        action: 'checkGamePhase',
                        gameId: <?php echo $_GET['game_id'] ?>
                    },
                    dataType: 'json',
                    success: function (response) {
                        document.getElementById('game-state').innerHTML = "Phase: " + response.phase;
                        currentGamePhase = response.phase; // Update the current game phase
                        resolve(response); // Resolve the promise with the response data
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        reject(error); // Reject the promise with the error
                    }
                });
            });
        }

        // Function to retrieve the current game phase
        function getGamePhase() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'request-handler.php', false); // Synchronous request
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('action=checkGamePhase&gameId=<?php echo $_GET['game_id'] ?>');

            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText); // Parse the JSON response
                return response.phase; // Return the phase from the parsed JSON object
            } else {
                console.error('Error:', xhr.statusText);
                return null;
            }
        }

        // Function to display elimination modal for real player
        function displayEliminationModal() {
            // Create message and button elements
            var paragraph = document.createElement('h4');
            paragraph.classList.add('text-danger');
            paragraph.textContent = 'You have been killed!';
            var button = document.createElement('button');
            button.classList.add('btn', 'btn-danger', 'text-center');
            button.textContent = 'Play Again!';

            // Add event listener to button to redirect to index page
            button.addEventListener('click', function () {
                window.location.href = 'index.php';
            });

            // Append message and button to modal body
            modalBody.appendChild(paragraph);
            modalBody.appendChild(button);

            // Show the modal
            $('#gameOverModal').modal({ backdrop: 'static', keyboard: false });
            $('#gameOverModal').modal({ show: true });
        }

        // Function to add detective or mafia tags based on real player role
        function addRoleTags(container, player) {
            // Check if real player role is detective
            if ('<?php echo $realPlayer['rolename'] ?>' == 'detective') {
                var detectiveTag = document.createElement('p');
                if (player.rolename == 'mafia') {
                    detectiveTag.className = 'text-danger';
                } else if (player.rolename == 'doctor') {
                    detectiveTag.className = 'text-success';
                }
                detectiveTag.textContent = player.rolename;
                container.appendChild(detectiveTag);
            }

            // Check if real player role is mafia
            if ('<?php echo $realPlayer['rolename'] ?>' == 'mafia') {
                var mafiaTag = document.createElement('p');
                if (player.rolename == 'mafia') {
                    mafiaTag.className = 'text-danger';
                    mafiaTag.textContent = player.rolename;
                    container.appendChild(mafiaTag);
                }
            }
        }

        // Function to add player name to the player div
        function addPlayerName(container, player) {
            var playerNames = document.createElement('p');
            playerNames.textContent = player.name;
            playerNames.style.fontSize = '9px';
            playerNames.style.fontWeight = 'bold';
            playerNames.className = 'mt-2';
            container.appendChild(playerNames);
        }

        // Function to add action buttons based on game phase and player role
        function addActionButtons(container, player) {
            var currentGamePhase = getGamePhase();
            var realPlayerRoleId = '<?php echo $realPlayer['rolename'] ?>';
            var realPlayerUserId = '<?php echo $realPlayer['user_id'] ?>';

            // Check if it's the real player's turn to take actions
            if ((currentGamePhase === 'day' && realPlayerRoleId !== 'mafia') ||
                (currentGamePhase === 'night' && realPlayerRoleId === 'mafia')) {
                // Check if the player is not the real player and is alive
                if (realPlayerUserId !== player.user_id && player.alive == 1) {
                    var button = document.createElement('button');
                    button.className = 'btn btn-secondary mt-2';

                    // Set button text and onclick event based on game phase and player role
                    if (currentGamePhase === 'day') {
                        if (realPlayerRoleId === 'doctor') {
                            if (currentPhase === 1) {
                                button.textContent = 'Protect';
                                button.onclick = function () {
                                    protectPlayer(player.id);
                                    transitionToNextPhase();
                                };
                            } else if (currentPhase === 2) {
                                button.textContent = 'Accuse';
                                button.onclick = function () {
                                    accusePlayer(player.id);
                                    transitionToNextPhase();
                                };
                            } else if (currentPhase === 3) {
                                button.textContent = 'Vote';
                                button.onclick = function () {
                                    votePlayer(player.id);
                                    transitionToNextPhase();
                                };
                            }
                        } else {
                            if (currentPhase === 1) {
                                button.textContent = 'Accuse';
                                button.onclick = function () {
                                    accusePlayer(player.id);
                                    transitionToNextPhase();
                                };
                            } else if (currentPhase === 2) {
                                button.textContent = 'Vote';
                                button.onclick = function () {
                                    votePlayer(player.id);
                                    disableAllButtons();
                                };
                            }
                        }
                    } else if (currentGamePhase === 'night') {
                        if (currentPhase === 1) {
                            button.textContent = 'Accuse';
                            button.onclick = function () {
                                accusePlayer(player.id);
                                transitionToNextPhase();
                            };
                        } else if (currentPhase === 2) {
                            button.textContent = 'Vote';
                            button.onclick = function () {
                                votePlayer(player.id);
                                disableAllButtons();
                            };
                        }
                    }
                    // Append button to the player div
                    container.appendChild(button);
                }
            }
        }

        // Function to change the game phase
        function changePhase() {
            // Send an AJAX request to change the game phase
            $.ajax({
                url: 'request-handler.php', // URL of the AJAX handler
                type: 'POST', // HTTP method
                data: { // Data to send to the server
                    action: 'changeGamePhase', // Action to identify the function to call
                    gameId: <?php echo $_GET['game_id'] ?>
                },
                success: function (response) { // Handle the response
                    // Chain asynchronous functions to handle game phases
                    adjustPhase().then(adjustPlayers)
                        .then(specialAbilities)
                        .then(function () {
                            return new Promise(function (resolve) {
                                setTimeout(function () {
                                    resolve();
                                }, 3000);
                            });
                        })
                        .then(discussing)
                        .then(voting)
                        .then(function () {
                            return updateActions('vote');
                        })
                        .then(function () {
                            currentPhase = 1; // Reset current phase
                        })
                        .catch(function (error) {
                            console.error("Error:", error);
                        });
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Set intervals to change game phase and check for win condition
        var changePhaseInterval = setInterval(changePhase, 7000);
        var checkWinInterval = setInterval(checkWin, 1000);

        // Function to check for win condition
        function checkWin() {
            // Send an AJAX request to check for win condition
            $.ajax({
                url: 'request-handler.php',
                type: 'POST',
                data: {
                    action: 'checkWin',
                    gameId: <?php echo $_GET['game_id'] ?>
                },
                dataType: 'json',
                success: function (response) {
                    // If there's a winner, display modal message and button
                    if (response.success) {
                        clearIntervals(); // Clear intervals
                        var paragraph = document.createElement('h4');
                        paragraph.classList.add('text-danger');
                        paragraph.textContent = response.winner + ' is the winner!';
                        modalBody.appendChild(paragraph);
                        var button = document.createElement('button');
                        button.classList.add('btn', 'btn-danger', 'text-center');
                        button.textContent = 'Play Again!';
                        modalBody.appendChild(button);
                        button.addEventListener('click', function () {
                            window.location.href = 'index.php';
                        });
                        $('#gameOverModal').modal({ backdrop: 'static', keyboard: false });
                        $('#gameOverModal').modal({ show: true });
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Function to clear intervals
        function clearIntervals() {
            clearInterval(changePhaseInterval);
            clearInterval(checkWinInterval);
        }

        // Function to handle discussing phase
        function discussing() {
            return new Promise(function (resolve, reject) {
                // Send an AJAX request for discussing phase
                $.ajax({
                    url: 'request-handler.php',
                    type: 'POST',
                    data: {
                        action: 'discussing',
                        gameId: <?php echo $_GET['game_id'] ?>
                    },
                    success: function (response) {
                        resolve(response); // Resolve the promise with the response data
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        reject(error); // Reject the promise with the error
                    }
                });
            });
        }

        // Function to handle special abilities phase
        function specialAbilities() {
            return new Promise(function (resolve, reject) {
                // Send an AJAX request for special abilities phase
                $.ajax({
                    url: 'request-handler.php',
                    type: 'POST',
                    data: {
                        action: 'specialAbilities',
                        gameId: <?php echo $_GET['game_id'] ?>
                    },
                    success: function (response) {
                        resolve(response); // Resolve the promise with the response data
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        reject(error); // Reject the promise with the error
                    }
                });
            });
        }

        // Function to handle voting phase
        function voting() {
            return new Promise(function (resolve, reject) {
                // Send an AJAX request for voting phase
                $.ajax({
                    url: 'request-handler.php',
                    type: 'POST',
                    data: {
                        action: 'voting',
                        gameId: <?php echo $_GET['game_id'] ?>
                    },
                    success: function (response) {
                        resolve(response); // Resolve the promise with the response data
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        reject(error); // Reject the promise with the error
                    }
                });
            });
        }

        // Function to update actions
        function updateActions(action) {
            // Send an AJAX request to update actions
            $.ajax({
                url: 'request-handler.php',
                type: 'POST',
                data: {
                    action: 'updateActions',
                    gameId: <?php echo $_GET['game_id'] ?>,
                    type: action
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Clear game updates and append new updates
                        $('.game-updates').empty();
                        response.rows.forEach(function (action) {
                            $('.game-updates').append('<p class="text-center">' + action + '</p>');
                        });
                    } else {
                        $('.game-updates').empty();
                    }
                },
                error: function (xhr, status, error) {
                    $('.game-updates').empty();
                }
            });
        }

        // Function to accuse a player
        function accusePlayer(playerID) {
            // Send an AJAX request to accuse a player
            $.ajax({
                url: 'request-handler.php',
                type: 'POST',
                data: {
                    action: 'accusePlayer',
                    gameId: <?php echo $_GET['game_id'] ?>,
                    toPlayerId: playerID,
                    fromPlayerId: <?php echo $realPlayer['id'] ?>
                },
                success: function (response) {
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Function to vote for a player
        function votePlayer(playerID) {
            // Send an AJAX request to vote for a player
            $.ajax({
                url: 'request-handler.php',
                type: 'POST',
                data: {
                    action: 'votePlayer',
                    gameId: <?php echo $_GET['game_id'] ?>,
                    toPlayerId: playerID,
                    fromPlayerId: <?php echo $realPlayer['id'] ?>
                },
                success: function (response) {
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Function to protect a player
        function protectPlayer(playerID) {
            // Send an AJAX request to protect a player
            $.ajax({
                url: 'request-handler.php',
                type: 'POST',
                data: {
                    action: 'protectPlayer',
                    gameId: <?php echo $_GET['game_id'] ?>,
                    playerId: playerID,
                    doctorId: <?php echo $realPlayer['id'] ?>
                },
                success: function (response) {
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }


    </script>



</body>

</html>