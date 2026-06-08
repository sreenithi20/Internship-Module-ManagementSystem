<?php
date_default_timezone_set('Asia/Kolkata'); // Set your timezone

// 1. Database Connection Parameters
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL root password, if you have one.
$dbname = "intern"; // Your database name

// Your website's base URL (VERY IMPORTANT: Replace with your actual domain)
// This should be the root of your web server where the 'uploads' directory is accessible.
// Example: If your 'uploads' folder is directly under your web root (e.g., C:\wamp64\www\uploads),
// then http://localhost/ is correct.
$base_url = "http://localhost/"; // <-- CHANGE THIS TO YOUR ACTUAL WEBSITE URL!

// 2. Create Database Connection
$con = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=combined_internship_data.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// --- Combined Export of Internship Application and Feedback Data ---

// Output the CSV header row for Combined Data
fputcsv($output, array(
    // Application Data Headers
    'APPLICATION DATA - First Name', 'Last Name', 'Student ID', 'Year of Study', 'College Name',
    'Qualification', 'Specialization', 'Field of Interest', 'Email',
    'Phone No','Relation of Contact', 'Relation Phone No', 'Relation Contact Name',
    'Location','pur_of_intern',
    'Duration From', 'Duration To', 'Resume Link', 'Photo Link', 'Application Submission Time', 'Application Status',
    // Feedback Data Headers
    'FEEDBACK DATA - ID', 'Full Name (Feedback)', 'Email Address (Feedback)', 'Phone Number (Feedback)',
    'Overall Rating (1-5)', 'Overall Satisfaction (1-5)', 'Most Valuable Aspect',
    'Challenges Encountered', 'Suggestions for Improvement', 'Is Recommendable?',
    'Supported by Supervisor?', 'Tasks Relevant to Learning?', 'Collaboration Opportunities?',
    'Prepared for Career?', 'Duration Appropriate?', 'Additional Comments (Feedback)',
    'Feedback Submission Date',
    'Uploaded File Link' // <--- ADDED: New header for the uploaded feedback file link
));

// Fetch data by joining studtable and internship_feedback on phone numbers
$sql_combined = "
    SELECT
        s.first_name, s.last_name, s.id AS student_id, s.yrs, s.clg_name, s.qual, s.spec, s.field, s.email, s.num,
        s.alt_num, s.alt_con, s.alt_contact_type, s.loc, s.pur_of_intern,s.dur_from, s.dur_to,
        s.resume_path, s.photo_path, s.submission_time AS application_submission_time, s.status,
        f.id AS feedback_id, f.name AS feedback_name, f.email AS feedback_email, f.num AS feedback_num,
        f.rating, f.satisfaction, f.valuable_aspect,
        f.challenges, f.suggestions, f.recommend, f.supported_supervisor,
        f.relevant_tasks, f.collaboration_opportunities, f.career_preparation,
        f.internship_duration, f.comments, f.submission_date AS feedback_submission_date,
        f.uploaded_file_path  -- <--- ADDED: Select the uploaded file path from feedback table
    FROM
        studtable AS s
    LEFT JOIN
        internship_feedback AS f ON s.num = f.num
    ORDER BY
        s.submission_time DESC;
";
$result_combined = mysqli_query($con, $sql_combined);

if ($result_combined) {
    while ($row_combined = mysqli_fetch_assoc($result_combined)) {
        // Construct full URLs for resume and photo (application data)
        $resume_full_url = '';
        if (!empty($row_combined['resume_path'])) {
            $resume_full_url = $base_url . $row_combined['resume_path'];
        }

        $photo_full_url = '';
        if (!empty($row_combined['photo_path'])) {
            $photo_full_url = $base_url . $row_combined['photo_path'];
        }

        // Format as Excel HYPERLINK formula for resume and photo
        $resume_link_for_csv = '';
        if (!empty($resume_full_url)) {
            $resume_link_for_csv = '=HYPERLINK("' . str_replace('\\', '/', $resume_full_url) . '", "View Resume")';
        }

        $photo_link_for_csv = '';
        if (!empty($photo_full_url)) {
            $photo_link_for_csv = '=HYPERLINK("' . str_replace('\\', '/', $photo_full_url) . '", "View Photo")';
        }

        // --- Handle Uploaded Feedback File Link ---
        $uploaded_file_full_url = '';
        $uploaded_file_link_for_csv = '';
        if (!empty($row_combined['uploaded_file_path'])) {
            // Construct the full URL for the uploaded file
            // The $base_url should be your web root (e.g., http://localhost/)
            // The uploaded_file_path from DB will be like 'uploads/intern_files/filename.ext'
            $uploaded_file_full_url = $base_url . $row_combined['uploaded_file_path'];
            // Format as Excel HYPERLINK formula
            $uploaded_file_link_for_csv = '=HYPERLINK("' . str_replace('\\', '/', $uploaded_file_full_url) . '", "View Upload")';
        }


        // Prepare a row for CSV output for Combined Data
        $csv_row_combined = array(
            // Application Data
            $row_combined['first_name'],
            $row_combined['last_name'],
            $row_combined['student_id'],
            $row_combined['yrs'],
            $row_combined['clg_name'],
            $row_combined['qual'],
            $row_combined['spec'],
            $row_combined['field'],
            $row_combined['email'],
            $row_combined['num'],
            $row_combined['alt_contact_type'],

            $row_combined['alt_num'],
            $row_combined['alt_con'],

            $row_combined['loc'],
$row_combined['pur_of_intern'],
            $row_combined['dur_from'],
            $row_combined['dur_to'],
            $resume_link_for_csv,
            $photo_link_for_csv,
            $row_combined['application_submission_time'],
            $row_combined['status'],
            // Feedback Data
            $row_combined['feedback_id'],
            $row_combined['feedback_name'],
            $row_combined['feedback_email'],
            $row_combined['feedback_num'], // Phone Number (Feedback)
            $row_combined['rating'],
            $row_combined['satisfaction'],
            $row_combined['valuable_aspect'],
            $row_combined['challenges'],
            $row_combined['suggestions'],
            $row_combined['recommend'],
            $row_combined['supported_supervisor'],
            $row_combined['relevant_tasks'],
            $row_combined['collaboration_opportunities'],
            $row_combined['career_preparation'],
            $row_combined['internship_duration'],
            $row_combined['comments'],
            $row_combined['feedback_submission_date'],
            $uploaded_file_link_for_csv // <--- ADDED: The generated uploaded file link
        );
        fputcsv($output, $csv_row_combined);
    }
}

// Close the file pointer and database connection
fclose($output);
mysqli_close($con);
exit(); // Ensure no other output is sent
?>
