<?php
/** Returns CHO dashboard statistics for pending, approved, and denied applications. */
session_start();
header('Content-Type: application/json');

if (($_SESSION['role'] ?? '') !== 'doctor') {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

require_once __DIR__ . '/../../config/db.php';

$stats = [
    'pending' => 0,
    'approved' => 0,
    'denied' => 0,
    'total' => 0,
    'approved_today' => 0,
    'monthly' => array_fill(0, 12, 0)
];

// Get application counts by status
$q = pg_query($conn, "
    SELECT
        COUNT(*) FILTER (WHERE workflow_status = 'cho_review') as pending,
        COUNT(*) FILTER (WHERE status = 'Approved') as approved,
        COUNT(*) FILTER (WHERE status = 'Denied') as denied,
        COUNT(*) as total,
        COUNT(*) FILTER (WHERE status = 'Approved' AND DATE(approved_at) = CURRENT_DATE) as approved_today
    FROM application
");

if ($row = pg_fetch_assoc($q)) {
    $stats['pending'] = (int)$row['pending'];
    $stats['approved'] = (int)$row['approved'];
    $stats['denied'] = (int)$row['denied'];
    $stats['total'] = (int)$row['total'];
    $stats['approved_today'] = (int)$row['approved_today'];
}

// Get monthly application counts for current year
$q = pg_query($conn, "
    SELECT EXTRACT(MONTH FROM created_at)::int as month, COUNT(*) as cnt
    FROM application
    WHERE EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_DATE)
    GROUP BY month
    ORDER BY month
");

while ($row = pg_fetch_assoc($q)) {
    $stats['monthly'][(int)$row['month'] - 1] = (int)$row['cnt'];
}

// Get application type distribution
$stats['by_type'] = ['New' => 0, 'Renewal' => 0, 'Lost ID' => 0];
$q = pg_query($conn, "
    SELECT
        CASE
            WHEN application_type IN ('New', 'new') THEN 'New'
            WHEN application_type IN ('Renewal', 'renewal') THEN 'Renewal'
            ELSE 'Lost ID'
        END as app_type,
        COUNT(*) as cnt
    FROM application
    GROUP BY app_type
");

while ($row = pg_fetch_assoc($q)) {
    $stats['by_type'][$row['app_type']] = (int)$row['cnt'];
}

echo json_encode(['success' => true, 'data' => $stats]);
