<?php

require_once 'vendor/autoload.php';


$userAuthInstance = new UserAuth;
if ($userAuthInstance->getLoggedUser()) {

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
    </head>

    <body>
        <div class="container h-100">

            <div class="d-flex justify-content-center">
                <div class="play-button">
                    <h1 class="mt-4" onclick='play()'>PLAY</h1>
                </div>
            </div>

        </div>
        <script>
            function play() {
                window.location.href = 'game.php';
            }
        </script>
    </body>

    </html>

    <?php
} else {

    header('location:login.php');

}
