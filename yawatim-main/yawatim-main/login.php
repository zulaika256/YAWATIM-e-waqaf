<?php
session_start();
require_once 'database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error_msg = '';
$active_channel = 'Admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = '';
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }
    
    $password = '';
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    $email_lower = strtolower($email);
    if ($email_lower == '') {
        $active_channel = 'Admin';
    } else if (strpos($email_lower, 'admin') !== false) {
        $active_channel = 'Admin';
    } else if (strpos($email_lower, 'bankrakyat') !== false || strpos($email_lower, 'rakyat') !== false || strpos($email_lower, 'siti') !== false) {
        $active_channel = 'Bank Rakyat';
    } else if (strpos($email_lower, 'pos') !== false || strpos($email_lower, 'posmalaysia') !== false || strpos($email_lower, 'chong') !== false) {
        $active_channel = 'Pos Malaysia';
    } else if (strpos($email_lower, 'ebb') !== false || strpos($email_lower, 'muthu') !== false) {
        $active_channel = 'EBB';
    } else if (strpos($email_lower, 'bsn') !== false || strpos($email_lower, 'safwan') !== false) {
        $active_channel = 'BSN';
    } else {
        $active_channel = 'Admin';
    }

    if ($email == '' || $password == '') {
        $error_msg = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $resolved_channel = $user['channel'];
                if ($resolved_channel == '') {
                    $resolved_channel = $active_channel;
                }
                
                $name = 'Partner';
                if ($user['role'] == 'admin') {
                    $name = 'Administrator';
                } else if ($user['wakalah_id']) {
                    $stmt_wak = $pdo->prepare('SELECT name, status FROM wakalah WHERE id = ?');
                    $stmt_wak->execute([$user['wakalah_id']]);
                    $wak = $stmt_wak->fetch();

                    if ($wak) {
                        if ($wak['status'] != 'Active') {
                            $error_msg = 'Your Wakalah account is currently inactive';
                        } else {
                            $name = $wak['name'];
                        }
                    } else {
                        $error_msg = 'Associated Wakalah record not found.';
                    }
                }

                if ($error_msg == '') {
                    if ($active_channel != 'Admin' && $resolved_channel != '' && $active_channel != $resolved_channel) {
                        $error_msg = 'You are not authorized for the ' . $active_channel . ' portal';
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

$theme_color = '#1d4ed8';
$theme_light = '#eff6ff';
$theme_dark = '#1e40af';
$theme_icon = 'fa-shield-halved';
$theme_badge = 'Admin Portal';
$theme_tagline = 'Secure access for administrators and channel partners';

if ($active_channel == 'BSN') {
    $theme_color = '#1d4ed8';
    $theme_light = '#eff6ff';
    $theme_dark = '#1e40af';
    $theme_icon = 'fa-building-columns';
    $theme_badge = 'BSN Portal';
    $theme_tagline = 'Empowering Wakalah partners with BSN';
} else if ($active_channel == 'Bank Rakyat') {
    $theme_color = '#b45309';
    $theme_light = '#fffbeb';
    $theme_dark = '#92400e';
    $theme_icon = 'fa-landmark';
    $theme_badge = 'Bank Rakyat Portal';
    $theme_tagline = 'Collaborating with Bank Kerjasama Rakyat Malaysia';
} else if ($active_channel == 'Pos Malaysia') {
    $theme_color = '#dc2626';
    $theme_light = '#fef2f2';
    $theme_dark = '#b91c1c';
    $theme_icon = 'fa-envelope-open-text';
    $theme_badge = 'Pos Malaysia Portal';
    $theme_tagline = 'Reaching every corner via the postal network';
} else if ($active_channel == 'EBB') {
    $theme_color = '#15803d';
    $theme_light = '#f0fdf4';
    $theme_dark = '#166534';
    $theme_icon = 'fa-coins';
    $theme_badge = 'EBB Portal';
    $theme_tagline = 'Driving impact through the EBB channel';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Login - YAWATIM System</title>
    <link rel="icon" type="image/png" href="img/logoyawatim.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body" style="--login-accent: <?php echo $theme_color; ?>; --login-accent-light: <?php echo $theme_light; ?>; --login-accent-dark: <?php echo $theme_dark; ?>;">
    <div class="login-card">
        <div class="login-header-group">
            <div class="login-logo" style="background: none; display: flex; justify-content: center; align-items: center; width: auto; height: auto; margin: 0 auto 1.5rem auto;">
                <img src="img/logoyawatim.png" alt="YAWATIM Logo" style="max-height: 80px; width: auto; object-fit: contain;">
            </div>
            <h2 class="login-title">YAWATIM Portal</h2>
            <p class="login-subtitle"><?php echo $theme_tagline; ?></p>
        </div>

        <?php if ($error_msg != '') { ?>
            <div class="login-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php } ?>

        <form action="login.php" method="POST" id="form-login">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-envelope" style="position: absolute; left: 14px; top: 12px; color: var(--light-neutral); font-size: 0.9rem;"></i>
                    <input type="email" name="email" id="email" class="form-input" placeholder="e.g. admin@yawatim.org.my" style="padding-left: 2.5rem;" value="<?php if(isset($_POST['email'])) { echo htmlspecialchars($_POST['email']); } ?>" required autofocus>
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
            <p><a href="register.php">Register as Wakalah</a></p>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
