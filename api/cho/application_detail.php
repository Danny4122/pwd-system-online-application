<?php
/** Fetches detailed application info for CHO review. */
session_start();
header('Content-Type: application/json');

if (($_SESSION['role'] ?? '') !== 'doctor') {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

require_once __DIR__ . '/../../config/db.php';

$app_id = (int)($_GET['id'] ?? 0);

if (!$app_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'application id required']));
}

// Get application with applicant info
$q = pg_query_params($conn, "
    SELECT
        a.application_id,
        a.application_type,
        a.application_date,
        a.status,
        a.workflow_status,
        a.remarks,
        a.pic_1x1_path,
        a.created_at,
        a.updated_at,
        ap.applicant_id,
        ap.pwd_number,
        ap.first_name,
        ap.last_name,
        ap.middle_name,
        ap.suffix,
        ap.birthdate,
        ap.sex,
        ap.civil_status,
        ap.house_no_street,
        ap.barangay,
        ap.municipality,
        ap.province,
        ap.region,
        ap.landline_no,
        ap.mobile_no,
        ap.email_address
    FROM application a
    JOIN applicant ap ON a.applicant_id = ap.applicant_id
    WHERE a.application_id = $1
", [$app_id]);

$application = pg_fetch_assoc($q);

if (!$application) {
    http_response_code(404);
    exit(json_encode(['error' => 'Application not found']));
}

// Get draft data (form steps)
$drafts = [];
$q = pg_query_params($conn, "
    SELECT step, data
    FROM application_draft
    WHERE application_id = $1
    ORDER BY step
", [$app_id]);

while ($row = pg_fetch_assoc($q)) {
    $drafts[$row['step']] = json_decode($row['data'], true);
}

// Get documents
$q = pg_query_params($conn, "
    SELECT *
    FROM documentrequirements
    WHERE application_id = $1
", [$app_id]);

$documents = pg_fetch_assoc($q) ?: [];

// Get status history
$history = [];
$q = pg_query_params($conn, "
    SELECT hist_id, from_status, to_status, changed_by, role, remarks, created_at
    FROM application_status_history
    WHERE application_id = $1
    ORDER BY created_at DESC
", [$app_id]);

while ($row = pg_fetch_assoc($q)) {
    $history[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => [
        'application' => $application,
        'drafts' => $drafts,
        'documents' => $documents,
        'history' => $history
    ]
]);
