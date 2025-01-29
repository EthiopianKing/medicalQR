<?php
// Include your database connection file (e.g., connection.php)
include 'connection.php';

// Create an array to store the unique PDF data
$pdfDataArray = array();

try {
    // Replace 'pdf_storage' with your actual table name if different
    $sql = "SELECT DISTINCT pdf_name, user_username AS username
            FROM pdf_storage
            WHERE doctor_username = 'doc'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Add each unique row of PDF data to the array
        $pdfDataArray[] = $row;
    }

    // Return the unique PDF data as JSON
    header('Content-Type: application/json');
    echo json_encode($pdfDataArray);
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . $e->getMessage();
}
?>
