<?php
// register.php - Self-registration for individuals and corporates
session_start();
require_once 'database.php';

// If already logged in, redirect to index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_msg = '';
$success_msg = '';

$malaysian_states = array(
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 
    'Penang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'WP Kuala Lumpur', 'WP Putrajaya', 'WP Labuan'
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = 'individual';
    if (isset($_POST['type'])) {
        $type = $_POST['type'];
    }
    
    $password = '';
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    // Only corporate requires state branch from registration:
    $state = '';
    if ($type == 'corporate' && isset($_POST['state'])) {
        $state = $_POST['state'];
    }

    if ($type == 'corporate' && $state == '') {
        $error_msg = 'Please select your state branch.';
    } else if (strlen($password) < 6) {
        $error_msg = 'Password must be at least 6 characters long.';
    } else {
        $email = '';
        $phone = '';
        $name = '';
        $ic_number = '';
        $representative = '';
        $ssm_number = '';
        $hq_address = '';
        
        if ($type == 'individual') {
            if (isset($_POST['name'])) { $name = trim($_POST['name']); }
            if (isset($_POST['ic_number'])) { $ic_number = trim($_POST['ic_number']); }
            if (isset($_POST['email'])) { $email = trim($_POST['email']); }
            if (isset($_POST['phone'])) { $phone = trim($_POST['phone']); }

            if ($name == '' || $ic_number == '' || $email == '' || $phone == '') {
                $error_msg = 'All individual fields are required.';
            }
        } else {
            // Corporate
            if (isset($_POST['company_name'])) { $name = trim($_POST['company_name']); }
            if (isset($_POST['company_representative'])) { $representative = trim($_POST['company_representative']); }
            if (isset($_POST['ssm_number'])) { $ssm_number = trim($_POST['ssm_number']); }
            if (isset($_POST['hq_address'])) { $hq_address = trim($_POST['hq_address']); }
            if (isset($_POST['company_email'])) { $email = trim($_POST['company_email']); }
            if (isset($_POST['company_phone'])) { $phone = trim($_POST['company_phone']); }

            if ($name == '' || $representative == '' || $ssm_number == '' || $hq_address == '' || $email == '' || $phone == '') {
                $error_msg = 'All corporate fields are required.';
            }
        }

        if ($error_msg == '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_msg = 'Invalid email address format.';
            } else {
                try {
                    // Check if email already exists in users table
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                    $stmt->execute(array($email));
                    $count = $stmt->fetchColumn();
                    if ($count > 0) {
                        $error_msg = 'Email address is already registered.';
                    } else {
                        // Start Transaction
                        $pdo->beginTransaction();

                        if ($type == 'individual') {
                            // Insert into wakalah (individual state is set to '-' default, address NULL)
                            $stmt_wak = $pdo->prepare("INSERT INTO wakalah (type, name, email, phone, state, status, ic_number, address) VALUES ('individual', ?, ?, ?, '-', 'Active', ?, NULL)");
                            $stmt_wak->execute(array($name, $email, $phone, $ic_number));
                            $wak_id = $pdo->lastInsertId();

                            // Insert into users
                            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt_user = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id) VALUES (?, ?, 'wakalah_individual', '-', ?)");
                            $stmt_user->execute(array($email, $pass_hash, $wak_id));
                        } else {
                            // Insert corporate into wakalah (address is NULL)
                            $stmt_wak = $pdo->prepare("INSERT INTO wakalah (type, name, email, phone, state, status, company_representative, ssm_number, hq_address, address) VALUES ('corporate', ?, ?, ?, ?, 'Active', ?, ?, ?, NULL)");
                            $stmt_wak->execute(array($name, $email, $phone, $state, $representative, $ssm_number, $hq_address));
                            $wak_id = $pdo->lastInsertId();

                            // Insert into users
                            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt_user = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id) VALUES (?, ?, 'wakalah_corporate', ?, ?)");
                            $stmt_user->execute(array($email, $pass_hash, $state, $wak_id));
                        }

                        $pdo->commit();
                        $success_msg = 'Registration successful! You can now log in.';
                    }
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error_msg = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - YAWATIM System</title>
    <link rel="icon" type="image/png" href="img/logoyawatim.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-body {
            background-color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 0;
        }
        .register-card {
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 580px;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin: 1.5rem;
        }
        .tabs-header {
            display: flex;
            border-bottom: 2px solid var(--border-light);
            margin-bottom: 1rem;
        }
        .tab-btn {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--light-neutral);
            border: none;
            background: none;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .tab-btn.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
    </style>
</head>
<body class="register-body">
    <div class="register-card">
        <div class="login-header-group" style="text-align: center;">
            <div class="login-logo" style="margin: 0 auto 1.5rem auto; background: none; display: flex; justify-content: center; align-items: center; width: auto; height: auto;">
                <img src="img/logoyawatim.png" alt="YAWATIM Logo" style="max-height: 80px; width: auto; object-fit: contain;">
            </div>
            <h2 class="login-title">YAWATIM Portal</h2>
            <p class="login-subtitle">Wakalah Partner Registration</p>
        </div>

        <?php if ($error_msg != '') { ?>
            <div style="background-color: var(--light-red); color: var(--alert-red); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
        <?php } ?>

        <?php if ($success_msg != '') { ?>
            <div style="background-color: var(--light-green); color: var(--success-green); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.2); font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo htmlspecialchars($success_msg); ?></span>
            </div>
        <?php } ?>

        <!-- Form Tabs Selection -->
        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab('individual')">Individual Wakalah</button>
            <button class="tab-btn" onclick="switchTab('corporate')">Corporate Wakalah</button>
        </div>

        <form action="register.php" method="POST" id="form-register">
            <!-- Hidden register type field -->
            <input type="hidden" name="type" id="register_type" value="individual">

            <!-- INDIVIDUAL FORM FIELDS -->
            <div id="section-individual" class="form-section active">
                <div class="form-group">
                    <label class="form-label" for="ind_name">Full Name</label>
                    <input type="text" name="name" id="ind_name" class="form-input" placeholder="e.g. Ahmad bin Ali">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="ind_ic">IC Number</label>
                        <input type="text" name="ic_number" id="ind_ic" class="form-input" placeholder="e.g. 890510-10-5555">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ind_phone">Phone Number</label>
                        <input type="text" name="phone" id="ind_phone" class="form-input" placeholder="e.g. 012-3456789">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ind_email">Email Address</label>
                    <input type="email" name="email" id="ind_email" class="form-input" placeholder="e.g. ahmad@gmail.com">
                </div>
            </div>

            <!-- CORPORATE FORM FIELDS -->
            <div id="section-corporate" class="form-section">
                <!-- Dropdown: State branch (ONLY for corporate) -->
                <div class="form-group">
                    <label class="form-label" for="reg_state">Select State Branch office</label>
                    <select name="state" id="reg_state" class="form-select">
                        <option value="">-- Select State Branch --</option>
                        <?php foreach ($malaysian_states as $state) { ?>
                            <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="corp_name">Company Name</label>
                    <input type="text" name="company_name" id="corp_name" class="form-input" placeholder="e.g. Syarikat Prihatin Sdn Bhd">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="corp_rep">Company Representative Name</label>
                        <input type="text" name="company_representative" id="corp_rep" class="form-input" placeholder="e.g. Mr. Tan Wei Beng">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="corp_ssm">SSM Registration Number</label>
                        <input type="text" name="ssm_number" id="corp_ssm" class="form-input" placeholder="e.g. 1234567-X">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="corp_address">HQ Registered Address</label>
                    <input type="text" name="hq_address" id="corp_address" class="form-input" placeholder="e.g. No. 1, Jalan Tebrau, JB">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="corp_email">Company Email Address</label>
                        <input type="email" name="company_email" id="corp_email" class="form-input" placeholder="e.g. finance@corp.com.my">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="corp_phone">Company Phone Number</label>
                        <input type="text" name="company_phone" id="corp_phone" class="form-input" placeholder="e.g. 03-5558888">
                    </div>
                </div>
            </div>

            <!-- Shared Field: Password -->
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="password">Account Password</label>
                <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem; border-radius: 8px;">
                Register Partner Account <i class="fa-solid fa-user-plus" style="margin-left: 0.25rem;"></i>
            </button>
        </form>

        <div style="text-align: center; font-size: 0.85rem; font-weight: 500; margin-top: 0.5rem;">
            Already have an account? <a href="login.php" style="color: var(--primary-blue); font-weight: 700;">Sign In here</a>
        </div>
    </div>

    <script>
        function switchTab(tabType) {
            // Update hidden input
            document.getElementById('register_type').value = tabType;

            // Toggle tab buttons class
            var tabButtons = document.querySelectorAll('.tab-btn');
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }

            if (tabType == 'individual') {
                tabButtons[0].classList.add('active');
                document.getElementById('section-individual').classList.add('active');
                document.getElementById('section-corporate').classList.remove('active');
                
                // Toggle required fields
                document.getElementById('ind_name').required = true;
                document.getElementById('ind_ic').required = true;
                document.getElementById('ind_email').required = true;
                document.getElementById('ind_phone').required = true;

                document.getElementById('reg_state').required = false;
                document.getElementById('corp_name').required = false;
                document.getElementById('corp_rep').required = false;
                document.getElementById('corp_ssm').required = false;
                document.getElementById('corp_address').required = false;
                document.getElementById('corp_email').required = false;
                document.getElementById('corp_phone').required = false;
            } else {
                tabButtons[1].classList.add('active');
                document.getElementById('section-individual').classList.remove('active');
                document.getElementById('section-corporate').classList.add('active');

                // Toggle required fields
                document.getElementById('ind_name').required = false;
                document.getElementById('ind_ic').required = false;
                document.getElementById('ind_email').required = false;
                document.getElementById('ind_phone').required = false;

                document.getElementById('reg_state').required = true;
                document.getElementById('corp_name').required = true;
                document.getElementById('corp_rep').required = true;
                document.getElementById('corp_ssm').required = true;
                document.getElementById('corp_address').required = true;
                document.getElementById('corp_email').required = true;
                document.getElementById('corp_phone').required = true;
            }
        }

        // Trigger on load to set initial required values
        window.addEventListener('DOMContentLoaded', function() {
            switchTab('individual');
        });
    </script>
</body>
</html>
