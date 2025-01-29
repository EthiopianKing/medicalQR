<?php
// Include the database connection file
include 'connection.php';
session_start();
// Check if the session username is set
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];


    // Query the database to retrieve medical information
    $sql = "SELECT blood_type, weight, common_allergies FROM medical_info WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Fetch the medical information
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $bloodType = $row["blood_type"];
        $weight = $row["weight"];
        $allergies = $row["common_allergies"];

        // Create an array with the medical data
        $medicalData = [
            'bloodType' => $bloodType,
            'weight' => $weight,
            'allergies' => $allergies,
        ];

        // Send the medical data as JSON response
        header('Content-Type: application/json');
        echo json_encode($medicalData);
        exit; // Terminate the script after sending JSON data
    } else {
        echo json_encode(['error' => 'No medical information found for the logged-in user.']);
        exit; // Terminate the script with an error response
    }
} else {
    echo json_encode(['error' => 'Session username not set.']);
    exit; // Terminate the script with an error response
}
?>
