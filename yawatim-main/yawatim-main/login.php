<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

function detectChannelFromEmail($email) {
    $email = strtolower(trim($email ?? ''));
    if ($email === '') {
        return 'Admin';
    }
    if (strpos($email, 'admin') !== false) {
        return 'Admin';
    }
    if (strpos($email, 'bankrakyat') !== false || strpos($email, 'rakyat') !== false || strpos($email, 'siti') !== false) {
        return 'Bank Rakyat';
    }
    if (strpos($email, 'pos') !== false || strpos($email, 'posmalaysia') !== false || strpos($email, 'chong') !== false) {
        return 'Pos Malaysia';
    }
    if (strpos($email, 'ebb') !== false || strpos($email, 'muthu') !== false) {
        return 'EBB';
    }
    if (strpos($email, 'bsn') !== false || strpos($email, 'safwan') !== false) {
        return 'BSN';
    }
    return 'Admin';
}

function getThemeForChannel($channel) {
    $channel = $channel ?? 'Admin';
    $themes = [
        'Admin' => ['name' => 'Admin', 'color' => '#1d4ed8', 'light' => '#eff6ff', 'dark' => '#1e40af', 'icon' => 'fa-shield-halved', 'badge' => 'Admin Portal', 'tagline' => 'Secure access for administrators and channel partners'],
        'BSN' => ['name' => 'BSN', 'color' => '#1d4ed8', 'light' => '#eff6ff', 'dark' => '#1e40af', 'icon' => 'fa-building-columns', 'badge' => 'BSN Portal', 'tagline' => 'Empowering Wakalah partners with BSN'],
        'Bank Rakyat' => ['name' => 'Bank Rakyat', 'color' => '#b45309', 'light' => '#fffbeb', 'dark' => '#92400e', 'icon' => 'fa-landmark', 'badge' => 'Bank Rakyat Portal', 'tagline' => 'Collaborating with Bank Kerjasama Rakyat Malaysia'],
        'Pos Malaysia' => ['name' => 'Pos Malaysia', 'color' => '#dc2626', 'light' => '#fef2f2', 'dark' => '#b91c1c', 'icon' => 'fa-envelope-open-text', 'badge' => 'Pos Malaysia Portal', 'tagline' => 'Reaching every corner via the postal network'],
        'EBB' => ['name' => 'EBB', 'color' => '#15803d', 'light' => '#f0fdf4', 'dark' => '#166534', 'icon' => 'fa-coins', 'badge' => 'EBB Portal', 'tagline' => 'Driving impact through the EBB channel']
    ];

    return $themes[$channel] ?? $themes['Admin'];
}

$error_msg = '';
$active_channel = 'Admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $active_channel = detectChannelFromEmail($email);

    if (empty($email) || empty($password)) {
        $error_msg = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $resolved_channel = $user['channel'] ?: $active_channel;
                $name = 'Partner';

                if ($user['role'] === 'admin') {
                    $name = 'Administrator';
                } elseif ($user['wakalah_id']) {
                    $stmt_wak = $pdo->prepare('SELECT name, status FROM wakalah WHERE id = ?');
                    $stmt_wak->execute([$user['wakalah_id']]);
                    $wak = $stmt_wak->fetch();

                    if ($wak) {
                        if ($wak['status'] !== 'Active') {
                            $error_msg = 'Your Wakalah account is currently inactive';
                        } else {
                            $name = $wak['name'];
                        }
                    } else {
                        $error_msg = 'Associated Wakalah record not found.';
                    }
                }

                if (empty($error_msg)) {
                    if ($active_channel !== 'Admin' && $resolved_channel && $active_channel !== $resolved_channel) {
                        $error_msg = 'You are not authorized for the ' . htmlspecialchars($active_channel) . ' portal';
                    } else {
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['state'] = $user['state'];
                        $_SESSION['wakalah_id'] = $user['wakalah_id'];
                        $_SESSION['channel'] = $resolved_channel;
                        $_SESSION['name'] = $name;

                        header('Location: index.php');
                        exit;
                    }
                }
            } else {
                $error_msg = 'Invalid email address or password.';
            }
        } catch (PDOException $e) {
            $error_msg = 'Database error: ' . $e->getMessage();
        }
    }
}

$theme = getThemeForChannel($active_channel);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Login - YAWATIM System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body" style="--login-accent: <?php echo htmlspecialchars($theme['color']); ?>; --login-accent-light: <?php echo htmlspecialchars($theme['light']); ?>; --login-accent-dark: <?php echo htmlspecialchars($theme['dark']); ?>;">
    <div class="login-card">
        <div class="login-header-group">
            <div class="login-logo" style="background: none; display: flex; justify-content: center; align-items: center; width: auto; height: auto; margin: 0 auto 1.5rem auto;">
                <img src="img/logoyawatim.png" alt="YAWATIM Logo" style="max-height: 80px; width: auto; object-fit: contain;">
            </div>
            <h2 class="login-title">YAWATIM Portal</h2>
            <p class="login-subtitle"><?php echo htmlspecialchars($theme['tagline']); ?></p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="login-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="form-login">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-envelope" style="position: absolute; left: 14px; top: 12px; color: var(--light-neutral); font-size: 0.9rem;"></i>
                    <input type="email" name="email" id="email" class="form-input" placeholder="e.g. admin@yawatim.org.my" style="padding-left: 2.5rem;" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="password" class="form-label">Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 14px; top: 12px; color: var(--light-neutral); font-size: 0.9rem;"></i>
                    <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" style="padding-left: 2.5rem;" required>
                </div>
            </div>

            <button type="submit" class="login-submit-btn">
                Sign In <i class="fa-solid fa-right-to-bracket" style="margin-left: 0.25rem;"></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 0.85rem;">
            <a href="forgot_password.php" style="font-size: 0.85rem; color: var(--login-accent, #1d4ed8); font-weight: 600; text-decoration: none;">
                <i class="fa-solid fa-key" style="margin-right: 0.3rem; font-size: 0.8rem;"></i>Forgot Password?
            </a>
        </div>

        <div class="login-footnote">
            <p>Enter your registered email and password to continue.</p>
            <p><a href="register.php">Need a new account?</a></p>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
