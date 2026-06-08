<?php
date_default_timezone_set('Asia/Kolkata'); // Set your timezone

// Temporarily enabled for debugging — comment out in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manual PHPMailer includes - make sure these paths are correct relative to Management.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php'; // Only required if sending via SMTP

// 1. Database Connection Parameters
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL root password, if you have one.
$dbname = "intern"; // Your database name

// 2. Create Database Connection
$con = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to send email using PHPMailer
function sendApplicationEmail($recipientEmail, $recipientName, $status, $customSubject = null, $customMessage = null) {
    $mail = new PHPMailer(true); // Passing 'true' enables exceptions for error handling

    try {
        // Server settings for SMTP
        $mail->SMTPDebug = 0; // Set to 0 for no output in production, 2 for detailed debug output during development
        $mail->isSMTP(); // Send using SMTP
        $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through (e.g., smtp.gmail.com)
        $mail->SMTPAuth   = true; // Enable SMTP authentication
        $mail->Username   = ''; // SMTP username (YOUR GMAIL ADDRESS)
        $mail->Password   = ''; // SMTP password (YOUR GMAIL APP PASSWORD)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable STARTTLS on port 587
        $mail->Port       = 587; // TCP port to connect to; use 587 if you set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        // ADD THIS EXACT BLOCK HERE TO BYPASS WINDOWS SSL VERIFICATION:
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        
        // Recipients
        $mail->setFrom('sreenithi20032006@gmail.com', 'Internship Coordinator'); // Sender's email and name
        $mail->addAddress($recipientEmail, $recipientName); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $customSubject ?? "Internship Application Status: " . $status; // Use custom subject or default
        //$mail->Body    = nl2br($customMessage ?? "Dear {$recipientName},<br><br>Your internship application status is: <strong>{$status}</strong>.<br><br>Thank you."); // Use custom message or default
        $mail->Body = $customMessage ?? "Dear {$recipientName},<br><br>Your internship application status is: <strong>{$status}</strong>.<br><br>Thank you.";
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        error_log("Email could not be sent to {$recipientEmail}. Mailer Error: {$mail->ErrorInfo}");
        return false; // Email sending failed
    }
}

// --- PHP Logic to handle form submissions (Accept/Decline/Custom Email) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $app_id = $_POST['app_id'] ?? null;
    $student_email = $_POST['student_email'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $action = $_POST['action'];

    if ($app_id === null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: Application ID is missing.']);
        exit();
    } else {
        $success = false;
        $message = '';
        $new_status = ''; 

        switch ($action) {
            case 'accept':
                $new_status = 'Accepted';
                // Update database status for the application
                $update_sql = "UPDATE studtable SET status = ? WHERE id = ?";
                $stmt = $con->prepare($update_sql);
                $stmt->bind_param("ss", $new_status, $app_id);

                if ($stmt->execute()) {
                    // Define acceptance email content
                    $subject = "Internship Application Accepted!";
                    $message_content = "Dear " . htmlspecialchars($student_name) . ",<br><br>";
                    $message_content .= "Congratulations! We are pleased to inform you that your internship application has been <strong>accepted</strong>.<br><br>";
                    $message_content .= "This Internship will provide Hands-on experience in real time projects and enhance your skills in a professional environment.<br><br>";
                    $message_content .= "We look forward to having you!<br><br>";
                    $message_content .= "Bring Your ID Card and Passport Size Photo on the specified day.<br><br>";
                    $message_content .= "Best regards,<br>CHR Team";

                    if (sendApplicationEmail($student_email, $student_name, $new_status, $subject, $message_content)) {
                        $success = true;
                        $message = "Application Accepted and Email Sent to " . htmlspecialchars($student_email) . "!";
                    } else {
                        $message = "Application Accepted, but Email could not be sent to " . htmlspecialchars($student_email) . ". Check your XAMPP/PHP error log for exact SMTP errors.";
                    }
                } else {
                    $message = "Error updating application status to Accepted: " . $stmt->error;
                }
                $stmt->close();
                break;

            case 'decline':
                $new_status = 'Declined';
                // Update database status for the application
                $update_sql = "UPDATE studtable SET status = ? WHERE id = ?";
                $stmt = $con->prepare($update_sql);
                $stmt->bind_param("ss", $new_status, $app_id);

                if ($stmt->execute()) {
                    // Define decline email content
                    $subject = "Regarding Your Internship Application";
                    $message_content = "Dear " . htmlspecialchars($student_name) . ",<br><br>";
                    $message_content .= "Thank you for your interest in our internship program. After careful consideration, we regret to inform you that we will not be able to offer you a position at this time.<br><br>";
                    $message_content .= "We wish you the best in your future endeavors and encourage you to apply again for future opportunities.<br><br>";
                    $message_content .= "Sincerely,<br>CHR Team";

                    if (sendApplicationEmail($student_email, $student_name, $new_status, $subject, $message_content)) {
                        $success = true;
                        $message = "Application Declined and Email Sent to " . htmlspecialchars($student_email) . "!";
                    } else {
                        $message = "Application Declined, but Email could not be sent to " . htmlspecialchars($student_email) . ". Check server logs for details.";
                    }
                } else {
                    $message = "Error updating application status to Declined: " . $stmt->error;
                }
                $stmt->close();
                break;

            case 'send_custom_email':
                $custom_subject = $_POST['custom_subject'] ?? 'Custom Email from Internship Coordinator';
                $custom_message = $_POST['custom_message'] ?? 'This is a custom message regarding your application.';

                if (sendApplicationEmail($student_email, $student_name, 'Custom', $custom_subject, $custom_message)) {
                    $success = true;
                    $message = "Custom Email Sent to " . htmlspecialchars($student_email) . "!";
                } else {
                    $message = "Custom Email could not be sent to " . htmlspecialchars($student_email) . ". Check server logs for details.";
                }
                break;

            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unknown action.']);
                exit();
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'app_id' => $app_id, 'new_status' => $new_status]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Application Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            color: #334155;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #1a202c;
            text-align: center;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .status-pending { background-color: #fffbe6; color: #b45309; border: 1px solid #fcd34d; }
        .status-accepted { background-color: #d1fae5; color: #047857; border: 1px solid #34d399; }
        .status-declined { background-color: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .action-button {
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s ease-in-out;
            font-size: 0.9em;
        }
        .accept-button { background-color: #22c55e; color: white; }
        .accept-button:hover { background-color: #16a34a; }
        .decline-button { background-color: #ef4444; color: white; }
        .decline-button:hover { background-color: #dc2626; }
        .send-email-button { background-color: #3b82f6; color: white; }
        .send-email-button:hover { background-color: #2563eb; }
        .no-applications { text-align: center; padding: 30px; font-size: 1.1em; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Internship Applications</h2>

        <?php
        $sql = "SELECT id, first_name, last_name, email, status, submission_time FROM studtable ORDER BY submission_time DESC";
        $result = mysqli_query($con, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo '<table class="min-w-full divide-y divide-gray-200 shadow-md rounded-lg overflow-hidden" id="applicationsTable">';
            echo '<thead class="bg-gray-50">';
            echo '<tr>';
            echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>';
            echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>';
            echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>';
            echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied Date</th>';
            echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>';
            echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="bg-white divide-y divide-gray-200">';

            while ($app = mysqli_fetch_assoc($result)) {
                echo '<tr id="app-row-' . htmlspecialchars($app['id']) . '">';
                echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($app['id']) . '</td>';
                echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) . '</td>';
                echo '<td class="px-6 py-4 whitespace-nowrap">';
                echo '<a href="mailto:' . htmlspecialchars($app['email']) . '?subject=Internship%20Application%20Query&body=Dear%20' . urlencode($app['first_name'] . ' ' . $app['last_name']) . ',%0A%0ARegarding%20your%20internship%20application:%0A%0A">';
                echo htmlspecialchars($app['email']);
                echo '</a>';
                echo '</td>';
                echo '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($app['submission_time']) . '</td>';
                echo '<td class="px-6 py-4 whitespace-nowrap">';
                
                $statusClass = '';
                switch ($app['status']) {
                    case 'Pending': $statusClass = 'status-pending'; break;
                    case 'Accepted': $statusClass = 'status-accepted'; break;
                    case 'Declined': $statusClass = 'status-declined'; break;
                    default: $statusClass = '';
                }
                echo '<span class="status-badge ' . $statusClass . '" id="status-badge-' . htmlspecialchars($app['id']) . '">' . htmlspecialchars($app['status']) . '</span>';
                echo '</td>';
                echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
                echo '<div class="flex space-x-2">';

                echo '<button type="button" class="action-button accept-button" data-app-id="' . htmlspecialchars($app['id']) . '" data-student-email="' . htmlspecialchars($app['email']) . '" data-student-name="' . htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) . '" data-action="accept" ' . ($app['status'] === 'Accepted' ? 'disabled' : '') . '>Accept</button>';
                echo '<button type="button" class="action-button decline-button" data-app-id="' . htmlspecialchars($app['id']) . '" data-student-email="' . htmlspecialchars($app['email']) . '" data-student-name="' . htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) . '" data-action="decline" ' . ($app['status'] === 'Declined' ? 'disabled' : '') . '>Decline</button>';
                echo '<button type="button" class="action-button send-email-button" data-app-id="' . htmlspecialchars($app['id']) . '" data-student-email="' . htmlspecialchars($app['email']) . '" data-student-name="' . htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) . '" data-action="send_custom_email">Send Custom Email</button>';

                echo '</div>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p class="no-applications">No internship applications found.</p>';
        }
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.action-button');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const appId = this.dataset.appId;
                    const studentEmail = this.dataset.studentEmail;
                    const studentName = this.dataset.studentName;
                    const action = this.dataset.action;

                    let customSubject = '';
                    let customMessage = '';

                    if (action === 'send_custom_email') {
                        customSubject = prompt('Enter custom email subject:');
                        if (customSubject === null) return; 
                        customMessage = prompt('Enter custom email message:');
                        if (customMessage === null) return; 
                    }

                    const formData = new FormData();
                    formData.append('app_id', appId);
                    formData.append('student_email', studentEmail);
                    formData.append('student_name', studentName);
                    formData.append('action', action);
                    if (customSubject) formData.append('custom_subject', customSubject);
                    if (customMessage) formData.append('custom_message', customMessage);

                    fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            if (action === 'accept' || action === 'decline') {
                                const row = document.getElementById('app-row-' + data.app_id);
                                if (row) {
                                    row.style.display = 'none'; 
                                }
                            }
                            const currentRowButtons = document.querySelectorAll('#app-row-' + data.app_id + ' .action-button');
                            currentRowButtons.forEach(btn => btn.disabled = true);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing your request.');
                    });
                });
            });
        });
    </script>
</body>
</html>
<?php
mysqli_close($con);
?>
