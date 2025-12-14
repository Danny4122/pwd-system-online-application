<?php
/** CHO Application Review - view application details and approve/deny. */
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'doctor') {
    header('Location: ' . ADMIN_BASE . '/signin.php');
    exit;
}

$app_id = (int)($_GET['id'] ?? 0);
if (!$app_id) {
    header('Location: ' . DOCTOR_BASE . '/pending.php');
    exit;
}

// Get application with applicant info
$q = pg_query_params($conn, "
    SELECT
        a.application_id, a.application_type, a.application_date, a.status,
        a.workflow_status, a.remarks, a.pic_1x1_path, a.created_at, a.approved_by, a.approved_at,
        ap.applicant_id, ap.pwd_number, ap.first_name, ap.last_name, ap.middle_name,
        ap.suffix, ap.birthdate, ap.sex, ap.civil_status, ap.house_no_street,
        ap.barangay, ap.municipality, ap.province, ap.region,
        ap.landline_no, ap.mobile_no, ap.email_address
    FROM application a
    JOIN applicant ap ON a.applicant_id = ap.applicant_id
    WHERE a.application_id = $1
", [$app_id]);

$application = pg_fetch_assoc($q);

if (!$application) {
    header('Location: ' . DOCTOR_BASE . '/pending.php?error=not_found');
    exit;
}

// Get draft data
$drafts = [];
$q = pg_query_params($conn, "SELECT step, data FROM application_draft WHERE application_id = $1 ORDER BY step", [$app_id]);
while ($row = pg_fetch_assoc($q)) {
    $drafts[$row['step']] = json_decode($row['data'], true);
}

// Get documents
$q = pg_query_params($conn, "SELECT * FROM documentrequirements WHERE application_id = $1", [$app_id]);
$documents = pg_fetch_assoc($q) ?: [];

// Get status history
$history = [];
$q = pg_query_params($conn, "
    SELECT from_status, to_status, changed_by, role, remarks, created_at
    FROM application_status_history WHERE application_id = $1 ORDER BY created_at DESC
", [$app_id]);
while ($row = pg_fetch_assoc($q)) {
    $history[] = $row;
}

$can_review = $application['workflow_status'] === 'cho_review';
$step1 = $drafts[1] ?? [];
$step2 = $drafts[2] ?? [];
$step3 = $drafts[3] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHO - Review Application #<?= $app_id ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_BASE_URL ?>/assets/css/global/base.css">
    <style>
        body{background:#f6f7fb;font-family:Inter,system-ui,sans-serif;}
        .sidebar{width:260px;background:linear-gradient(180deg,#11174a,#163273);color:#fff;position:fixed;top:0;left:0;bottom:0;padding:18px;display:flex;flex-direction:column;}
        .sidebar .logo{display:flex;gap:10px;align-items:center;margin-bottom:8px}
        .sidebar a{display:flex;gap:10px;padding:10px;border-radius:8px;color:#fff;text-decoration:none;margin:2px 0}
        .sidebar a:hover,.sidebar a.active{background:rgba(255,255,255,0.1)}
        .main{margin-left:280px;padding:26px;min-height:100vh}
        .card{border:none;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-radius:10px;margin-bottom:16px}
        .section-title{font-weight:700;color:#11174a;border-bottom:2px solid #2f2fbf;padding-bottom:8px;margin-bottom:16px}
        .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
        .info-item label{font-size:.75rem;color:#6c757d;display:block;margin-bottom:2px}
        .info-item span{font-weight:500}
        .status-badge{padding:6px 16px;border-radius:20px;font-weight:600}
        .status-pending{background:#fff3cd;color:#856404}
        .status-approved{background:#d4edda;color:#155724}
        .status-denied{background:#f8d7da;color:#721c24}
        .action-buttons{position:sticky;bottom:0;background:#fff;padding:16px;border-top:1px solid #eee;margin:-16px;margin-top:20px}
        @media(max-width:900px){.main{margin-left:0}.sidebar{display:none}}
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= DOCTOR_BASE ?>/pending.php" class="text-decoration-none text-muted">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
                <h4 class="mt-2 mb-0">Application #<?= $app_id ?></h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="status-badge status-<?= strtolower($application['status']) ?>">
                    <?= htmlspecialchars($application['status']) ?>
                </span>
                <span class="badge bg-secondary"><?= htmlspecialchars($application['application_type']) ?></span>
            </div>
        </div>

        <!-- Applicant Info -->
        <div class="card">
            <div class="card-body">
                <h5 class="section-title"><i class="fas fa-user me-2"></i>Applicant Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Full Name</label>
                        <span><?= htmlspecialchars(trim($application['first_name'] . ' ' . ($application['middle_name'] ?? '') . ' ' . $application['last_name'] . ' ' . ($application['suffix'] ?? ''))) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Birthdate</label>
                        <span><?= $step1['birthdate'] ?? ($application['birthdate'] ? date('M d, Y', strtotime($application['birthdate'])) : 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Sex</label>
                        <span><?= htmlspecialchars($step1['sex'] ?? $application['sex'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Civil Status</label>
                        <span><?= htmlspecialchars($step1['civil_status'] ?? $application['civil_status'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Address</label>
                        <span><?= htmlspecialchars(($step1['house_no_street'] ?? $application['house_no_street'] ?? '') . ', ' . ($step1['barangay'] ?? $application['barangay'] ?? '') . ', ' . ($step1['municipality'] ?? $application['municipality'] ?? '')) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Province/Region</label>
                        <span><?= htmlspecialchars(($step1['province'] ?? $application['province'] ?? 'N/A') . ', Region ' . ($step1['region'] ?? $application['region'] ?? 'N/A')) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Mobile</label>
                        <span><?= htmlspecialchars($step1['mobile_no'] ?? $application['mobile_no'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <span><?= htmlspecialchars($step1['email_address'] ?? $application['email_address'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disability Info -->
        <div class="card">
            <div class="card-body">
                <h5 class="section-title"><i class="fas fa-wheelchair me-2"></i>Disability Information</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Disability Type</label>
                        <span><?= htmlspecialchars($step1['disability_type'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Cause</label>
                        <span><?= htmlspecialchars($step1['cause'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Cause Description</label>
                        <span><?= htmlspecialchars($step1['cause_description'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Family & Employment -->
        <?php if (!empty($step2)): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="section-title"><i class="fas fa-users me-2"></i>Family & Employment</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Father's Name</label>
                        <span><?= htmlspecialchars(trim(($step2['father_first_name'] ?? '') . ' ' . ($step2['father_middle_name'] ?? '') . ' ' . ($step2['father_last_name'] ?? '')) ?: 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Mother's Name</label>
                        <span><?= htmlspecialchars(trim(($step2['mother_first_name'] ?? '') . ' ' . ($step2['mother_middle_name'] ?? '') . ' ' . ($step2['mother_last_name'] ?? '')) ?: 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Guardian's Name</label>
                        <span><?= htmlspecialchars(trim(($step2['guardian_first_name'] ?? '') . ' ' . ($step2['guardian_middle_name'] ?? '') . ' ' . ($step2['guardian_last_name'] ?? '')) ?: 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Education</label>
                        <span><?= htmlspecialchars($step2['educational_attainment'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Employment Status</label>
                        <span><?= htmlspecialchars($step2['employment_status'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Occupation</label>
                        <span><?= htmlspecialchars($step2['occupation'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Emergency Contact -->
        <?php if (!empty($step3)): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="section-title"><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h5>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Contact Person</label>
                        <span><?= htmlspecialchars($step3['contact_person_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <label>Contact Number</label>
                        <span><?= htmlspecialchars($step3['contact_person_no'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Documents -->
        <?php if (!empty($documents)): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="section-title"><i class="fas fa-file-alt me-2"></i>Uploaded Documents</h5>
                <div class="d-flex flex-wrap gap-3">
                    <?php if ($documents['medicalcert_path'] ?? null): ?>
                    <a href="<?= APP_BASE_URL . htmlspecialchars($documents['medicalcert_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-medical me-1"></i> Medical Certificate
                    </a>
                    <?php endif; ?>
                    <?php if ($documents['barangaycert_path'] ?? null): ?>
                    <a href="<?= APP_BASE_URL . htmlspecialchars($documents['barangaycert_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file me-1"></i> Barangay Certificate
                    </a>
                    <?php endif; ?>
                    <?php if ($documents['bodypic_path'] ?? null): ?>
                    <a href="<?= APP_BASE_URL . htmlspecialchars($documents['bodypic_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-image me-1"></i> Body Photo
                    </a>
                    <?php endif; ?>
                    <?php if ($application['pic_1x1_path'] ?? null): ?>
                    <a href="<?= APP_BASE_URL . htmlspecialchars($application['pic_1x1_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-portrait me-1"></i> 1x1 Photo
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status History -->
        <?php if (!empty($history)): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="section-title"><i class="fas fa-history me-2"></i>Status History</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Date</th><th>From</th><th>To</th><th>By</th><th>Remarks</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($h['created_at'])) ?></td>
                                <td><?= htmlspecialchars($h['from_status'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($h['to_status']) ?></td>
                                <td><?= htmlspecialchars($h['changed_by']) ?> (<?= htmlspecialchars($h['role']) ?>)</td>
                                <td><?= htmlspecialchars($h['remarks'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <?php if ($can_review): ?>
        <div class="action-buttons">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    <i class="fas fa-info-circle me-1"></i> Review this application and take action
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#denyModal">
                        <i class="fas fa-times me-1"></i> Deny
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                </div>
            </div>
        </div>
        <?php elseif ($application['status'] === 'Approved'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            This application was approved by <?= htmlspecialchars($application['approved_by'] ?? 'N/A') ?>
            <?php if ($application['approved_at']): ?> on <?= date('M d, Y', strtotime($application['approved_at'])) ?><?php endif; ?>
        </div>
        <?php elseif ($application['status'] === 'Denied'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            This application was denied.
            <?php if ($application['remarks']): ?><br><strong>Reason:</strong> <?= htmlspecialchars($application['remarks']) ?><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check me-2"></i>Approve Application</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this application?</p>
                    <div class="mb-3">
                        <label class="form-label">Remarks (optional)</label>
                        <textarea id="approveRemarks" class="form-control" rows="2" placeholder="Add any notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitAction('approve')">
                        <i class="fas fa-check me-1"></i> Confirm Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Deny Modal -->
    <div class="modal fade" id="denyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times me-2"></i>Deny Application</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to deny this application?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Denial <span class="text-danger">*</span></label>
                        <textarea id="denyRemarks" class="form-control" rows="3" placeholder="Please provide a reason..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitAction('deny')">
                        <i class="fas fa-times me-1"></i> Confirm Denial
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function submitAction(action) {
            const remarks = action === 'approve'
                ? document.getElementById('approveRemarks').value
                : document.getElementById('denyRemarks').value;

            if (action === 'deny' && !remarks.trim()) {
                alert('Please provide a reason for denial');
                return;
            }

            try {
                const response = await fetch('<?= APP_BASE_URL ?>/api/cho/application_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        application_id: <?= $app_id ?>,
                        action: action,
                        remarks: remarks
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.href = '<?= DOCTOR_BASE ?>/' + (action === 'approve' ? 'accepted' : 'denied') + '.php';
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Network error. Please try again.');
                console.error(error);
            }
        }
    </script>
</body>
</html>
