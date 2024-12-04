<?php

session_start();
include './components/loggly-logger.php';

$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';

$logger->info("Attempting to connect to the database.");
$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$logger->info("Database connection is successful!");

unset($error_message);

if ($conn->connect_error) {
    $errorMessage = "Connection failed: " . $conn->connect_error; 
    die($errorMessage);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND approved = 1";
    // $result = $conn->query($sql);
    // Prepare a parameterized SQL query
    $sql = "SELECT * FROM users WHERE username = ? AND password = ? AND approved = 1";
    $stmt = $conn->prepare($sql);
    // Bind the input variables to the prepared statement
    $stmt->bind_param("ss", $username, $password);
    // Execute the prepared statement
    $stmt->execute();
    // Get the result of the query
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
       
        $userFromDB = $result->fetch_assoc();

        //$_COOKIE['authenticated'] = $username;
        $_SESSION['authenticated'] = $username;     

        if ($userFromDB['default_role_id'] == 1)
        {        
            $_SESSION['isSiteAdministrator'] = 1;               
        }else{
            unset($_SESSION['isSiteAdministrator']); 
        }
        $logger->info("Login successful for username: $username");

        header("Location: index.php");
        exit();
    } else {
        $error_message = 'Invalid username or password.';  
        $logger->warning("Login failed for username: $username");
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Login Page</title>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3">
            <h2 class="text-center">Login</h2>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="mt-3 text-center">
                <a href="./users/request_account.php" class="btn btn-secondary btn-block">Request an Account</a>
            </div>
        </div>
    </div>
</body>
</html>