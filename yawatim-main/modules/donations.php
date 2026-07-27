<?php
// modules/donations.php - Donation Monitoring Module (Wakalah Refactored)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/notification.php';

$role = $_SESSION['role'] ?? 'wakalah_individual';
$wakalah_id = $_SESSION['wakalah_id'] ?? null;
$user_state = $_SESSION['state'] ?? 'Selangor';
$user_channel = $_SESSION['channel'] ?? null;

// ===== POST HANDLER: ADD DONATION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_donation') {
    $donor_name = trim($_POST['donor_name'] ?? 'Anonymous');
    $donor_phone = trim($_POST['donor_phone'] ?? '');
    $donor_email = trim($_POST['donor_email'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $donation_date = trim($_POST['donation_date'] ?? date('Y-m-d'));
    $booth_id_val = $_POST['booth_id'] ?? '';

    if ($amount <= 0) {
        set_flash('error', 'Donation amount must be greater than zero.');
        header("Location: index.php?page=donations");
        exit;
    }

    // Handle receipt image upload
    $attachment_image_path = null;
    if (!empty($_FILES['attachment_image']) && $_FILES['attachment_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_tmp  = $_FILES['attachment_image']['tmp_name'];
        $file_name = $_FILES['attachment_image']['name'];
        $file_size = $_FILES['attachment_image']['size'];
        $file_type = mime_content_type($file_tmp);

        if (!in_array($file_type, $allowed_types)) {
            set_flash('error', 'Invalid receipt image format. Only JPG, PNG, GIF, or WEBP are allowed.');
            header("Location: index.php?page=donations");
            exit;
        }
        if ($file_size > 5 * 1024 * 1024) {
            set_flash('error', 'Receipt image is too large. Maximum size is 5MB.');
            header("Location: index.php?page=donations");
            exit;
        }

        $upload_dir = __DIR__ . '/../assets/receipts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_filename = 'receipt_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
        $dest = $upload_dir . $new_filename;

        if (!move_uploaded_file($file_tmp, $dest)) {
            set_flash('error', 'Failed to save receipt image. Please try again.');
            header("Location: index.php?page=donations");
            exit;
        }
        $attachment_image_path = 'assets/receipts/' . $new_filename;
    }

    try {
        $final_wakalah_id = null;
        if ($role !== 'admin') {
            $final_wakalah_id = $wakalah_id;
        } else {
            $final_wakalah_id = (int)($_POST['wakalah_id'] ?? 0) ?: null;
        }

        $is_individual_collector = false;
        if ($role === 'wakalah_individual') {
            $is_individual_collector = true;
        } elseif ($final_wakalah_id) {
            $stmt_w = $pdo->prepare("SELECT type FROM wakalah WHERE wakalah_id = ?");
            $stmt_w->execute([$final_wakalah_id]);
            if ($stmt_w->fetchColumn() === 'individual') {
                $is_individual_collector = true;
            }
        }

        $is_no_booth = ($booth_id_val === 'none' || $booth_id_val === '-' || !$booth_id_val);

        if ($is_individual_collector || $is_no_booth) {
            if ($final_wakalah_id) {
                $stmt_w = $pdo->prepare("SELECT channel FROM wakalah WHERE wakalah_id = ?");
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
                set_flash('error', 'Collection booth must be selected.');
                header("Location: index.php?page=donations");
                exit;
            }
            $stmt = $pdo->prepare("SELECT name, location, state, channel FROM booths WHERE booth_id = ?");
            $stmt->execute([$booth_id]);
            $booth = $stmt->fetch();

            if (!$booth) {
                set_flash('error', 'Selected booth location not found.');
                header("Location: index.php?page=donations");
                exit;
            }
            $final_channel = $booth['channel'];
            $final_state = $booth['state'];
            $final_location = $booth['name'];
            $final_booth_id = $booth_id;
        }

        $donation_month = date('F', strtotime($donation_date));

        $stmt = $pdo->prepare("INSERT INTO donations (donor_name, donor_phone, donor_email, amount, donation_date, donation_month, channel, state, location, wakalah_id, booth_id, attachment_image) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
            $final_booth_id,
            $attachment_image_path
        ]);

        set_flash('success', 'Donation recorded successfully!');
    } catch (PDOException $e) {
        set_flash('error', 'Database error: ' . $e->getMessage());
    }

    header("Location: index.php?page=donations");
    exit;
}

// ===== POST HANDLER: DELETE DONATION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_donation') {
    $donation_id = (int)($_POST['donation_id'] ?? 0);
    
    if ($donation_id) {
        try {
            if ($role === 'admin') {
                $stmt = $pdo->prepare("DELETE FROM donations WHERE donation_id = ?");
                $stmt->execute([$donation_id]);
                set_flash('success', 'Donation record deleted successfully.');
            } else {
                $stmt = $pdo->prepare("DELETE FROM donations WHERE donation_id = ? AND wakalah_id = ?");
                $stmt->execute([$donation_id, $wakalah_id]);
                if ($stmt->rowCount() > 0) {
                    set_flash('success', 'Donation record deleted successfully.');
                } else {
                    set_flash('error', 'Unauthorized to delete this record or record not found.');
                }
            }
        } catch (PDOException $e) {
            set_flash('error', 'Database error: ' . $e->getMessage());
        }
    }
    
    header("Location: index.php?page=donations");
    exit;
}

// ===== DATA FETCH =====
$selected_month = $_GET['month_filter'] ?? '';
$selected_state = $_GET['state_filter'] ?? '';

$donations = [];
$booths_dropdown = [];
$wakalah_dropdown = [];

$months = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
];

$malaysian_states = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 
    'Penang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'WP Kuala Lumpur', 'WP Putrajaya', 'WP Labuan'
];
$channels = ['BSN', 'Bank Rakyat', 'Pos Malaysia', 'EBB'];

try {
    if ($role === 'admin') {
        $booths_dropdown = $pdo->query("SELECT booth_id as id, name, channel, state FROM booths WHERE status = 'Active' ORDER BY name ASC")->fetchAll();
    } else {
        $stmt_b = $pdo->prepare("SELECT booth_id as id, name, channel, state FROM booths WHERE status = 'Active' AND channel = ? ORDER BY name ASC");
        $stmt_b->execute([$user_channel]);
        $booths_dropdown = $stmt_b->fetchAll();
    }

    if ($role === 'admin') {
        $wakalah_dropdown = $pdo->query("SELECT wakalah_id as id, name, type FROM wakalah WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

        $sql = "SELECT d.*, w.name as collector_name, w.branch_name, w.type as collector_type 
                FROM donations d
                LEFT JOIN wakalah w ON d.wakalah_id = w.wakalah_id
                WHERE 1=1";
        
        $params = [];
        if (!empty($selected_month)) {
            $sql .= " AND d.donation_month = :selected_month";
            $params[':selected_month'] = $selected_month;
        }
        if (!empty($selected_state)) {
            $sql .= " AND d.state = :selected_state";
            $params[':selected_state'] = $selected_state;
        }
        
        $sql .= " ORDER BY d.donation_date DESC, d.donation_id DESC";
        
        $stmt_don = $pdo->prepare($sql);
        $stmt_don->execute($params);
        $donations = $stmt_don->fetchAll();
    } else {
        $sql = "SELECT d.*, w.name as collector_name, w.branch_name, w.type as collector_type 
                FROM donations d
                LEFT JOIN wakalah w ON d.wakalah_id = w.wakalah_id
                WHERE d.wakalah_id = :wakalah_id";
        
        $params = [':wakalah_id' => $wakalah_id];
        if (!empty($selected_month)) {
            $sql .= " AND d.donation_month = :selected_month";
            $params[':selected_month'] = $selected_month;
        }
        if (!empty($selected_state)) {
            $sql .= " AND d.state = :selected_state";
            $params[':selected_state'] = $selected_state;
        }
        
        $sql .= " ORDER BY d.donation_date DESC, d.donation_id DESC";
        
        $stmt_don = $pdo->prepare($sql);
        $stmt_don->execute($params);
        $donations = $stmt_don->fetchAll();
    }
} catch (PDOException $e) {
    echo "<div style='color: var(--alert-red); font-weight: bold;'>Query Error: " . $e->getMessage() . "</div>";
}
?>

<!-- Controls Header -->
<div class="table-controls-container" id="donation-controls">
    <form method="GET" action="index.php" class="search-filter-group" id="form-filter-donations" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <input type="hidden" name="page" value="donations">
        
        <!-- Search bar -->
        <input type="text" class="input-search" placeholder="Search donor name..." data-search-target="true" id="donation-search-input">
        
        <!-- Filter by Month -->
        <select class="select-filter" name="month_filter" id="donation-filter-month" onchange="this.form.submit()">
            <option value="">All Months</option>
            <?php foreach ($months as $month): ?>
                <option value="<?php echo $month; ?>" <?php echo $selected_month === $month ? 'selected' : ''; ?>>
                    <?php echo $month; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Filter by State -->
        <select class="select-filter" name="state_filter" id="donation-filter-state" onchange="this.form.submit()">
            <option value="">All States</option>
            <?php foreach ($malaysian_states as $state): ?>
                <option value="<?php echo htmlspecialchars($state); ?>" <?php echo $selected_state === $state ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($state); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Clear Filters Button -->
        <?php if (!empty($selected_month) || !empty($selected_state)): ?>
            <a href="index.php?page=donations" class="btn btn-outline" style="padding: 0.45rem 1rem; font-size: 0.8rem;">
                <i class="fa-solid fa-times"></i> Clear Filters
            </a>
        <?php endif; ?>
    </form>
    
    <!-- Trigger Modal -->
    <button class="btn btn-success" data-modal-open="modal-add-donation" id="btn-log-donation">
        <i class="fa-solid fa-plus-circle"></i> Log Donation Collected
    </button>
</div>

<!-- Table Card -->
<div class="table-card" id="donations-table-card">
    <div class="table-wrapper">
        <table class="table-yawatim" id="donations-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Month</th>
                    <th>Donor Details</th>
                    <th>Attachment</th>
                    <?php if ($role !== 'wakalah_individual'): ?>
                        <th>State Branch</th>
                        <th>Booth Location</th>
                    <?php endif; ?>
                    <?php if ($role === 'admin'): ?>
                        <th>Wakalah</th>
                    <?php endif; ?>
                    <th style="text-align: right;">Amount Collected</th>
                    <th style="text-align: center; width: 70px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($donations) === 0): ?>
                    <?php
                        $colcount = 6;
                        if ($role !== 'wakalah_individual') { $colcount += 2; }
                        if ($role === 'admin') { $colcount += 1; }
                    ?>
                    <tr>
                        <td colspan="<?php echo $colcount; ?>" style="text-align: center; color: var(--light-neutral); padding: 2rem;">
                            No donation records registered 
                            <?php 
                                $filters = [];
                                if (!empty($selected_month)) { $filters[] = "for " . htmlspecialchars($selected_month); }
                                if (!empty($selected_state)) { $filters[] = "in " . htmlspecialchars($selected_state); }
                                echo !empty($filters) ? "(" . implode(", ", $filters) . ")" : "";
                            ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($donations as $don): ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo date('d/m/Y', strtotime($don['donation_date'])); ?></td>
                            <td style="font-weight: 600; color: var(--medium-neutral);"><?php echo htmlspecialchars($don['donation_month']); ?></td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600;"><?php echo htmlspecialchars($don['donor_name'] ?: 'Anonymous'); ?></span>
                                    <span style="font-size: 0.75rem; color: var(--medium-neutral);">
                                        <?php echo htmlspecialchars($don['donor_phone'] ?: '-'); ?> 
                                        <?php echo $don['donor_email'] ? ' &bull; ' . htmlspecialchars($don['donor_email']) : ''; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($don['attachment_image'])): ?>
                                    <a href="<?php echo htmlspecialchars($don['attachment_image']); ?>" target="_blank" title="View Receipt">
                                        <img src="<?php echo htmlspecialchars($don['attachment_image']); ?>" alt="Receipt"
                                             style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--border-light);cursor:pointer;transition:transform 0.2s;" 
                                             onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--light-neutral); font-size: 0.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($role !== 'wakalah_individual'): ?>
                                <td>
                                    <span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">
                                        <?php echo htmlspecialchars($don['state']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $loc = htmlspecialchars($don['location']);
                                        if ((($don['collector_type'] ?? '') === 'corporate' || $role === 'wakalah_corporate') && $loc === '-') {
                                            $partner_name_display = $don['collector_name'] ?: ($_SESSION['name'] ?? 'Corporate Partner');
                                            echo htmlspecialchars($partner_name_display . ' ' . $don['state']);
                                        } else {
                                            echo (($don['collector_type'] ?? '') === 'individual') ? '-' : $loc; 
                                        }
                                    ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($role === 'admin'): ?>
                                <td>
                                     <div style="display: flex; flex-direction: column;">
                                         <span style="font-size: 0.95rem; font-weight: 600; color: var(--dark-neutral);">
                                             <?php 
                                                 if (($don['collector_type'] ?? '') === 'corporate' || ($don['collector_type'] ?? '') === 'individual') {
                                                     echo htmlspecialchars($don['collector_name']);
                                                 } else {
                                                     echo 'Direct YAWATIM';
                                                 }
                                             ?>
                                         </span>
                                         <span style="font-size: 0.85rem; font-weight: 600; white-space: nowrap; color: <?php echo ($don['collector_type'] ?? '') === 'corporate' ? 'var(--primary-blue)' : (($don['collector_type'] ?? '') === 'individual' ? 'var(--success-green)' : 'var(--medium-neutral)'); ?>;">
                                             <?php echo ($don['collector_type'] ?? '') === 'corporate' ? 'Wakalah Corporate' : (($don['collector_type'] ?? '') === 'individual' ? 'Wakalah Individual' : 'Direct'); ?>
                                         </span>
                                     </div>
                                </td>
                            <?php endif; ?>
                            <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                RM <?php echo number_format($don['amount'], 2); ?>
                            </td>
                            <td style="text-align: center;">
                                <form method="POST" action="index.php?page=donations" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this donation record? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_donation">
                                    <input type="hidden" name="donation_id" value="<?php echo $don['donation_id']; ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 0.35rem 0.6rem; color: var(--alert-red); border: none; background: transparent;" title="Delete Donation">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD DONATION -->
<div class="modal-overlay" id="modal-add-donation">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Record Donation Collection</h3>
            <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="index.php?page=donations" id="form-add-donation" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_donation">
            <div class="modal-body">
                
                <!-- Admin Only: Select Wakalah Partner -->
                <?php if ($role === 'admin'): ?>
                    <div class="form-group">
                        <label class="form-label" for="don_wakalah_id">Wakalah Partner Collector</label>
                        <select name="wakalah_id" id="don_wakalah_id" class="form-select">
                            <option value="">-- Direct YAWATIM Admin Log --</option>
                            <?php foreach ($wakalah_dropdown as $w): ?>
                                <option value="<?php echo $w['id']; ?>">
                                    <?php echo htmlspecialchars($w['name'] . ' (' . ucfirst($w['type']) . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Donor Info -->
                <div class="form-group">
                    <label class="form-label" for="don_donor_name">Donor Name</label>
                    <input type="text" name="donor_name" id="don_donor_name" class="form-input" placeholder='e.g. Hajah Aminah — enter "-" for anonymous'>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="don_donor_phone">Donor Phone</label>
                        <input type="text" name="donor_phone" id="don_donor_phone" class="form-input" placeholder='e.g. 013-1234567 or "-"'>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="don_donor_email">Donor Email</label>
                        <input type="text" name="donor_email" id="don_donor_email" class="form-input" placeholder='e.g. aminah@email.com or "-"'>
                    </div>
                </div>

                <?php if ($role !== 'wakalah_individual'): ?>
                <!-- Collection State Branch -->
                <div class="form-group" id="don-state-group">
                    <label class="form-label" for="don_state">Collection State Branch</label>
                    <select name="state" id="don_state" class="form-select" required>
                        <option value="">-- Select State Branch --</option>
                        <?php if ($role === 'admin'): ?>
                            <option value="-">- (Not Applicable)</option>
                        <?php endif; ?>
                        <?php foreach ($malaysian_states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Collection Booth Location -->
                <div class="form-group" id="don-booth-group">
                    <label class="form-label" for="don_booth_id">Collection Booth Location</label>
                    <select name="booth_id" id="don_booth_id" class="form-select" required disabled>
                        <option value="" data-state="">-- Select State First --</option>
                        <?php if ($role === 'admin'): ?>
                            <option value="-" data-state="-">- (Not Applicable)</option>
                        <?php endif; ?>
                        <?php foreach ($booths_dropdown as $b): ?>
                            <option value="<?php echo $b['id']; ?>" data-state="<?php echo htmlspecialchars($b['state']); ?>">
                                <?php echo htmlspecialchars($b['name']); ?> <?php echo $role === 'admin' ? '(' . htmlspecialchars($b['channel']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Attachment Upload -->
                <div class="form-group" id="don-receipt-group">
                    <label class="form-label" for="don_attachment_image">
                        <i class="fa-solid fa-paperclip" style="margin-right: 0.35rem; color: var(--primary-blue);"></i>
                        Attachment <span style="font-size: 0.75rem; color: var(--light-neutral);">(Optional — JPG, PNG, WEBP, max 5MB)</span>
                    </label>
                    <div style="position: relative;">
                        <input type="file" name="attachment_image" id="don_attachment_image" accept="image/jpeg,image/png,image/gif,image/webp"
                               style="display:none;" onchange="previewReceipt(this)">
                        <div id="receipt-upload-area"
                             style="border: 2px dashed var(--border-light); border-radius: 10px; padding: 1.1rem 1rem; display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: border-color 0.2s; background: var(--off-white);"
                             onclick="document.getElementById('don_attachment_image').click()"
                             onmouseover="this.style.borderColor='var(--primary-blue)'" onmouseout="this.style.borderColor='var(--border-light)'">
                            <div id="receipt-preview-wrap" style="display:none; flex-shrink:0;">
                                <img id="receipt-preview-img" src="" alt="Receipt Preview"
                                     style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid var(--border-light);">
                            </div>
                            <div id="receipt-placeholder" style="display:flex;align-items:center;gap:0.6rem;">
                                <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.4rem;color:var(--primary-blue);"></i>
                                <span style="font-size:0.85rem;color:var(--medium-neutral);">Click to attach file</span>
                            </div>
                            <div id="receipt-filename" style="display:none;font-size:0.82rem;color:var(--dark-neutral);font-weight:600;"></div>
                            <button type="button" id="receipt-clear-btn"
                                    style="display:none;margin-left:auto;background:none;border:none;color:var(--alert-red);cursor:pointer;font-size:0.85rem;padding:0.2rem 0.4rem;"
                                    onclick="event.stopPropagation();clearReceipt()"
                                    title="Remove receipt">
                                <i class="fa-solid fa-xmark"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Amount and Date -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="don_amount">Donation Amount (RM)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 9px; font-weight: 700; color: var(--medium-neutral); font-size: 0.9rem;">RM</span>
                            <input type="number" name="amount" id="don_amount" class="form-input" step="0.01" min="1.00" placeholder="0.00" style="padding-left: 2.2rem;" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="don_donation_date">Collection Date</label>
                        <input type="date" name="donation_date" id="don_donation_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close="modal-add-donation">Cancel</button>
                <button type="submit" class="btn btn-success" id="btn-record-donation">Record Donation</button>
            </div>
        </form>
    </div>
</div>

<script>
// ----- Receipt preview helpers -----
function previewReceipt(input) {
    const file = input.files[0];
    const previewWrap = document.getElementById('receipt-preview-wrap');
    const previewImg  = document.getElementById('receipt-preview-img');
    const placeholder = document.getElementById('receipt-placeholder');
    const fileLabel   = document.getElementById('receipt-filename');
    const clearBtn    = document.getElementById('receipt-clear-btn');

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            previewWrap.style.display = 'block';
            placeholder.style.display = 'none';
            fileLabel.textContent = file.name;
            fileLabel.style.display = 'block';
            clearBtn.style.display = 'inline-flex';
        };
        reader.readAsDataURL(file);
    }
}

function clearReceipt() {
    const input = document.getElementById('don_attachment_image');
    input.value = '';
    document.getElementById('receipt-preview-wrap').style.display = 'none';
    document.getElementById('receipt-preview-img').src = '';
    document.getElementById('receipt-placeholder').style.display = 'flex';
    document.getElementById('receipt-filename').style.display = 'none';
    document.getElementById('receipt-filename').textContent = '';
    document.getElementById('receipt-clear-btn').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    // ----- State filtering logic for booths dropdown -----
    const stateSelect = document.getElementById('don_state');
    const boothSelect = document.getElementById('don_booth_id');

    if (stateSelect && boothSelect) {
        const originalOptions = Array.from(boothSelect.options);

        stateSelect.addEventListener('change', () => {
            const selectedState = stateSelect.value;
            boothSelect.innerHTML = '';

            if (!selectedState) {
                boothSelect.disabled = true;
                const opt = document.createElement('option');
                opt.value = '';
                opt.text = '-- Select State First --';
                boothSelect.appendChild(opt);
                return;
            }

            if (selectedState === '-') {
                boothSelect.disabled = false;
                const dashOpt = document.createElement('option');
                dashOpt.value = '-';
                dashOpt.text = '- (Not Applicable)';
                boothSelect.appendChild(dashOpt);
                boothSelect.value = '-';
                return;
            }

            boothSelect.disabled = false;

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.text = '-- Select Collection Booth --';
            boothSelect.appendChild(placeholder);

            const naOpt = document.createElement('option');
            naOpt.value = '-';
            naOpt.text = '- (Not Applicable)';
            boothSelect.appendChild(naOpt);

            originalOptions.forEach(opt => {
                const optState = opt.getAttribute('data-state');
                if (optState === selectedState) {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.text = opt.text;
                    newOpt.setAttribute('data-state', optState);
                    boothSelect.appendChild(newOpt);
                }
            });
        });
    }
});
</script>
