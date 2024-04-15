<?php



require_once 'vendor/autoload.php';

$userAuthInstance = new UserAuth;

if ($userAuthInstance->getLoggedUser()) {
    header('location:game.php');
}

$userAuth = new UserAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Call the login method
    $loginResult = $userAuth->login($email, $password);

    if ($loginResult) {
        // Login successful
        header('location:index.php');
    } else {
        // Login failed
        $errors = $userAuth->getErrors();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" rel="noopener" target="_blank" href="/assets/css/style.css">
</head>

<body>
    <div class="container h-100">
        <div class="row h-100">
            <div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
                <div class="d-table-cell align-middle">

                    <div class="text-center mt-5">
                        <h1 class="h2">Mafia game</h1>
                        <p class="lead">
                            Trust no one. Deceive everyone. Let the chaos unfold.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="m-sm-4">
                                <!-- Error message -->
                                <?php if (isset($errors) && !empty($errors)): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?php echo $errors; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" action="login.php">
                                    <div class="form-group">
                                        <label class="mb-2 mt-2"><strong>Email</strong></label>
                                        <input class="form-control form-control-lg" type="email" name="email"
                                            placeholder="Enter your email">
                                    </div>
                                    <div class="form-group">
                                        <label class="mb-2 mt-2"><strong>Password</strong></label>
                                        <input class="form-control form-control-lg" type="password" name="password"
                                            placeholder="Enter password">
                                    </div>
                                    <p class="mt-2">Don't have an account? <a href="register.php">Register</a></p>
                                    <div class="text-center mt-3">
                                        <button class="btn btn-lg btn-danger" type="submit">Login</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

</html>