<?php
date_default_timezone_set('Asia/Kolkata');
// 1. Database Connection Parameters
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL root password, if you have one.
$dbname = "intern";

// 2. Create Database Connection
$con = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// 3. Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Sanitize and Validate Input
    $fname = isset($_POST['fname']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['fname']))) : '';
    $lname = isset($_POST['lname']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['lname']))) : '';
    $student_id = isset($_POST['student_id']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['student_id']))) : '';
    $college_name = isset($_POST['college_name']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['college_name']))) : '';
    $qualification = isset($_POST['qualification']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['qualification']))) : '';

    // Handle specialization
    $specialization = ''; // Initialize
if (isset($_POST['specialization'])) {
    $selected_specialization = trim($_POST['specialization']);

    if ($selected_specialization === 'Other' && isset($_POST['other_specialization']) && $_POST['other_specialization'] !== '') {
        // If 'Other' is selected and 'other_specialization' is provided, use that value
        $specialization = mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['other_specialization'])));
    } else {
        // Otherwise, use the directly selected specialization
        $specialization = mysqli_real_escape_string($con, htmlspecialchars($selected_specialization));
    }
}    

    $year_of_study = isset($_POST['year_of_study']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['year_of_study']))) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['email']))) : '';
    $phone_no = isset($_POST['phone_no']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['phone_no']))) : '';
    $alternate_phone_no = isset($_POST['alternate_phone_no']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['alternate_phone_no']))) : '';
    $alternate_phone_name = isset($_POST['alternate_phone_name']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['alternate_phone_name']))) : '';
    $alt_contact_type = isset($_POST['alt_contact_type']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['alt_contact_type']))) : '';
    $field_of_interest = isset($_POST['field_of_interest']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['field_of_interest']))) : '';
    $location = isset($_POST['location']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['location']))) : '';
$pur_of_intern=isset($_POST['pur_of_intern']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['pur_of_intern']))) : '';    
$duration_from = isset($_POST['duration_from']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['duration_from']))) : '';
    $duration_to = isset($_POST['duration_to']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['duration_to']))) : '';
        $status = isset($_POST['status']) ? mysqli_real_escape_string($con, htmlspecialchars(trim($_POST['status']))) : '';
    $resume_path = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = uniqid('resume_') . '_' . basename($_FILES['resume']['name']);
        $target_file = $upload_dir . $file_name;
        $file_type = mime_content_type($_FILES['resume']['tmp_name']);
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 5 * 1024 * 1024;

        if (in_array($file_type, $allowed_types) && $_FILES['resume']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
                $resume_path = $target_file;
            }
        }
    }
$photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // Ensure this directory exists and is writable
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        $photo_name = uniqid('photo_') . '_' . basename($_FILES['photo']['name']);
        $target_photo_file = $upload_dir . $photo_name;
        $photo_file_type = mime_content_type($_FILES['photo']['tmp_name']);
        $allowed_photo_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_photo_size = 2 * 1024 * 1024; // 2MB

        if (in_array($photo_file_type, $allowed_photo_types) && $_FILES['photo']['size'] <= $max_photo_size) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_photo_file)) {
                $photo_path = $target_photo_file;
            } else {
                // Handle error moving photo file
                error_log("Failed to move uploaded photo file: " . $_FILES['photo']['tmp_name'] . " to " . $target_photo_file);
            }
        } else {
            // Handle invalid photo file type or size
            error_log("Invalid photo file type or size: " . $photo_file_type . " size: " . $_FILES['photo']['size']);
        }
    }

    // NEW: Capture the server's current date and time
    $submission_time = date('Y-m-d H:i:s');

    // 5. Prepare SQL INSERT statement (now includes submission_time)
    $sql = "INSERT INTO studtable (
                first_name,
                last_name,
                id,
                clg_name,
                qual,
                spec,
                yrs,
                email,
                num,
                alt_num,
                alt_con,
                alt_contact_type,
                field,
                loc,
                pur_of_intern,
                dur_from,
                dur_to,
                resume_path,
                photo_path,
                submission_time,
status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?)";

    $stmt = mysqli_prepare($con,$sql);
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($con));
    }

    // 6. Bind parameters (18 total now)
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssssssssssss", // 19 's'
       
        $fname,
        $lname,
        $student_id,
        $college_name,
        $qualification,
        $specialization,
        $year_of_study,
        $email,
        $phone_no,
        $alternate_phone_no,
        $alternate_phone_name,
        $alt_contact_type,
        $field_of_interest,
        $location,
        $pur_of_intern,
        $duration_from,
        $duration_to,
        $resume_path,
        $photo_path,
        $submission_time,
$status
    );

    // 7. Execute
    if (mysqli_stmt_execute($stmt)) {
        echo "<!DOCTYPE html>
              <html>
              <head>
                  <title>Submission Success</title>
                  <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f7f6; text-align: center; padding-top: 50px; }
                    h1 { color: #28a745; }
                    p { color: #333; }
                    a { color: #007bff; text-decoration: none; }
                    a:hover { text-decoration: underline; }
                    .download-link { margin-top: 20px; font-size: 1.2em; }
                  </style>
              </head>
              <body>
                  <h1>Internship Registration - Submission Successful!</h1>
                  <p>Thank you, <strong>" . htmlspecialchars($fname) . " " . htmlspecialchars($lname) . "</strong>, for registering.</p>
                  <p>Your details have been successfully recorded.</p>
                  
                   <p>click the icon to Scan the whatsapp group</P>
                  <p> <img src='data:image/jpeg;base64,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' style='width:50px;position:relative;top:0px;left:0px;'></P>
                 <a href='https://drive.google.com/file/d/1PqBumR7OUYNhm8FBOTCO5sdqLGUkbWDp/view?usp=sharing' target='_blank'>
                  <img src='data:image/jpeg;base64,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' alt='Join WhatsApp Group' style='width: 100px; margin-top: 10px;' />
            
</a><br><br>(or)<br><br>
<a href='https://chat.whatsapp.com/BiocPnmY6CH5IneMqPRww3'>Click here to join the Whatsapp chat </a>
                  <p><a href='web.html'>Go back to the registration form</a></p>
              </body>
              </html>";
    } else {
        echo "<!DOCTYPE html>
              <html>
              <head>
                  <title>Submission Error</title>
                  <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f7f6; text-align: center; padding-top: 50px; }
                    h1 { color: #dc3545; }
                    p { color: #333; }
                    a { color: #007bff; text-decoration: none; }
                    a:hover { text-decoration: underline; }
                  </style>
              </head>
              <body>
                  <h1>Internship Registration - Submission Failed!</h1>
                  <p>There was an error saving your data: " . mysqli_error($con) . "</p>
                  <p>Please try again later or contact support.</p>
                
                   
                  <p><a href='web.html'>Go back to the registration form</a></p>
              </body>
              </html>";
    }

    mysqli_stmt_close($stmt);

} else {
    echo "<!DOCTYPE html>
          <html>
          <head>
              <title>Access Denied</title>
              <style>
                body { font-family: Arial, sans-serif; background-color: #f4f7f6; text-align: center; padding-top: 50px; }
                h1 { color: #ffc107; }
                p { color: #333; }
                a { color: #007bff; text-decoration: none; }
                a:hover { text-decoration: underline; }
              </style>
          </head>
          <body>
              <h1>Access Denied</h1>
              <p>This page cannot be accessed directly. Please submit the form.</p>
              <p><a href='index.html'>Go to the registration form</a></p>
          </body>
          </html>";
}


?>
