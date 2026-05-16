<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];   
    $password = $_POST['password'];

    $query = "SELECT * FROM user_account WHERE email = $1";
    $result = pg_query_params($conn, $query, [$email]);

    if ($result && pg_num_rows($result) === 1) {
        $user = pg_fetch_assoc($result);

        if (password_verify($password, $user['password_hash'])) {
            // ✅ Store session variables
            $_SESSION['user_email']  = $user['email'];
            $_SESSION['first_name']  = $user['first_name'];
            $_SESSION['last_name']   = $user['last_name'];
            $_SESSION['user_id']     = $user['user_id'];

            // ✅ Check if this user already has an applicant record
            $applicantCheck = "SELECT applicant_id FROM applicant WHERE user_id = $1";
            $applicantResult = pg_query_params($conn, $applicantCheck, [$user['user_id']]);

            if ($applicantResult && pg_num_rows($applicantResult) > 0) {
                // If applicant already exists, use it
                $applicant = pg_fetch_assoc($applicantResult);
                $_SESSION['applicant_id'] = $applicant['applicant_id'];
            } else {
                // If no applicant yet, create one
                $insertApplicant = "INSERT INTO applicant (user_id, last_name, first_name) 
                                    VALUES ($1, $2, $3) RETURNING applicant_id";
                $insertResult = pg_query_params($conn, $insertApplicant, [
                    $user['user_id'],
                    $user['last_name'],
                    $user['first_name']
                ]);

                if ($insertResult && pg_num_rows($insertResult) > 0) {
                    $newApplicant = pg_fetch_assoc($insertResult);
                    $_SESSION['applicant_id'] = $newApplicant['applicant_id'];
                }
            }

            // ✅ Redirect after login
            header("Location: /public/index.php");
            exit;
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Email not found.";
    }
}
?>
