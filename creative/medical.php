<?php
session_start();
require 'connection.php'; 

if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $bloodType = filter_input(INPUT_POST, 'bloodType', FILTER_SANITIZE_STRING);
    $allergies = filter_input(INPUT_POST, 'allergies', FILTER_SANITIZE_STRING);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $sex = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_STRING);
    $height = filter_input(INPUT_POST, 'height', FILTER_SANITIZE_STRING);
    $conditions = filter_input(INPUT_POST, 'conditions', FILTER_SANITIZE_STRING);
    $comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING);

    // Define doctor_username
    $doctorUsername = "doc";

    // Check if the username already exists in medical_info
    $checkStmt = $pdo->prepare("SELECT * FROM medical_info WHERE username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        // If the username exists, update the existing record
        $updateStmt = $pdo->prepare("UPDATE medical_info SET full_name = :fullName, blood_type = :bloodType, common_allergies = :allergies, weight = :weight, sex = :sex, height = :height, pre_existing_conditions = :conditions, comments = :comments, doctor_username = :doctorUsername WHERE username = :username");
        $updateStmt->bindParam(':fullName', $fullName);
        $updateStmt->bindParam(':bloodType', $bloodType);
        $updateStmt->bindParam(':allergies', $allergies);
        $updateStmt->bindParam(':weight', $weight);
        $updateStmt->bindParam(':sex', $sex);
        $updateStmt->bindParam(':height', $height);
        $updateStmt->bindParam(':conditions', $conditions);
        $updateStmt->bindParam(':comments', $comments);
        $updateStmt->bindParam(':doctorUsername', $doctorUsername);
        $updateStmt->bindParam(':username', $username);
        $updateStmt->execute();
    } else {
        // Insert the new information
        $insertStmt = $pdo->prepare("INSERT INTO medical_info (username, full_name, blood_type, common_allergies, weight, sex, height, pre_existing_conditions, comments, doctor_username) VALUES (:username, :fullName, :bloodType, :allergies, :weight, :sex, :height, :conditions, :comments, :doctorUsername)");
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':fullName', $fullName);
        $insertStmt->bindParam(':bloodType', $bloodType);
        $insertStmt->bindParam(':allergies', $allergies);
        $insertStmt->bindParam(':weight', $weight);
        $insertStmt->bindParam(':sex', $sex);
        $insertStmt->bindParam(':height', $height);
        $insertStmt->bindParam(':conditions', $conditions);
        $insertStmt->bindParam(':comments', $comments);
        $insertStmt->bindParam(':doctorUsername', $doctorUsername);
        $insertStmt->execute();
    }

    header('Location: edit.php');
    exit();
}

$pdo = null;
?>
