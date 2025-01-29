<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
    </style>
</head>

<body>
    <h1>You have been logged out.</h1>
    <a href="login.html"><button>Login</button></a>
</body>
<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = array();
// Destroy the session
session_destroy();

// Redirect to the login page or any other page you want
exit;
?>

</html>