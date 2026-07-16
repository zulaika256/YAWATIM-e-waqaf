<?php
// modules/settings.php - User Account Settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="chart-card" id="card-change-password">
        <div class="chart-header">
            <h3 class="chart-title"><i class="fa-solid fa-key" style="margin-right: 0.5rem; color: var(--primary-blue);"></i> Change Password</h3>
        </div>
        
        <form id="form-change-password">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Hook AJAX password submit
    handleFormSubmit('form-change-password', 'Password updated successfully!', () => {
        // Successful callback: wait 2 seconds, redirect to logout to re-authenticate
        showToast('Password changed! Logging out in 2 seconds...', 'success');
        setTimeout(() => {
            window.location.href = 'logout.php';
        }, 2000);
    });
});
</script>
