<?php
/** Processes CHO approval or denial of a PWD application. */
session_start();
header('Content-Type: application/json');

if (($_SESSION['role'] ?? '') !== 'doctor') {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

require_once __DIR__ . '/../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$app_id = (int)($input['application_id'] ?? 0);
$action = $input['action'] ?? '';
$remarks = trim($input['remarks'] ?? '');

if (!$app_id || !in_array($action, ['approve', 'deny'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid request. Required: application_id and action (approve/deny)']));
}

pg_query($conn, "BEGIN");

try {
    // Lock row for update
    $q = pg_query_params($conn,
        "SELECT status, workflow_status FROM application WHERE application_id = $1 FOR UPDATE",
        [$app_id]
    );

    $app = pg_fetch_assoc($q);

    if (!$app) {
        throw new Exception('Application not found');
    }

    if ($app['workflow_status'] !== 'cho_review') {
        throw new Exception('Application is not available for CHO review. Current status: ' . $app['workflow_status']);
    }

    $new_status = $action === 'approve' ? 'Approved' : 'Denied';
    $new_workflow = $action === 'approve' ? 'completed' : 'denied';
    $username = $_SESSION['username'] ?? 'cho_officer';

    // Update application
    $update_result = pg_query_params($conn,
        "UPDATE application
         SET status = $1,
             workflow_status = $2,
             approved_by = $3,
             approved_at = NOW(),
             remarks = $4,
             updated_at = NOW()
         WHERE application_id = $5",
        [$new_status, $new_workflow, $username, $remarks, $app_id]
    );

    if (!$update_result) {
        throw new Exception('Failed to update application');
    }

    // Add history record
    $history_result = pg_query_params($conn,
        "INSERT INTO application_status_history
         (application_id, from_status, to_status, changed_by, role, remarks)
         VALUES ($1, $2, $3, $4, 'cho', $5)",
        [$app_id, $app['status'], $new_status, $username, $remarks]
    );

    if (!$history_result) {
        throw new Exception('Failed to record history');
    }

    pg_query($conn, "COMMIT");

    echo json_encode([
        'success' => true,
        'message' => "Application $new_status successfully",
        'status' => $new_status,
        'workflow_status' => $new_workflow
    ]);

} catch (Exception $e) {
    pg_query($conn, "ROLLBACK");
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
