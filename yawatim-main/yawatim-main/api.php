<?php
// api.php - Backend AJAX API handler (Wakalah Refactored)
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

$user_role = $_SESSION['role'];
$user_wakalah_id = $_SESSION['wakalah_id'] ?? null;

// Helper to send json response
function json_resp($status, $message, $extra = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}

// Get raw input if JSON is sent
$input = [];
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$action = $_GET['action'] ?? $input['action'] ?? '';

if (!$action) {
    json_resp('error', 'Action not specified.');
}

// ADMIN-ONLY ACTIONS
$admin_actions = ['add_wakalah_individual', 'edit_wakalah_individual', 'add_wakalah_corporate', 'edit_wakalah_corporate', 'toggle_wakalah_status'];
if (in_array($action, $admin_actions) && $user_role !== 'admin') {
    http_response_code(403);
    json_resp('error', 'Access denied. Administrator privileges required.');
}

switch ($action) {
    case 'add_wakalah_individual':
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $status = $input['status'] ?? 'Active';
        $ic_number = trim($input['ic_number'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($ic_number)) {
            json_resp('error', 'All individual wakalah fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_resp('error', 'Invalid email address format.');
        }

        try {
            // Check if email already exists in users/wakalah
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                json_resp('error', 'Email is already registered.');
            }

            $pdo->beginTransaction();

            // Insert into wakalah (address is NULL, state is '-')
            $stmt = $pdo->prepare("INSERT INTO wakalah (type, name, email, phone, state, status, ic_number, address) VALUES ('individual', ?, ?, ?, '-', ?, ?, NULL)");
            $stmt->execute([$name, $email, $phone, $status, $ic_number]);
            $wak_id = $pdo->lastInsertId();

            // Create user account (default password is staff123, state '-')
            $default_pass = password_hash('staff123', PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id) VALUES (?, ?, 'wakalah_individual', '-', ?)");
            $stmt_user->execute([$email, $default_pass, $wak_id]);

            $pdo->commit();
            json_resp('success', 'Wakalah Individual added successfully.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    case 'edit_wakalah_individual':
        $id = (int)($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $status = $input['status'] ?? 'Active';
        $ic_number = trim($input['ic_number'] ?? '');

        if (!$id || empty($name) || empty($email) || empty($phone) || empty($ic_number)) {
            json_resp('error', 'All fields are required.');
        }

        try {
            // Check if email already exists for a different user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND wakalah_id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetchColumn() > 0) {
                json_resp('error', 'Email is already registered by another account.');
            }

            $pdo->beginTransaction();

            // Update wakalah (address set to NULL, state is '-')
            $stmt = $pdo->prepare("UPDATE wakalah SET name = ?, email = ?, phone = ?, state = '-', status = ?, ic_number = ?, address = NULL WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $status, $ic_number, $id]);

            // Update users table state & email
            $stmt_user = $pdo->prepare("UPDATE users SET email = ?, state = '-' WHERE wakalah_id = ?");
            $stmt_user->execute([$email, $id]);

            $pdo->commit();
            json_resp('success', 'Wakalah Individual details updated successfully.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    case 'add_wakalah_corporate':
        $name = trim($input['company_name'] ?? '');
        $branch_name = trim($input['branch_name'] ?? '');
        $representative = trim($input['company_representative'] ?? '');
        $ssm_number = trim($input['ssm_number'] ?? '');
        $hq_address = trim($input['hq_address'] ?? '');
        $email = trim($input['company_email'] ?? '');
        $phone = trim($input['company_phone'] ?? '');
        $status = $input['status'] ?? 'Active';

        if (empty($name) || empty($branch_name) || empty($representative) || empty($ssm_number) || empty($hq_address) || empty($email) || empty($phone)) {
            json_resp('error', 'All corporate fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_resp('error', 'Invalid email address format.');
        }

        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                json_resp('error', 'Email is already registered.');
            }

            $pdo->beginTransaction();

            // Insert into wakalah (state is '-', address is NULL)
            $stmt = $pdo->prepare("INSERT INTO wakalah (type, name, branch_name, email, phone, state, status, company_representative, ssm_number, hq_address, address) VALUES ('corporate', ?, ?, ?, ?, '-', ?, ?, ?, ?, NULL)");
            $stmt->execute([$name, $branch_name, $email, $phone, $status, $representative, $ssm_number, $hq_address]);
            $wak_id = $pdo->lastInsertId();

            // Create user login (default password is staff123, state is '-')
            $default_pass = password_hash('staff123', PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id) VALUES (?, ?, 'wakalah_corporate', '-', ?)");
            $stmt_user->execute([$email, $default_pass, $wak_id]);

            $pdo->commit();
            json_resp('success', 'Wakalah Corporate added successfully.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    case 'edit_wakalah_corporate':
        $id = (int)($input['id'] ?? 0);
        $name = trim($input['company_name'] ?? '');
        $branch_name = trim($input['branch_name'] ?? '');
        $representative = trim($input['company_representative'] ?? '');
        $ssm_number = trim($input['ssm_number'] ?? '');
        $hq_address = trim($input['hq_address'] ?? '');
        $email = trim($input['company_email'] ?? '');
        $phone = trim($input['company_phone'] ?? '');
        $status = $input['status'] ?? 'Active';

        if (!$id || empty($name) || empty($branch_name) || empty($representative) || empty($ssm_number) || empty($hq_address) || empty($email) || empty($phone)) {
            json_resp('error', 'All corporate fields are required.');
        }

        try {
            // Check if email already exists for a different user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND wakalah_id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetchColumn() > 0) {
                json_resp('error', 'Email address is already registered by another account.');
            }

            $pdo->beginTransaction();

            // Update wakalah (state is '-', address is NULL)
            $stmt = $pdo->prepare("UPDATE wakalah SET name = ?, branch_name = ?, email = ?, phone = ?, state = '-', status = ?, company_representative = ?, ssm_number = ?, hq_address = ?, address = NULL WHERE id = ?");
            $stmt->execute([$name, $branch_name, $email, $phone, $status, $representative, $ssm_number, $hq_address, $id]);

            // Update user state & email ('-')
            $stmt_user = $pdo->prepare("UPDATE users SET email = ?, state = '-' WHERE wakalah_id = ?");
            $stmt_user->execute([$email, $id]);

            $pdo->commit();
            json_resp('success', 'Wakalah Corporate details updated successfully.');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    case 'toggle_wakalah_status':
        $id = (int)($input['id'] ?? 0);
        if (!$id) json_resp('error', 'Wakalah ID required.');

        try {
            $stmt = $pdo->prepare("SELECT status FROM wakalah WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetchColumn();
            if (!$current) json_resp('error', 'Wakalah account not found.');

            $new_status = ($current === 'Active') ? 'Inactive' : 'Active';
            $stmt = $pdo->prepare("UPDATE wakalah SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);

            json_resp('success', 'Wakalah status updated successfully.', ['status' => $new_status]);
        } catch (PDOException $e) {
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    case 'add_donation':
        $donor_name = trim($input['donor_name'] ?? 'Anonymous');
        $donor_phone = trim($input['donor_phone'] ?? '');
        $donor_email = trim($input['donor_email'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        $donation_date = trim($input['donation_date'] ?? date('Y-m-d'));
        $booth_id_val = $input['booth_id'] ?? '';

        if ($amount <= 0) {
            json_resp('error', 'Donation amount must be greater than zero.');
        }

        try {
            // Determine wakalah context
            // If logged in as wakalah, use their own wakalah_id automatically.
            // If logged in as admin, check if they selected a specific wakalah_id from the dropdown.
            $final_wakalah_id = null;
            if ($user_role !== 'admin') {
                $final_wakalah_id = $user_wakalah_id;
            } else {
                $final_wakalah_id = (int)($input['wakalah_id'] ?? 0) ?: null;
            }

            // Determine if collector is individual
            $is_individual_collector = false;
            if ($user_role === 'wakalah_individual') {
                $is_individual_collector = true;
            } elseif ($final_wakalah_id) {
                $stmt_w = $pdo->prepare("SELECT type FROM wakalah WHERE id = ?");
                $stmt_w->execute([$final_wakalah_id]);
                if ($stmt_w->fetchColumn() === 'individual') {
                    $is_individual_collector = true;
                }
            }

            $is_no_booth = ($booth_id_val === 'none' || $booth_id_val === '-' || !$booth_id_val);

            if ($is_individual_collector || $is_no_booth) {
                // Determine channel
                if ($final_wakalah_id) {
                    $stmt_w = $pdo->prepare("SELECT channel FROM wakalah WHERE id = ?");
                    $stmt_w->execute([$final_wakalah_id]);
                    $final_channel = $stmt_w->fetchColumn() ?: ($user_channel ?: 'BSN');
                } else {
                    $final_channel = $user_channel ?: 'BSN';
                }
                $final_state = '-';
                $final_location = '-';
                $final_booth_id = null;
            } else {
                $booth_id = (int)$booth_id_val;
                if (!$booth_id) {
                    json_resp('error', 'Collection booth must be selected.');
                }
                // Get booth details
                $stmt = $pdo->prepare("SELECT name, location, state, channel FROM booths WHERE id = ?");
                $stmt->execute([$booth_id]);
                $booth = $stmt->fetch();

                if (!$booth) {
                    json_resp('error', 'Selected booth location not found.');
                }
                $final_channel = $booth['channel'];
                $final_state = $booth['state'];
                $final_location = $booth['name'];
                $final_booth_id = $booth_id;
            }

            $donation_month = date('F', strtotime($donation_date));

            $stmt = $pdo->prepare("INSERT INTO donations (donor_name, donor_phone, donor_email, amount, donation_date, donation_month, channel, state, location, wakalah_id, booth_id) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $donor_name ?: 'Anonymous',
                $donor_phone,
                $donor_email,
                $amount,
                $donation_date,
                $donation_month,
                $final_channel,
                $final_state,
                $final_location,
                $final_wakalah_id,
                $final_booth_id
            ]);

            json_resp('success', 'Donation recorded successfully!', ['amount' => $amount]);
        } catch (PDOException $e) {
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    case 'change_password':
        $current_password = $input['current_password'] ?? '';
        $new_password = $input['new_password'] ?? '';
        $confirm_password = $input['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            json_resp('error', 'All password fields are required.');
        }

        if ($new_password !== $confirm_password) {
            json_resp('error', 'New passwords do not match.');
        }

        if (strlen($new_password) < 6) {
            json_resp('error', 'New password must be at least 6 characters long.');
        }

        try {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current_password, $hash)) {
                json_resp('error', 'Incorrect current password.');
            }

            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$new_hash, $_SESSION['user_id']]);

            json_resp('success', 'Password changed successfully.');
        } catch (PDOException $e) {
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    case 'add_booth':
        // Wakalah users (or admin) can add booths
        if ($user_role !== 'wakalah_individual' && $user_role !== 'wakalah_corporate' && $user_role !== 'admin') {
            json_resp('error', 'Access denied.');
        }

        $booth_name     = trim($input['name'] ?? '');
        $booth_location = trim($input['location'] ?? '');
        $booth_state    = trim($input['state'] ?? '');
        $booth_channel  = trim($input['channel'] ?? '');
        $booth_status   = trim($input['status'] ?? 'Active');

        $valid_channels = ['BSN', 'Bank Rakyat', 'Pos Malaysia', 'EBB'];
        $valid_statuses = ['Active', 'Inactive'];

        if (empty($booth_name) || empty($booth_location) || empty($booth_state) || empty($booth_channel)) {
            json_resp('error', 'All booth fields are required.');
        }

        if (!in_array($booth_channel, $valid_channels)) {
            json_resp('error', 'Invalid channel selected.');
        }

        if (!in_array($booth_status, $valid_statuses)) {
            $booth_status = 'Active';
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO booths (name, location, state, channel, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$booth_name, $booth_location, $booth_state, $booth_channel, $booth_status]);
            $new_booth_id = $pdo->lastInsertId();

            // Fetch the created booth row to return to the frontend
            $stmt2 = $pdo->prepare("SELECT * FROM booths WHERE id = ?");
            $stmt2->execute([$new_booth_id]);
            $new_booth = $stmt2->fetch();

            json_resp('success', 'Booth added successfully!', [
                'booth' => [
                    'id'                => $new_booth['id'],
                    'name'              => $new_booth['name'],
                    'location'          => $new_booth['location'],
                    'state'             => $new_booth['state'],
                    'channel'           => $new_booth['channel'],
                    'status'            => $new_booth['status'],
                    'created_at_formatted' => date('d M Y', strtotime($new_booth['created_at']))
                ]
            ]);
        } catch (PDOException $e) {
            json_resp('error', 'Database error: ' . $e->getMessage());
        }
        break;

    default:
        json_resp('error', 'Unknown action requested.');
        break;
}
