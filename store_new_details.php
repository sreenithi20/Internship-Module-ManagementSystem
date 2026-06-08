<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

header('Content-Type: application/json');

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "intern";

$feedbackTableName          = "internship_feedback";
$internshipDetailsTableName = "studtable";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $e->getMessage()]);
    exit();
}

$name          = trim($_POST['name']          ?? '');
$email         = trim($_POST['email']         ?? '');
$num           = trim($_POST['num']           ?? '');
$rating_raw    = $_POST['rating']             ?? '';
$sat_raw       = $_POST['satisfaction']       ?? '';
$valuable_aspect             = $_POST['valuable_aspect']             ?? '';
$challenges                  = $_POST['challenges']                  ?? '';
$suggestions                 = $_POST['suggestions']                 ?? '';
$recommend                   = $_POST['recommend']                   ?? '';
$supported_supervisor        = $_POST['supported_supervisor']        ?? '';
$relevant_tasks              = $_POST['relevant_tasks']              ?? '';
$collaboration_opportunities = $_POST['collaboration_opportunities'] ?? '';
$career_preparation          = $_POST['career_preparation']          ?? '';
$internship_duration         = $_POST['internship_duration']         ?? '';
$comments                    = $_POST['comments']                    ?? '';

$rating       = ($rating_raw !== '') ? (int)$rating_raw : 0;
$satisfaction = ($sat_raw    !== '') ? (int)$sat_raw    : 0;

if (empty($name) || empty($email) || empty($num) || $rating === 0 || $satisfaction === 0) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields (Name, Email, Phone Number, Rating, Satisfaction).']);
    $conn->close();
    exit();
}

// Check phone exists in studtable
try {
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM `$internshipDetailsTableName` WHERE num = ?");
    $stmt_check->bind_param("s", $num);
    $stmt_check->execute();
    $stmt_check->bind_result($phone_count);
    $stmt_check->fetch();
    $stmt_check->close();
} catch (mysqli_sql_exception $e) {
    echo json_encode(['success' => false, 'message' => 'Phone check failed: ' . $e->getMessage()]);
    $conn->close();
    exit();
}

if ($phone_count == 0) {
    echo json_encode(['success' => false, 'message' => 'Phone number not found. Feedback cannot be submitted.']);
    $conn->close();
    exit();
}

// Insert feedback
$sql = "INSERT INTO `$feedbackTableName`
    (name, email, num, rating, satisfaction, valuable_aspect, challenges,
     suggestions, recommend, supported_supervisor, relevant_tasks,
     collaboration_opportunities, career_preparation, internship_duration, comments, submission_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiissssssssss",
        $name, $email, $num,
        $rating, $satisfaction,
        $valuable_aspect, $challenges, $suggestions,
        $recommend, $supported_supervisor, $relevant_tasks,
        $collaboration_opportunities, $career_preparation,
        $internship_duration, $comments
    );
    $stmt->execute();

    echo json_encode([
        'success'      => true,
        'message'      => 'Feedback submitted successfully!',
        'redirect_url' => 'new 3.html?num=' . urlencode($num)
    ]);

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        $msg = strpos($e->getMessage(), 'email')
            ? 'Feedback already submitted with this email address.'
            : 'Feedback already submitted for this phone number.';
        echo json_encode(['success' => false, 'message' => $msg]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $e->getMessage()]);
    }
}

$stmt->close();
$conn->close();
?>