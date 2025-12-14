<?php
/** CHO Sidebar navigation component. */
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="sidebar">
    <div class="logo">
        <img src="<?= APP_BASE_URL ?>/assets/pictures/cho-logo.png" width="44" alt="CHO" onerror="this.style.display='none'">
        <div>
            <h5 style="margin:0">CHO</h5>
            <small style="opacity:.85">City Health Office</small>
        </div>
    </div>
    <hr style="border-color:rgba(255,255,255,0.2)">

    <a href="<?= DOCTOR_BASE ?>/CHO_dashboard.php" class="<?= $current_page === 'CHO_dashboard' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i><span>Dashboard</span>
    </a>
    <a href="<?= DOCTOR_BASE ?>/pending.php" class="<?= $current_page === 'pending' ? 'active' : '' ?>">
        <i class="fas fa-hourglass-half"></i><span>Pending Reviews</span>
    </a>
    <a href="<?= DOCTOR_BASE ?>/accepted.php" class="<?= $current_page === 'accepted' ? 'active' : '' ?>">
        <i class="fas fa-check-circle"></i><span>Approved</span>
    </a>
    <a href="<?= DOCTOR_BASE ?>/denied.php" class="<?= $current_page === 'denied' ? 'active' : '' ?>">
        <i class="fas fa-times-circle"></i><span>Denied</span>
    </a>

    <div style="margin-top:auto">
        <a href="<?= APP_BASE_URL ?>/public/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
</div>
