<?php
// Start a PHP session
session_start();

// Check if the user is logged in and has a session username
if (isset($_SESSION['doctor_username'])) {
    // Get the session username (doctor's username)
    $doctorUsername = $_SESSION['doctor_username'];

    // Include the database connection script
    include 'connection.php';

    // Query to fetch medical information for the current doctor's patients
    $sql = "SELECT full_name, blood_type, common_allergies, weight, sex, height, pre_existing_conditions, comments FROM medical_info WHERE doctor_username = :doctorUsername";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':doctorUsername', $doctorUsername, PDO::PARAM_STR);
    $stmt->execute();

    // Check if there are any records
    if ($stmt->rowCount() > 0) {
        // Fetch the medical information into an array
        $medicalInfo = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $medicalInfo[] = $row;
        }

        // Return the medical information as JSON
        header('Content-Type: application/json');
        echo json_encode($medicalInfo);
    } else {
        // Return an empty JSON array if no medical information is found
        echo json_encode([]);
    }
} else {
    // Redirect to the login page if the user is not logged in
    header("Location: login.html");
    exit();
}
?>
