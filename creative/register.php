<?php
session_start();
require 'connection.php';

if (isset($_POST['usernameinput']) && isset($_POST['passwordinput'])) {
    // Using the `filter_input()` function to sanitize the input.
    $username = filter_input(INPUT_POST, 'usernameinput', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'passwordinput', FILTER_SANITIZE_STRING);

    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "Username already exists. Please choose a different username.";
        echo '<a href="register.html" class="back-button">Back to Register Page</a>';
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);

            $user_id = $pdo->lastInsertId();

            echo '<div>';
            echo '<p>Registration successful!</p>';
            echo '<a href="login.html" class="back-button">Back</a>';
            echo '</div>';
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
} else {
    echo "Please provide a username and password.";
}

?>
