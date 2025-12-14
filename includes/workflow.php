<?php
// includes/workflow.php
// Map action name => metadata
return [
    // PDAO actions (performed by PDAO users)
    'forward_to_cho' => [
        'to_status' => 'For CHO Verification',
        'allowed_roles' => ['PDAO','ADMIN'],
        'require_remarks' => false,
        'redirect' => '/src/doctor/accepted.php'
    ],
    'request_more_info' => [
        'to_status' => 'Pending - More Info Requested',
        'allowed_roles' => ['PDAO','ADMIN'],
        'require_remarks' => true,
        'redirect' => null
    ],
    'reject' => [
        'to_status' => 'CHO Rejected', // you could also use 'Denied' if you prefer
        'allowed_roles' => ['PDAO','ADMIN'],
        'require_remarks' => true,
        'redirect' => null
    ],

    // CHO actions
    'cho_verify' => [
        'to_status' => 'Approved',
        'allowed_roles' => ['CHO','DOCTOR','ADMIN'],
        'require_remarks' => false,
        'redirect' => '/src/doctor/accepted.php'
    ],
    'cho_reject' => [
        'to_status' => 'CHO Rejected',
        'allowed_roles' => ['CHO','DOCTOR'],
        'require_remarks' => true,
        'redirect' => null
    ],

    // Finalize issuance (PDAO after CHO verified)
    'finalize_issue_id' => [
        'to_status' => 'Approved',
        'allowed_roles' => ['PDAO','ADMIN'],
        'require_remarks' => false,
        'redirect' => '/src/admin_side/members.php'
    ],
];
