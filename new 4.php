<?php
// Enable error reporting for debugging (REMOVE/COMMENT IN PRODUCTION)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root";   // <--- REPLACE WITH YOUR ACTUAL DATABASE USERNAME
$password = "";   // <--- REPLACE WITH YOUR ACTUAL DATABASE PASSWORD
$dbname = "intern";   // <--- REPLACE WITH YOUR ACTUAL DATABASE NAME

// Directory where uploaded files will be stored.
// Make sure this directory exists and is writable by the web server.
// Example: C:\xampp\htdocs\your_project\uploads\intern_files
$upload_dir = 'uploads/';

// Create the upload directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // 0777 grants full permissions (adjust as needed for security)
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get the phone number from the hidden field
$num = $_POST['num'] ?? '';

// Validate phone number
if (empty($num)) {
    echo json_encode(['success' => false, 'message' => 'Phone number missing. Cannot process upload.']);
    $conn->close();
    exit();
}

// Check if a file was actually uploaded
if (!isset($_FILES['internship_file']) || $_FILES['internship_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or an upload error occurred. Error code: ' . ($_FILES['internship_file']['error'] ?? 'N/A')]);
    $conn->close();
    exit();
}

$file = $_FILES['internship_file'];

// File properties
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];
$fileType = $file['type'];

// Allowed file extensions and max size
$allowedExtensions = ['pdf', 'doc', 'docx', 'zip'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file type and size
if (!in_array($fileExt, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, DOCX, and ZIP are allowed.']);
    $conn->close();
    exit();
}

if ($fileSize > $maxFileSize) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
    $conn->close();
    exit();
}

// Generate a unique file name to prevent overwrites and security issues
// Using phone number and current timestamp for uniqueness
$newFileName = $num . '_' . uniqid() . '.' . $fileExt;
$fileDestination = $upload_dir . $newFileName;

// Move the uploaded file to the target directory
if (move_uploaded_file($fileTmpName, $fileDestination)) {
    // File uploaded successfully. Now, update the database.
    // Assuming you have a column (e.g., `uploaded_file_path`) in `internship_feedback`
    // or `studtable` to store the path. Let's assume `internship_feedback`.

    // First, check if the feedback entry exists for this phone number
    $check_feedback_sql = "SELECT COUNT(*) FROM internship_feedback WHERE num = ?";
    $stmt_check_feedback = $conn->prepare($check_feedback_sql);
    $stmt_check_feedback->bind_param("s", $num);
    $stmt_check_feedback->execute();
    $stmt_check_feedback->bind_result($feedback_count);
    $stmt_check_feedback->fetch();
    $stmt_check_feedback->close();

    if ($feedback_count > 0) {
        // Update the existing feedback entry with the file path
        // You'll need to add a column, e.g., `uploaded_file_path` to your `internship_feedback` table
        // ALTER TABLE internship_feedback ADD COLUMN uploaded_file_path VARCHAR(255) DEFAULT NULL;
        $update_sql = "UPDATE internship_feedback SET uploaded_file_path = ? WHERE num = ?";
        $stmt_update = $conn->prepare($update_sql);

        if ($stmt_update) {
            $stmt_update->bind_param("ss", $fileDestination, $num);
            if ($stmt_update->execute()) {
                echo json_encode(['success' => true, 'message' => 'File uploaded and linked successfully!']);
            } else {
                // If DB update fails, delete the uploaded file to avoid orphaned files
                unlink($fileDestination);
                echo json_encode(['success' => false, 'message' => 'File uploaded, but failed to update database: ' . $stmt_update->error]);
            }
            $stmt_update->close();
        } else {
            // If DB prepare fails, delete the uploaded file
            unlink($fileDestination);
            echo json_encode(['success' => false, 'message' => 'Failed to prepare update statement for database: ' . $conn->error]);
        }
    } else {
        // This case should ideally not happen if ff.html redirection is robust
        // If feedback not found, delete the uploaded file
        unlink($fileDestination);
        echo json_encode(['success' => false, 'message' => 'No matching feedback entry found for this phone number. File not linked.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file. Check directory permissions.']);
}

$conn->close();
?>
