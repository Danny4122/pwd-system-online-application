<?php
/** Fetches applications for CHO with status filter, search, and pagination. */
session_start();
header('Content-Type: application/json');

if (($_SESSION['role'] ?? '') !== 'doctor') {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

require_once __DIR__ . '/../../config/db.php';

$status = $_GET['status'] ?? 'pending';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build WHERE clause based on status
switch ($status) {
    case 'approved':
        $where = "a.status = 'Approved'";
        break;
    case 'denied':
        $where = "a.status = 'Denied'";
        break;
    case 'pending':
    default:
        $where = "a.workflow_status = 'cho_review'";
        break;
}

$params = [];
$param_idx = 1;

// Add search condition
if ($search !== '') {
    $where .= " AND (ap.first_name ILIKE $" . $param_idx . " OR ap.last_name ILIKE $" . $param_idx . ")";
    $params[] = "%$search%";
    $param_idx++;
}

// Main query
$sql = "
    SELECT
        a.application_id,
        a.application_type,
        a.status,
        a.workflow_status,
        a.created_at,
        a.approved_at,
        a.remarks,
        ap.first_name,
        ap.last_name,
        ap.middle_name,
        ap.barangay,
        ap.municipality,
        ap.province
    FROM application a
    JOIN applicant ap ON a.applicant_id = ap.applicant_id
    WHERE $where
    ORDER BY a.created_at DESC
    LIMIT $limit OFFSET $offset
";

$result = $params
    ? pg_query_params($conn, $sql, $params)
    : pg_query($conn, $sql);

$applications = [];
while ($row = pg_fetch_assoc($result)) {
    $applications[] = $row;
}

// Get total count for pagination
$count_sql = "
    SELECT COUNT(*)
    FROM application a
    JOIN applicant ap ON a.applicant_id = ap.applicant_id
    WHERE $where
";

$count_result = $params
    ? pg_query_params($conn, $count_sql, $params)
    : pg_query($conn, $count_sql);

$total = (int)pg_fetch_result($count_result, 0, 0);

echo json_encode([
    'success' => true,
    'data' => $applications,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'pages' => (int)ceil($total / $limit)
    ]
]);
