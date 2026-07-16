<?php
// modules/donations.php - Donation Monitoring Module (Wakalah Refactored)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'wakalah_individual';
$wakalah_id = $_SESSION['wakalah_id'] ?? null;
$user_state = $_SESSION['state'] ?? 'Selangor';
$user_channel = $_SESSION['channel'] ?? null;

// Get selected filters
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
    // Fetch active booths - filtered by channel for wakalah users
    if ($role === 'admin') {
        $booths_dropdown = $pdo->query("SELECT id, name, channel, state FROM booths WHERE status = 'Active' ORDER BY name ASC")->fetchAll();
    } else {
        $stmt_b = $pdo->prepare("SELECT id, name, channel, state FROM booths WHERE status = 'Active' AND channel = ? ORDER BY name ASC");
        $stmt_b->execute([$user_channel]);
        $booths_dropdown = $stmt_b->fetchAll();
    }

    if ($role === 'admin') {
        // Fetch all active wakalah partners for Admin logging form
        $wakalah_dropdown = $pdo->query("SELECT id, name, type FROM wakalah WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

        // Build donations query (Admins view all)
        $sql = "SELECT d.*, w.name as collector_name, w.branch_name, w.type as collector_type 
                FROM donations d
                LEFT JOIN wakalah w ON d.wakalah_id = w.id
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
        
        $sql .= " ORDER BY d.donation_date DESC, d.id DESC";
        
        $stmt_don = $pdo->prepare($sql);
        $stmt_don->execute($params);
        $donations = $stmt_don->fetchAll();
    } else {
        // Wakalah partner — filter by their own channel only
        $sql = "SELECT d.*, w.name as collector_name, w.branch_name, w.type as collector_type 
                FROM donations d
                LEFT JOIN wakalah w ON d.wakalah_id = w.id
                WHERE d.channel = :user_channel";
        
        $params = [':user_channel' => $user_channel];
        if (!empty($selected_month)) {
            $sql .= " AND d.donation_month = :selected_month";
            $params[':selected_month'] = $selected_month;
        }
        if (!empty($selected_state)) {
            $sql .= " AND d.state = :selected_state";
            $params[':selected_state'] = $selected_state;
        }
        
        $sql .= " ORDER BY d.donation_date DESC, d.id DESC";
        
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
                    <?php if ($role !== 'wakalah_individual'): ?>
                        <th>State Branch</th>
                    <?php endif; ?>
                    <?php if ($role !== 'wakalah_individual'): ?>
                        <th>Booth Location</th>
                    <?php endif; ?>
                    <?php if ($role === 'admin'): ?>
                        <th>Wakalah</th>
                    <?php endif; ?>
                    <th style="text-align: right;">Amount Collected</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($donations) === 0): ?>
                    <?php
                        $colcount = 4;
                        if ($role !== 'wakalah_individual') $colcount += 2;
                        if ($role === 'admin') $colcount += 1;
                    ?>
                    <tr>
                        <td colspan="<?php echo $colcount; ?>" style="text-align: center; color: var(--light-neutral); padding: 2rem;">
                            No donation records registered 
                            <?php 
                                $filters = [];
                                if (!empty($selected_month)) $filters[] = "for " . htmlspecialchars($selected_month);
                                if (!empty($selected_state)) $filters[] = "in " . htmlspecialchars($selected_state);
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
                            <?php if ($role !== 'wakalah_individual'): ?>
                                <td>
                                    <span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">
                                        <?php echo htmlspecialchars($don['state']); ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <?php if ($role !== 'wakalah_individual'): ?>
                                <td>
                                    <?php 
                                        $loc = htmlspecialchars($don['location']);
                                        if ((($don['collector_type'] ?? '') === 'corporate' || $role === 'wakalah_corporate') && $loc === '-') {
                                            $partner_name = $don['collector_name'] ?: ($_SESSION['name'] ?? 'Corporate Partner');
                                            echo htmlspecialchars($partner_name . ' ' . $don['state']);
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
        <form id="form-add-donation">
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
                    <input type="text" name="donor_name" id="don_donor_name" class="form-input" placeholder="e.g. Hajah Aminah (Leave blank for Anonymous)">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="don_donor_phone">Donor Phone</label>
                        <input type="text" name="donor_phone" id="don_donor_phone" class="form-input" placeholder="e.g. 013-1234567">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="don_donor_email">Donor Email</label>
                        <input type="email" name="donor_email" id="don_donor_email" class="form-input" placeholder="e.g. aminah@email.com">
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
                <button type="submit" class="btn btn-success">Record Donation</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Hook submit action
    handleFormSubmit('form-add-donation', 'Donation recorded successfully!');

    // State filtering logic for booths dropdown
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

            // If dash is selected, auto-set booth to dash too
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

            // Add placeholder option
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.text = '-- Select Collection Booth --';
            boothSelect.appendChild(placeholder);

            // Add "Not Applicable" option
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