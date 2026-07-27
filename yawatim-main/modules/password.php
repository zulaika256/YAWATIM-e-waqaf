<?php
// modules/password.php - User Account Settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/notification.php';

// ===== POST HANDLER: CHANGE PASSWORD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        set_flash('error', 'All password fields are required.');
        header("Location: index.php?page=password");
        exit;
    }

    if ($new_password !== $confirm_password) {
        set_flash('error', 'New password and confirmation do not match.');
        header("Location: index.php?page=password");
        exit;
    }

    if (strlen($new_password) < 6) {
        set_flash('error', 'New password must be at least 6 characters long.');
        header("Location: index.php?page=password");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password_hash'])) {
            set_flash('error', 'Current password is incorrect.');
            header("Location: index.php?page=password");
            exit;
        }

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$new_hash, $user_id]);

        // Redirect to logout so user re-authenticates
        set_flash('success', 'Password changed successfully! Please log in again.');
        header("Location: logout.php");
        exit;

    } catch (PDOException $e) {
        set_flash('error', 'Database error: ' . $e->getMessage());
        header("Location: index.php?page=password");
        exit;
    }
}
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="chart-card" id="card-change-password">
        <div class="chart-header">
            <h3 class="chart-title"><i class="fa-solid fa-key" style="margin-right: 0.5rem; color: var(--primary-blue);"></i> Change Password</h3>
        </div>
        
        <form method="POST" action="index.php?page=password" id="form-change-password">
            <input type="hidden" name="action" value="change_password">
            
            <div class="modal-body" style="padding: 0;">
                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-input" placeholder="Enter current password" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-input" placeholder="Min. 6 characters" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Repeat new password" required>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" id="btn-submit-password">
                    <i class="fa-solid fa-lock"></i> Update Password
                </button>
            </div>
        </form>
    </div>
</div>
