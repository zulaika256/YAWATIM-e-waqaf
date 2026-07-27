<?php
ob_start(); // Buffer output to prevent headers already sent errors
session_start();
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'wakalah_individual';
$page = $_GET['page'] ?? 'dashboard';

if ($role === 'admin') {
    $allowed_pages = ['dashboard', 'wakalah_individual', 'wakalah_corporate', 'donations', 'performance', 'reports', 'password'];
} elseif ($role === 'wakalah_corporate') {
    $allowed_pages = ['dashboard', 'donations', 'my_booth', 'performance', 'password'];
} else {
    // wakalah_individual — no booth access
    $allowed_pages = ['dashboard', 'donations', 'performance', 'password'];
}

if (!in_array($page, $allowed_pages)) {
    header('Location: index.php?page=dashboard');
    exit;
}

$titles = [
    'dashboard' => 'Dashboard Overview',
    'wakalah_individual' => 'Wakalah Individual Partner Registry',
    'wakalah_corporate' => 'Wakalah Corporate Partner Registry',
    'donations' => ($role === 'admin') ? 'Donation Collection Records' : 'My Donation Log',
    'performance' => ($role === 'admin') ? 'Wakalah Performance Rankings' : 'My Collection Statistics',
    'reports' => 'Analytical Reports',
    'password' => 'Account Settings',
    'my_booth' => 'My Booth'
];

$page_title = $titles[$page] ?? 'YAWATIM System';
$module_path = __DIR__ . "/modules/{$page}.php";

include_once __DIR__ . '/includes/header.php';

if (file_exists($module_path)) {
    include_once $module_path;
} else {
    echo "
    <div style='background-color: var(--white); padding: 2.5rem; border-radius: 12px; border: 1px solid var(--border-light); text-align: center;'>
        <i class='fa-solid fa-triangle-exclamation' style='font-size: 3rem; color: var(--alert-red); margin-bottom: 1rem;'></i>
        <h2 style='font-weight: 800; margin-bottom: 0.5rem;'>Module Not Found</h2>
        <p style='color: var(--light-neutral);'>The requested module view file is missing from the directory.</p>
    </div>
    ";
}

include_once __DIR__ . '/includes/footer.php';
