<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Information Form</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Medical Information Form</h1>
        <form method="post" action="medical.php">
            <div class="form-group">
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" placeholder="Your Full Name">
            </div>
            <div class="form-group">
                <label for="bloodType">Blood Type:</label>
                <input type="text" id="bloodType" name="bloodType" placeholder="e.g., A+, O-">
            </div>
            <div class="form-group">
                <label for="allergies">Common Allergies:</label>
                <input type="text" id="allergies" name="allergies" placeholder="e.g., Pollen, Peanuts">
            </div>
            <div class="form-group">
                <label for="weight">Weight:</label>
                <input type="number" id="weight" name="weight" placeholder="lbs">
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <input type="text" id="sex" name="sex" placeholder="e.g., Male, Female, Other">
            </div>
            <div class="form-group">
                <label for="height">Height:</label>
                <input type="text" id="height" name="height" placeholder="e.g.: 5.10 for 5 feet 10 inches">
            </div>
            <div class="form-group">
                <label for="conditions">Pre-Existing Conditions:</label>
                <textarea id="conditions" name="conditions" placeholder="Describe any pre-existing conditions"></textarea>
            </div>
            <div class="form-group">
                <label for="comments">Comments:</label>
                <textarea id="comments" name="comments" placeholder="Additional comments or information"></textarea>
            </div>
            <button type="submit">Submit</button>
        </form>
        <form method="post" action="pdf_upload.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pdfFile">Upload PDF:</label>
                <input type="file" id="pdfFile" name="pdfFile">
            </div>
            <button type="submit">Upload PDF</button>
        </form>
        <a href="qrcode.html" class="redirect-button">Generate QR Code</a>
        <a href="logout.php" class="loggout">Log Out</a>
    </div>
    <?php
        session_start();
        if (isset($_SESSION['username'])) {
            $sessionUsername = $_SESSION['username'];
            echo "<script>console.log('Session Username:', '$sessionUsername');</script>";
        }
    ?>
</body>

</html>
