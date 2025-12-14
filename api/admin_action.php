<?php
// api/admin_action.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/workflow.php'; // returns array

$workflow = include __DIR__ . '/../includes/workflow.php';

// Basic request validation
$action = $_POST['action'] ?? '';
$appId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
$csrf = $_POST['csrf_token'] ?? '';

if (!$action || !$appId) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing action or application_id']);
    exit;
}

// CSRF check
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$csrf)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Invalid CSRF token']);
    exit;
}

// Check logged in role (adjust depending on where you store role)
$session_role = strtoupper($_SESSION['role'] ?? ($_SESSION['user_role'] ?? (!empty($_SESSION['is_admin']) ? 'ADMIN' : '')));

if (!isset($workflow[$action])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Unknown action']);
    exit;
}
$meta = $workflow[$action];

// role check: allow if session role exists in allowed_roles
$allowed = array_map('strtoupper', $meta['allowed_roles'] ?? []);
if (!in_array($session_role, $allowed, true)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Not allowed for your role', 'details'=>['session_role'=>$session_role,'required_role'=>$allowed]]);
    exit;
}

// remarks validation
$remarks = trim((string)($_POST['remarks'] ?? ''));
if (!empty($meta['require_remarks']) && $meta['require_remarks'] && $remarks === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Remarks required for this action']);
    exit;
}

// Determine new status
$newStatus = $meta['to_status'] ?? null;
if ($newStatus === null) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Invalid workflow target status']);
    exit;
}

// Start transaction
pg_query($conn, 'BEGIN');
$ok = true;
$error = null;

// Update application status
$updateSql = "UPDATE application SET status = $1, updated_at = now() WHERE application_id = $2";
$res = pg_query_params($conn, $updateSql, [$newStatus, $appId]);
if ($res === false || pg_affected_rows($res) === 0) {
    $ok = false;
    $error = 'DB update failed: ' . pg_last_error($conn);
}

// Insert into history (if update ok)
if ($ok) {
    // get previous status for history (optional)
    $prev = null;
    $q = pg_query_params($conn, "SELECT status FROM application WHERE application_id = $1 LIMIT 1", [$appId]);
    if ($q && pg_num_rows($q) > 0) {
        $row = pg_fetch_assoc($q);
        $prev = $row['status'] ?? null;
    }

    $insertSql = "INSERT INTO application_status_history (application_id, from_status, to_status, changed_by, role, remarks, created_at)
                  VALUES ($1, $2, $3, $4, $5, $6, now())";
    $changedBy = $_SESSION['user_id'] ?? 0;
    $roleForHistory = $session_role;
    $res2 = pg_query_params($conn, $insertSql, [$appId, $prev, $newStatus, $changedBy, $roleForHistory, $remarks]);
    if ($res2 === false) {
        $ok = false;
        $error = 'DB insert history failed: ' . pg_last_error($conn);
    }
}

// commit/rollback
if ($ok) {
    pg_query($conn, 'COMMIT');
    $out = ['success'=>true, 'redirect'=>$meta['redirect'] ?? null];
    echo json_encode($out);
    exit;
} else {
    pg_query($conn, 'ROLLBACK');
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$error]);
    exit;
}
