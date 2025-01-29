<?php
// Include your database connection file (e.g., connection.php)
include 'connection.php';

// Check if both user_username and pdf_name are provided as query parameters
if (isset($_GET['user_username']) && isset($_GET['pdf_name'])) {
    // Sanitize the input (to prevent SQL injection)
    $user_username = filter_input(INPUT_GET, 'user_username', FILTER_SANITIZE_STRING);
    $pdf_name = filter_input(INPUT_GET, 'pdf_name', FILTER_SANITIZE_STRING);

    try {
        // Prepare and execute a query to retrieve the PDF data
        $stmt = $pdo->prepare("SELECT pdf_data FROM pdf_storage WHERE user_username = :user_username AND pdf_name = :pdf_name");
        $stmt->bindParam(':user_username', $user_username, PDO::PARAM_STR);
        $stmt->bindParam(':pdf_name', $pdf_name, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the PDF data
        $pdfData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pdfData) {
            // Set the appropriate headers for PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $pdf_name . '"');

            // Output the PDF data
            echo $pdfData['pdf_data'];
            exit();
        } else {
            // PDF not found, display an error message or redirect as needed
            echo 'PDF not found.';
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
    }
} else {
    // Parameters not provided, display an error message or redirect as needed
    echo 'Parameters not provided.';
}
?>
