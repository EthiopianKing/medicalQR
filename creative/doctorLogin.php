<?php
session_start();
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Regenerate session ID to prevent Session Fixation
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}

// Include the connection file to define $pdo
include 'connection.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the username from the submitted form and sanitize the input
    $user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);

    // Use a prepared statement to select user information by username
    $stmt = $pdo->prepare("SELECT username, password_hash FROM doctors WHERE username = :username");

    // Bind the username parameter
    $stmt->bindParam(':username', $user, PDO::PARAM_STR);

    // Execute the prepared statement
    $stmt->execute();

    // Fetch the result as an associative array
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Use password_verify to check the provided password against the stored hash
    if ($result && password_verify($_POST['passwordinput'], $result['password_hash'])) {
        // Login succeeded!
        $_SESSION['doctor_username'] = $user; 
        echo "Logged in as: " . $_SESSION['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a new token
        // Redirect to your target page on successful login
        header("Location: doctorsPage.html");
        exit();
    } else {
        // Login failed; redirect to 'BadLogin.html' or display an error message
        // Use htmlentities to ensure any potential malicious characters from user input are rendered harmless
        echo htmlentities("Failed Login");
        echo '<br><button onclick="window.history.back();">Go Back</button>';
        exit();
    }
}

?>
