<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_email = $_SESSION['email'] ?? 'User';
$current_role = $_SESSION['role'] ?? 'wakalah_individual';
$current_name = $_SESSION['name'] ?? 'YAWATIM Partner';
$current_channel = $_SESSION['channel'] ?? 'Admin';

$channel_themes = [
    'Admin' => ['accent' => '#1d4ed8', 'accent_dark' => '#1e40af', 'accent_soft' => '#eff6ff'],
    'BSN' => ['accent' => '#1d4ed8', 'accent_dark' => '#1e40af', 'accent_soft' => '#eff6ff'],
    'Bank Rakyat' => ['accent' => '#b45309', 'accent_dark' => '#92400e', 'accent_soft' => '#fffbeb'],
    'Pos Malaysia' => ['accent' => '#dc2626', 'accent_dark' => '#b91c1c', 'accent_soft' => '#fef2f2'],
    'EBB' => ['accent' => '#15803d', 'accent_dark' => '#166534', 'accent_soft' => '#f0fdf4']
];
$theme = $channel_themes[$current_channel] ?? $channel_themes['Admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>YAWATIM Donation Monitoring System</title>
    <meta name="description" content="YAWATIM Donation Monitoring System.">
    <meta name="author" content="YAWATIM Developer">
    <link rel="icon" type="image/png" href="img/logoyawatim.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-blue: <?php echo htmlspecialchars($theme['accent']); ?>;
            --primary-blue-hover: <?php echo htmlspecialchars($theme['accent_dark']); ?>;
            --light-blue: <?php echo htmlspecialchars($theme['accent_soft']); ?>;
            --border-blue: <?php echo htmlspecialchars($theme['accent']); ?>;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include_once __DIR__ . '/sidebar.php'; ?>
        <div class="main-wrapper">
            <header class="header" id="yawatim-header">
                <div class="header-title-section">
                    <button class="menu-toggle" id="btn-sidebar-toggle" aria-label="Toggle Navigation Sidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1 class="header-page-title" id="page-main-heading"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                </div>

                <div class="header-user-section">
                    <?php if (($current_role ?? '') !== 'wakalah_individual'): ?>
                    <div class="user-state-badge" id="header-user-state-badge" title="Your Assigned State Branch">
                        <i class="fa-solid fa-circle"></i>
                        <span><?php echo htmlspecialchars($current_channel ?: 'YAWATIM'); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="header-date-badge">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span id="system-time-display"><?php echo date('d M Y, h:i A'); ?></span>
                    </div>

                    <a href="logout.php" class="logout-btn" id="btn-logout" title="Log Out of System">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Log Out</span>
                    </a>
                </div>
            </header>

            <main class="content-body" id="main-content-area">
