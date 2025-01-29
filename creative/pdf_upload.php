<?php
session_start();

if (isset($_SESSION['username'])) {
    $sessionUsername = $_SESSION['username'];

    // Check if a PDF file was uploaded
    if (isset($_FILES["pdfFile"]) && $_FILES["pdfFile"]["error"] == UPLOAD_ERR_OK) {
        $pdfTmpName = $_FILES["pdfFile"]["tmp_name"];

        // Include your connection.php file
        include 'connection.php';

        // Get the PDF file name
        $pdfFileName = $_FILES["pdfFile"]["name"];

        // Check if a record with the same name and username exists
        $checkQuery = "SELECT id FROM pdf_storage WHERE pdf_name = :pdf_name AND user_username = :user_username";

        try {
            $checkStmt = $pdo->prepare($checkQuery);
            $checkStmt->bindParam(':pdf_name', $pdfFileName, PDO::PARAM_STR);
            $checkStmt->bindParam(':user_username', $sessionUsername, PDO::PARAM_STR);
            $checkStmt->execute();

            $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRecord) {
                // If a matching record exists, update it
                $updateQuery = "UPDATE pdf_storage SET pdf_data = :pdf_data WHERE id = :id";
                $updateStmt = $pdo->prepare($updateQuery);
                $pdfData = file_get_contents($pdfTmpName);

                // Bind parameters for update
                $updateStmt->bindParam(':pdf_data', $pdfData, PDO::PARAM_LOB);
                $updateStmt->bindParam(':id', $existingRecord['id'], PDO::PARAM_INT);

                if ($updateStmt->execute()) {
                    echo "Successfully updated file.";
                } else {
                    echo "Error updating file: " . $updateStmt->errorInfo()[2];
                }
            } else {
                // If no matching record exists, insert a new record
                $insertQuery = "INSERT INTO pdf_storage (pdf_data, pdf_name, user_username, doctor_username)
                    VALUES (:pdf_data, :pdf_name, :user_username, 'doc')";
                $insertStmt = $pdo->prepare($insertQuery);
                $pdfData = file_get_contents($pdfTmpName);

                // Bind parameters for insert
                $insertStmt->bindParam(':pdf_data', $pdfData, PDO::PARAM_LOB);
                $insertStmt->bindParam(':pdf_name', $pdfFileName, PDO::PARAM_STR);
                $insertStmt->bindParam(':user_username', $sessionUsername, PDO::PARAM_STR);

                if ($insertStmt->execute()) {
                    echo "Successfully sent file to doctor.";
                } else {
                    echo "Error inserting record: " . $insertStmt->errorInfo()[2];
                }
            }
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "No PDF file uploaded.";
    }
} else {
    header("Location: login.php");
    exit;
}
?>

<button onclick="returnToEditPage()">Return to Edit Page</button>

<script>
function returnToEditPage() {
    window.location.href = 'edit.php';
}
</script>
