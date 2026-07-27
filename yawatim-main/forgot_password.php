<?php
// forgot_password.php - Direct password reset (no email token, XAMPP-compatible)
session_start();
require_once __DIR__ . '/database.php';

if (isset($_GET['cancel'])) {
    unset($_SESSION['reset_email']);
    header('Location: forgot_password.php');
    exit;
}

// If already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// ── Retrieve Session State ──────────────────────────────────────────────────
$step        = $_SESSION['forgot_step'] ?? 1;
$error_msg   = $_SESSION['forgot_error'] ?? '';
$success_msg = $_SESSION['forgot_success'] ?? '';
$post_email  = $_SESSION['post_email'] ?? '';
$post_id     = $_SESSION['post_id_number'] ?? '';

unset($_SESSION['forgot_error'], $_SESSION['forgot_success'], $_SESSION['post_email'], $_SESSION['post_id_number'], $_SESSION['forgot_step']);

// ── STEP 1: Verify email ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'verify_email') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $id_number = trim($_POST['id_number'] ?? '');

    $_SESSION['post_email'] = $_POST['email'] ?? '';
    $_SESSION['post_id_number'] = $_POST['id_number'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_error'] = 'Please enter a valid email address.';
        $_SESSION['forgot_step'] = 1;
        header('Location: forgot_password.php');
        exit;
    } elseif (empty($id_number)) {
        $_SESSION['forgot_error'] = 'Please enter your IC Number or SSM Number.';
        $_SESSION['forgot_step'] = 1;
        header('Location: forgot_password.php');
        exit;
    } else {
        try {
            $stmt = $pdo->prepare('
                SELECT u.user_id, u.role, w.ic_number, w.ssm_number 
                FROM users u 
                LEFT JOIN wakalah w ON u.wakalah_id = w.wakalah_id 
                WHERE u.email = ?
            ');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $valid = false;
                if ($user['role'] === 'wakalah_individual' && $user['ic_number'] === $id_number) {
                    $valid = true;
                } elseif ($user['role'] === 'wakalah_corporate' && $user['ssm_number'] === $id_number) {
                    $valid = true;
                }

                if ($valid) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['forgot_step'] = 2;
                    header('Location: forgot_password.php');
                    exit;
                } else {
                    $_SESSION['forgot_error'] = 'Verification failed. IC/SSM number does not match.';
                    $_SESSION['forgot_step'] = 1;
                }
            } else {
                $_SESSION['forgot_error'] = 'No account found with that email address.';
                $_SESSION['forgot_step'] = 1;
            }
        } catch (PDOException $e) {
            $_SESSION['forgot_error'] = 'Database error. Please try again.';
            $_SESSION['forgot_step'] = 1;
        }
        header('Location: forgot_password.php');
        exit;
    }
}

// ── STEP 2: Set new password ────────────────────────────────────────────────
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'reset_password') {
    $reset_email     = $_SESSION['reset_email'] ?? '';
    $new_password    = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($reset_email)) {
        // Session expired — restart
        header('Location: forgot_password.php');
        exit;
    }

    if (strlen($new_password) < 6) {
        $_SESSION['forgot_error'] = 'Password must be at least 6 characters long.';
        $_SESSION['forgot_step'] = 2;
        header('Location: forgot_password.php');
        exit;
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['forgot_error'] = 'Passwords do not match. Please try again.';
        $_SESSION['forgot_step'] = 2;
        header('Location: forgot_password.php');
        exit;
    } else {
        try {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
            $stmt->execute([$hash, $reset_email]);

            unset($_SESSION['reset_email']);
            $_SESSION['forgot_step'] = 3;
        } catch (PDOException $e) {
            $_SESSION['forgot_error'] = 'Failed to update password. Please try again.';
            $_SESSION['forgot_step'] = 2;
        }
        header('Location: forgot_password.php');
        exit;
    }
}

// ── Restore step from session if POST had errors ────────────────────────────
elseif (isset($_SESSION['reset_email']) && $step === 1) {
    $step = 2;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - YAWATIM System</title>
    <link rel="icon" type="image/png" href="img/logoyawatim.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Step indicator ─────────────────────────────── */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 1.75rem;
        }
        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            border: 2px solid var(--border-light, #e2e8f0);
            background: #fff;
            color: var(--light-neutral, #94a3b8);
            transition: all 0.3s;
            position: relative;
            z-index: 1;
        }
        .step-dot.active {
            background: var(--primary-blue, #1d4ed8);
            border-color: var(--primary-blue, #1d4ed8);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(29,78,216,0.15);
        }
        .step-dot.done {
            background: var(--success-green, #10b981);
            border-color: var(--success-green, #10b981);
            color: #fff;
        }
        .step-line {
            height: 2px;
            width: 64px;
            background: var(--border-light, #e2e8f0);
        }
        .step-line.done {
            background: var(--success-green, #10b981);
        }



        /* ── Success card ───────────────────────────────── */
        .success-card {
            text-align: center;
            padding: 1rem 0;
        }
        .success-icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2rem;
            color: #059669;
        }
        .success-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-neutral, #1e293b);
            margin-bottom: 0.5rem;
        }
        .success-card p {
            font-size: 0.9rem;
            color: var(--medium-neutral, #64748b);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="login-body" style="--login-accent: #1d4ed8; --login-accent-light: #eff6ff; --login-accent-dark: #1e40af;">
    <div class="login-card">

        <!-- Logo & Title -->
        <div class="login-header-group">
            <div class="login-logo" style="background: none; display: flex; justify-content: center; align-items: center; width: auto; height: auto; margin: 0 auto 1.25rem auto;">
                <img src="img/logoyawatim.png" alt="YAWATIM Logo" style="max-height: 72px; width: auto; object-fit: contain;">
            </div>
            <h2 class="login-title">Reset Password</h2>
            <p class="login-subtitle">YAWATIM Partner Account Recovery</p>
        </div>

        <!-- Step Indicator -->
        <?php if ($step < 3): ?>
        <div class="step-indicator">
            <div class="step-dot <?php echo $step >= 1 ? ($step > 1 ? 'done' : 'active') : ''; ?>">
                <?php echo $step > 1 ? '<i class="fa-solid fa-check"></i>' : '1'; ?>
            </div>
            <div class="step-line <?php echo $step > 1 ? 'done' : ''; ?>"></div>
            <div class="step-dot <?php echo $step >= 2 ? 'active' : ''; ?>">2</div>
        </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if (!empty($error_msg)): ?>
            <div class="login-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- ══════════════ STEP 1: Enter Email ══════════════ -->
        <?php if ($step === 1): ?>
            <p style="font-size: 0.875rem; color: var(--medium-neutral, #64748b); margin-bottom: 1.25rem; line-height: 1.6;">
                Enter the <strong>email address</strong> associated with your YAWATIM account and we'll let you choose a new password.
            </p>
            <form action="forgot_password.php" method="POST">
                <input type="hidden" name="step" value="verify_email">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="email" class="form-label">Registered Email Address</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-envelope" style="position: absolute; left: 14px; top: 12px; color: var(--light-neutral); font-size: 0.9rem;"></i>
                        <input type="email" name="email" id="email" class="form-input"
                               placeholder="e.g. partner@yawatim.org.my"
                               style="padding-left: 2.5rem;"
                               value="<?php echo htmlspecialchars($post_email); ?>"
                               required autofocus>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="id_number" class="form-label">IC Number (Individual) / SSM Number (Corporate)</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-id-card" style="position: absolute; left: 14px; top: 12px; color: var(--light-neutral); font-size: 0.9rem;"></i>
                        <input type="text" name="id_number" id="id_number" class="form-input"
                               placeholder="e.g. 890510-10-5555 or 1234567-X"
                               style="padding-left: 2.5rem;"
                               value="<?php echo htmlspecialchars($post_id); ?>"
                               required>
                    </div>
                </div>
                <button type="submit" class="login-submit-btn">
                    Verify Email <i class="fa-solid fa-arrow-right" style="margin-left: 0.25rem;"></i>
                </button>
            </form>

        <!-- ══════════════ STEP 2: Set New Password ══════════════ -->
        <?php elseif ($step === 2): ?>
            <?php $reset_email = $_SESSION['reset_email'] ?? ''; ?>
            <div style="display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 0.9rem; background: var(--light-green, #d1fae5); border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.83rem;">
                <i class="fa-solid fa-circle-check" style="color: var(--success-green, #10b981);"></i>
                <span style="color: var(--success-green, #059669); font-weight: 600;">Account found: <strong><?php echo htmlspecialchars($reset_email); ?></strong></span>
            </div>
            <p style="font-size: 0.875rem; color: var(--medium-neutral, #64748b); margin-bottom: 1.25rem; line-height: 1.6;">
                Create a <strong>new password</strong> for your account. It must be at least 6 characters long.
            </p>
            <form action="forgot_password.php" method="POST" id="form-reset">
                <input type="hidden" name="step" value="reset_password">
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 14px; top: 12px; color: var(--light-neutral); font-size: 0.9rem;"></i>
                        <input type="password" name="new_password" id="new_password" class="form-input"
                               placeholder="Min. 6 characters"
                               style="padding-left: 2.5rem; padding-right: 2.5rem;"
                               oninput="checkMatch()"
                               required autofocus>
                        <button type="button" onclick="toggleVis('new_password', this)"
                                style="position: absolute; right: 12px; top: 10px; background: none; border: none; cursor: pointer; color: var(--light-neutral); font-size: 0.9rem;" tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock-open" style="position: absolute; left: 14px; top: 12px; color: var(--light-neutral); font-size: 0.9rem;"></i>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input"
                               placeholder="Re-enter your new password"
                               style="padding-left: 2.5rem; padding-right: 2.5rem;"
                               oninput="checkMatch()"
                               required>
                        <button type="button" onclick="toggleVis('confirm_password', this)"
                                style="position: absolute; right: 12px; top: 10px; background: none; border: none; cursor: pointer; color: var(--light-neutral); font-size: 0.9rem;" tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-label" id="match-label" style="margin-top: 3px;"></div>
                </div>
                <button type="submit" class="login-submit-btn">
                    Update Password <i class="fa-solid fa-floppy-disk" style="margin-left: 0.25rem;"></i>
                </button>
            </form>
            <!-- Go back to re-enter email -->
            <div style="text-align: center; margin-top: 0.75rem;">
                <a href="forgot_password.php?cancel=1" style="font-size: 0.82rem; color: var(--light-neutral); text-decoration: none;">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i> Use a different email
                </a>
            </div>

        <!-- ══════════════ STEP 3: Success ══════════════ -->
        <?php elseif ($step === 3): ?>
            <div class="success-card">
                <div class="success-icon-circle">
                    <i class="fa-solid fa-shield-check"></i>
                </div>
                <h3>Password Updated!</h3>
                <p>Your password has been changed successfully.<br>You can now sign in with your new password.</p>
                <a href="login.php" class="login-submit-btn" style="display: block; text-decoration: none; text-align: center;">
                    Go to Sign In <i class="fa-solid fa-right-to-bracket" style="margin-left: 0.25rem;"></i>
                </a>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <?php if ($step !== 3): ?>
        <div class="login-footnote" style="margin-top: 1.25rem;">
            <p>Remembered your password? <a href="login.php">Sign In</a></p>
        </div>
        <?php endif; ?>
    </div>

    <script>
    // ── Toggle password visibility ──────────────────────────────────────────
    function toggleVis(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    }



    // ── Password match indicator ────────────────────────────────────────────
    function checkMatch() {
        const p1  = document.getElementById('new_password')?.value || '';
        const p2  = document.getElementById('confirm_password')?.value || '';
        const lbl = document.getElementById('match-label');
        if (!lbl || p2.length === 0) { if(lbl) lbl.textContent = ''; return; }
        if (p1 === p2) {
            lbl.textContent = '✓ Passwords match';
            lbl.style.color = '#10b981';
        } else {
            lbl.textContent = '✗ Passwords do not match';
            lbl.style.color = '#ef4444';
        }
    }
    </script>
</body>
</html>
