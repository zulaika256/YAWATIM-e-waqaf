<?php
// modules/wakalah_individual.php - Admin Wakalah Individual Management View
if (!isset($_SESSION)) {
    session_start();
}
require_once __DIR__ . '/../includes/notification.php';

// Enforce admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php?page=dashboard");
    exit;
}

// ===== POST HANDLERS =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = '';
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    }

    // --- ADD INDIVIDUAL WAKALAH ---
    if ($action == 'add_wakalah_individual') {
        $name = '';
        if (isset($_POST['name'])) { $name = trim($_POST['name']); }
        $email = '';
        if (isset($_POST['email'])) { $email = trim($_POST['email']); }
        $phone = '';
        if (isset($_POST['phone'])) { $phone = trim($_POST['phone']); }
        
        $status = 'Active';
        if (isset($_POST['status'])) { $status = $_POST['status']; }
        
        $ic_number = '';
        if (isset($_POST['ic_number'])) { $ic_number = trim($_POST['ic_number']); }

        if ($name == '' || $email == '' || $phone == '' || $ic_number == '') {
            set_flash('error', 'All individual wakalah fields are required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Invalid email address format.');
        } else {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute(array($email));
                $count = $stmt->fetchColumn();
                if ($count > 0) {
                    set_flash('error', 'Email is already registered.');
                } else {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO wakalah (type, name, email, phone, state, status, ic_number, address) VALUES ('individual', ?, ?, ?, '-', ?, ?, NULL)");
                    $stmt->execute(array($name, $email, $phone, $status, $ic_number));
                    $wak_id = $pdo->lastInsertId();

                    $default_pass = password_hash('staff123', PASSWORD_DEFAULT);
                    $stmt_user = $pdo->prepare("INSERT INTO users (email, password_hash, role, state, wakalah_id) VALUES (?, ?, 'wakalah_individual', '-', ?)");
                    $stmt_user->execute(array($email, $default_pass, $wak_id));

                    $pdo->commit();
                    set_flash('success', 'Wakalah Individual added successfully.');
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { 
                    $pdo->rollBack(); 
                }
                set_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
        header("Location: index.php?page=wakalah_individual");
        exit;
    }

    // --- EDIT INDIVIDUAL WAKALAH ---
    if ($action == 'edit_wakalah_individual') {
        $id = 0;
        if (isset($_POST['id'])) { $id = (int)$_POST['id']; }
        
        $name = '';
        if (isset($_POST['name'])) { $name = trim($_POST['name']); }
        
        $email = '';
        if (isset($_POST['email'])) { $email = trim($_POST['email']); }
        
        $phone = '';
        if (isset($_POST['phone'])) { $phone = trim($_POST['phone']); }
        
        $status = 'Active';
        if (isset($_POST['status'])) { $status = $_POST['status']; }
        
        $ic_number = '';
        if (isset($_POST['ic_number'])) { $ic_number = trim($_POST['ic_number']); }

        if ($id == 0 || $name == '' || $email == '' || $phone == '' || $ic_number == '') {
            set_flash('error', 'All fields are required.');
        } else {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND wakalah_id != ?");
                $stmt->execute(array($email, $id));
                $count = $stmt->fetchColumn();
                if ($count > 0) {
                    set_flash('error', 'Email is already registered by another account.');
                } else {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("UPDATE wakalah SET name = ?, email = ?, phone = ?, state = '-', status = ?, ic_number = ?, address = NULL WHERE id = ?");
                    $stmt->execute(array($name, $email, $phone, $status, $ic_number, $id));

                    $stmt_user = $pdo->prepare("UPDATE users SET email = ?, state = '-' WHERE wakalah_id = ?");
                    $stmt_user->execute(array($email, $id));

                    $pdo->commit();
                    set_flash('success', 'Wakalah Individual details updated successfully.');
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { 
                    $pdo->rollBack(); 
                }
                set_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
        header("Location: index.php?page=wakalah_individual");
        exit;
    }

    // --- TOGGLE STATUS ---
    if ($action == 'toggle_wakalah_status') {
        $id = 0;
        if (isset($_POST['id'])) { $id = (int)$_POST['id']; }
        if ($id == 0) {
            set_flash('error', 'Wakalah ID required.');
        } else {
            try {
                $stmt = $pdo->prepare("SELECT status FROM wakalah WHERE id = ?");
                $stmt->execute(array($id));
                $current = $stmt->fetchColumn();
                if (!$current) {
                    set_flash('error', 'Wakalah account not found.');
                } else {
                    $new_status = 'Active';
                    if ($current == 'Active') {
                        $new_status = 'Inactive';
                    }
                    $stmt = $pdo->prepare("UPDATE wakalah SET status = ? WHERE id = ?");
                    $stmt->execute(array($new_status, $id));
                    set_flash('success', 'Wakalah status updated to ' . $new_status . '.');
                }
            } catch (PDOException $e) {
                set_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
        header("Location: index.php?page=wakalah_individual");
        exit;
    }
}

// ===== DATA FETCH =====
$wakalah_list = array();
try {
    $stmt = $pdo->query("SELECT * FROM wakalah WHERE type = 'individual' ORDER BY name ASC");
    $wakalah_list = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div style='color: var(--alert-red); font-weight: bold;'>Query Error: " . $e->getMessage() . "</div>";
}

$malaysian_states = array(
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 
    'Penang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'WP Kuala Lumpur', 'WP Putrajaya', 'WP Labuan'
);
?>

<!-- Table Controls Header -->
<div class="table-controls-container" id="wakalah-individual-controls">
    <div class="search-filter-group">
        <input type="text" class="input-search" placeholder="Search name, IC, email..." data-search-target="true" id="wak-ind-search-input">
        
        <select class="select-filter" data-filter-column="4" id="wak-ind-filter-status">
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>
    
    <button class="btn btn-primary" data-modal-open="modal-add-wakalah-individual" id="btn-add-wakalah-individual-modal">
        <i class="fa-solid fa-user-plus"></i> Register Individual Partner
    </button>
</div>

<!-- Table Card -->
<div class="table-card" id="wakalah-individual-table-card">
    <div class="table-wrapper">
        <table class="table-yawatim" id="wakalah-individual-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>IC Number</th>
                    <th>Email Address</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Registered Date</th>
                    <th class="actions-td">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($wakalah_list) == 0) { ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--light-neutral); padding: 2rem;">No individual partner records found.</td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($wakalah_list as $wak) { ?>
                        <tr id="wak-ind-row-<?php echo $wak['id']; ?>">
                            <td style="font-weight: 700; color: var(--primary-blue);" class="wak-name-cell"><?php echo htmlspecialchars($wak['name']); ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($wak['ic_number']); ?></td>
                            <td><?php echo htmlspecialchars($wak['email']); ?></td>
                            <td><?php echo htmlspecialchars($wak['phone']); ?></td>
                            <td class="wak-status-cell">
                                <span class="badge <?php echo strtolower($wak['status']); ?>"><?php echo $wak['status']; ?></span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--medium-neutral);">
                                <?php echo date('d M Y', strtotime($wak['created_at'])); ?>
                            </td>
                            <td class="actions-td">
                                <div class="action-group">
                                    <button class="action-btn edit btn-edit-wak-ind" 
                                            data-id="<?php echo $wak['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($wak['name']); ?>"
                                            data-email="<?php echo htmlspecialchars($wak['email']); ?>"
                                            data-phone="<?php echo htmlspecialchars($wak['phone']); ?>"
                                            data-status="<?php echo htmlspecialchars($wak['status']); ?>"
                                            data-ic="<?php echo htmlspecialchars($wak['ic_number']); ?>"
                                            <?php if(isset($wak['address'])) { ?>
                                                data-address="<?php echo htmlspecialchars($wak['address']); ?>"
                                            <?php } else { ?>
                                                data-address=""
                                            <?php } ?>
                                            title="Edit Individual Details">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <form method="POST" action="index.php?page=wakalah_individual" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_wakalah_status">
                                        <input type="hidden" name="id" value="<?php echo $wak['id']; ?>">
                                        <button type="submit" class="action-btn" 
                                                title="Toggle Status (Active/Inactive)"
                                                onclick="return confirm('Are you sure you want to toggle this Wakalah account status?')">
                                            <i class="fa-solid fa-power-off"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD INDIVIDUAL WAKALAH -->
<div class="modal-overlay" id="modal-add-wakalah-individual">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Register Individual Partner</h3>
            <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="index.php?page=wakalah_individual" id="form-add-wak-ind">
            <input type="hidden" name="action" value="add_wakalah_individual">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="add_name">Full Name</label>
                    <input type="text" name="name" id="add_name" class="form-input" placeholder="e.g. Ahmad bin Ali" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_ic">IC Number</label>
                    <input type="text" name="ic_number" id="add_ic" class="form-input" placeholder="e.g. 890510-10-5555" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="add_email">Email Address</label>
                        <input type="email" name="email" id="add_email" class="form-input" placeholder="e.g. ahmad@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="add_phone">Phone Number</label>
                        <input type="text" name="phone" id="add_phone" class="form-input" placeholder="e.g. 012-3456789" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_status">Account Status</label>
                    <select name="status" id="add_status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_ind_address">Home Address <span style="font-size: 0.75rem; color: var(--light-neutral);">(Optional)</span></label>
                    <textarea name="address" id="add_ind_address" class="form-input" rows="2" placeholder="e.g. No. 10, Jalan Contoh, Taman Maju, 43000 Kajang, Selangor" style="resize: vertical;"></textarea>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close="modal-add-wakalah-individual">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Partner</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT INDIVIDUAL WAKALAH -->
<div class="modal-overlay" id="modal-edit-wakalah-individual">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Edit Individual Details</h3>
            <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="index.php?page=wakalah_individual" id="form-edit-wak-ind">
            <input type="hidden" name="action" value="edit_wakalah_individual">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="edit_name">Full Name</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_ic">IC Number</label>
                    <input type="text" name="ic_number" id="edit_ic" class="form-input" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_email">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_phone">Phone Number</label>
                        <input type="text" name="phone" id="edit_phone" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_status">Account Status</label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_ind_address">Home Address <span style="font-size: 0.75rem; color: var(--light-neutral);">(Optional)</span></label>
                    <textarea name="address" id="edit_ind_address" class="form-input" rows="2" style="resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close="modal-edit-wakalah-individual">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Details</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Open edit modal & prepopulate values
    var editBtns = document.querySelectorAll('.btn-edit-wak-ind');
    for (var i = 0; i < editBtns.length; i++) {
        var btn = editBtns[i];
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
            document.getElementById('edit_phone').value = this.getAttribute('data-phone');
            document.getElementById('edit_status').value = this.getAttribute('data-status');
            document.getElementById('edit_ic').value = this.getAttribute('data-ic');
            var address = this.getAttribute('data-address');
            if (address) {
                document.getElementById('edit_ind_address').value = address;
            } else {
                document.getElementById('edit_ind_address').value = '';
            }
            
            openModal('modal-edit-wakalah-individual');
        });
    }
});
</script>
