<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: /src/admin_side/dashboard.php');
} elseif (isset($_SESSION['cho_id'])) {
    header('Location: /src/doctor/CHO_dashboard.php');
} elseif (isset($_SESSION['applicant_id'])) {
    header('Location: /public/index.php');
} else {
    header('Location: /public/login_form.php');
}
exit;
