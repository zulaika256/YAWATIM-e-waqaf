<?php
// modules/reports.php - Analytical Reports (Wakalah Refactored)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=dashboard");
    exit;
}

$report_type = $_GET['report_type'] ?? 'donations';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$selected_month = $_GET['month_filter'] ?? '';
$partner_name = trim($_GET['partner_name'] ?? '');
$state_filter = $_GET['state_filter'] ?? [];
if (!is_array($state_filter)) $state_filter = [];

// Query distinct states and partner names for filter UI
$all_states = [];
$all_partners = [];
try {
    $all_states = $pdo->query("SELECT DISTINCT state FROM donations WHERE state IS NOT NULL AND state != '' AND state != '-' ORDER BY state ASC")->fetchAll(PDO::FETCH_COLUMN);
    $all_partners = $pdo->query("SELECT DISTINCT name FROM wakalah ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { /* ignore */ }

// Build Date/Month Filter parameters
$params = [];
$date_filter_don = "";
$month_filter_don = "";

if (!empty($start_date)) {
    $date_filter_don .= " AND donation_date >= :start_date";
    $params[':start_date'] = $start_date;
}
if (!empty($end_date)) {
    $date_filter_don .= " AND donation_date <= :end_date";
    $params[':end_date'] = $end_date;
}
if (!empty($selected_month)) {
    $month_filter_don .= " AND donation_month = :month_filter";
    $params[':month_filter'] = $selected_month;
}

// Build Partner Name filter clause
$partner_filter_don = ""; // for donations table (d.wakalah_id based)
$partner_filter_wak = ""; // for wakalah table (w.name based)
$partner_params = [];
if (!empty($partner_name)) {
    $partner_filter_don = " AND d.wakalah_id IN (SELECT wakalah_id FROM wakalah WHERE name LIKE :partner_name)";
    $partner_filter_wak = " AND w.name LIKE :partner_name";
    $partner_params[':partner_name'] = '%' . $partner_name . '%';
}

// Build State filter clause
$state_filter_don = ""; // for donations table
$state_filter_wak = ""; // for wakalah table
$state_params = [];
if (!empty($state_filter)) {
    $state_placeholders = [];
    foreach ($state_filter as $idx => $st) {
        $key = ':state_' . $idx;
        $state_placeholders[] = $key;
        $state_params[$key] = $st;
    }
    $in_clause = implode(', ', $state_placeholders);
    $state_filter_don = " AND state IN ($in_clause)";
    $state_filter_wak = " AND w.state IN ($in_clause)";
}

// Merge all params
$params = array_merge($params, $partner_params, $state_params);
// Separate params for donation-only queries (no partner join prefix on state)
$params_don_only = array_merge($params); // includes state params directly

$report_title = "Donation Collections Summary Report";
$records = [];
$summary_stats = [];

$months = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
];

// Build donation-only params (for queries that don't JOIN wakalah)
// Partner filter for non-joined donation queries uses a subquery approach
$partner_filter_don_noalias = "";
if (!empty($partner_name)) {
    $partner_filter_don_noalias = " AND wakalah_id IN (SELECT wakalah_id FROM wakalah WHERE name LIKE :partner_name)";
}

try {
    if ($report_type === 'donations') {
        $report_title = "Donation Collections Detail Report";
        
        // Query summary stats (donations table, no alias)
        $stmt_sum = $pdo->prepare("SELECT COUNT(donation_id) as count, COALESCE(SUM(amount), 0.0) as total, COALESCE(AVG(amount), 0.0) as average FROM donations WHERE 1=1" . $date_filter_don . $month_filter_don . $partner_filter_don_noalias . $state_filter_don);
        $stmt_sum->execute($params);
        $summary_stats = $stmt_sum->fetch();

        // Query records (with wakalah join using d. alias)
        $stmt_rec = $pdo->prepare("
            SELECT d.*, w.name as collector_name, w.branch_name, w.type as collector_type 
            FROM donations d
            LEFT JOIN wakalah w ON d.wakalah_id = w.wakalah_id
            WHERE 1=1 " . $date_filter_don . $month_filter_don . $partner_filter_don . str_replace(' AND state', ' AND d.state', $state_filter_don) . "
            ORDER BY d.donation_date DESC
        ");
        $stmt_rec->execute($params);
        $records = $stmt_rec->fetchAll();

    } elseif ($report_type === 'wakalah') {
        $report_title = "Wakalah Partner Collections Report";

        // Build wakalah-specific params (partner + state filters apply to wakalah table)
        $wak_params = array_merge($params);

        // Query summary stats
        $sum_partner_filter = !empty($partner_name) ? " AND type IN (SELECT type FROM wakalah WHERE name LIKE :partner_name_s)" : "";
        $sum_params = $params;
        // For summary, count wakalah with filters
        $ind_filter = $partner_filter_wak . $state_filter_wak;
        $corp_filter = $partner_filter_wak . $state_filter_wak;
        $stmt_sum = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM wakalah w WHERE type = 'individual'" . $partner_filter_wak . $state_filter_wak . ") as ind_count,
                (SELECT COUNT(*) FROM wakalah w WHERE type = 'corporate'" . $partner_filter_wak . $state_filter_wak . ") as corp_count,
                (SELECT COALESCE(SUM(amount), 0.0) FROM donations WHERE 1=1 " . $date_filter_don . $month_filter_don . $partner_filter_don_noalias . $state_filter_don . ") as total
        ");
        $stmt_sum->execute($params);
        $summary_stats = $stmt_sum->fetch();

        // Build records query
        $sql = "
            SELECT 
                w.wakalah_id as id,
                w.name, 
                w.type,
                w.email, 
                w.phone,
                w.state, 
                w.status,
                (SELECT COALESCE(SUM(amount), 0.0) FROM donations WHERE wakalah_id = w.wakalah_id " . $date_filter_don . $month_filter_don . $state_filter_don . ") as total_collections
            FROM wakalah w
            WHERE 1=1" . $partner_filter_wak . $state_filter_wak . "
            ORDER BY total_collections DESC
        ";
        $stmt_rec = $pdo->prepare($sql);
        $stmt_rec->execute($params);
        $records = $stmt_rec->fetchAll();

    } elseif ($report_type === 'states') {
        $report_title = "State-Based Donation Performance Report";

        // Summary stats
        $stmt_sum = $pdo->prepare("SELECT COUNT(DISTINCT state) as state_count, COALESCE(SUM(amount), 0.0) as total FROM donations WHERE 1=1" . $date_filter_don . $month_filter_don . $partner_filter_don_noalias . $state_filter_don);
        $stmt_sum->execute($params);
        $summary_stats = $stmt_sum->fetch();

        // Records
        $stmt_rec = $pdo->prepare("
            SELECT 
                state, 
                COUNT(donation_id) as donation_count, 
                COALESCE(SUM(amount), 0.0) as total_amount 
            FROM donations 
            WHERE 1=1 " . $date_filter_don . $month_filter_don . $partner_filter_don_noalias . $state_filter_don . "
            GROUP BY state 
            ORDER BY total_amount DESC
        ");
        $stmt_rec->execute($params);
        $records = $stmt_rec->fetchAll();

    } elseif ($report_type === 'channels') {
        $report_title = "Collection Channel Distribution Report";

        // Summary stats
        $stmt_sum = $pdo->prepare("SELECT COUNT(DISTINCT channel) as channel_count, COALESCE(SUM(amount), 0.0) as total FROM donations WHERE 1=1" . $date_filter_don . $month_filter_don . $partner_filter_don_noalias . $state_filter_don);
        $stmt_sum->execute($params);
        $summary_stats = $stmt_sum->fetch();

        // Records
        $stmt_rec = $pdo->prepare("
            SELECT 
                channel, 
                COUNT(donation_id) as donation_count, 
                COALESCE(SUM(amount), 0.0) as total_amount 
            FROM donations 
            WHERE 1=1 " . $date_filter_don . $month_filter_don . $partner_filter_don_noalias . $state_filter_don . "
            GROUP BY channel 
            ORDER BY total_amount DESC
        ");
        $stmt_rec->execute($params);
        $records = $stmt_rec->fetchAll();
    }
} catch (PDOException $e) {
    echo "<div style='color: var(--alert-red); font-weight: bold;'>Query Error: " . $e->getMessage() . "</div>";
}
?>

<!-- Filter & Criteria Card -->
<div class="chart-card" style="margin-bottom: 2rem;" id="card-report-filters">
    <div class="chart-header">
        <h3 class="chart-title"><i class="fa-solid fa-sliders" style="margin-right: 0.5rem; color: var(--primary-blue);"></i> Report Filter Criteria</h3>
    </div>
    
    <form method="GET" action="index.php" id="report-filter-form">
        <input type="hidden" name="page" value="reports">
        
        <!-- Row 1: Report Type, Month, Dates, Buttons -->
        <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; margin-bottom: 1rem;">
            <!-- Report Type Selection -->
            <div class="form-group" style="margin: 0; flex-grow: 1; min-width: 200px;">
                <label class="form-label" for="report_type">Select Report Type</label>
                <select name="report_type" id="report_type" class="form-select">
                    <option value="donations" <?php echo $report_type === 'donations' ? 'selected' : ''; ?>>Donation Collection Detailed Report</option>
                    <option value="wakalah" <?php echo $report_type === 'wakalah' ? 'selected' : ''; ?>>Wakalah Partner Performance Report</option>
                    <option value="states" <?php echo $report_type === 'states' ? 'selected' : ''; ?>>State-Based Donation Summary</option>
                    <option value="channels" <?php echo $report_type === 'channels' ? 'selected' : ''; ?>>Collection Channel Distribution</option>
                </select>
            </div>

            <!-- Filter by Month -->
            <div class="form-group" style="margin: 0; width: 160px;">
                <label class="form-label" for="month_filter">Filter by Month</label>
                <select name="month_filter" id="month_filter" class="form-select">
                    <option value="">All Months</option>
                    <?php foreach ($months as $month): ?>
                        <option value="<?php echo $month; ?>" <?php echo $selected_month === $month ? 'selected' : ''; ?>>
                            <?php echo $month; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Start Date -->
            <div class="form-group" style="margin: 0; width: 140px;">
                <label class="form-label" for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-input" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>

            <!-- End Date -->
            <div class="form-group" style="margin: 0; width: 140px;">
                <label class="form-label" for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-input" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>

            <!-- Action Button -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.25rem;">
                    <i class="fa-solid fa-magnifying-glass"></i> Generate
                </button>
                <a href="index.php?page=reports" class="btn btn-outline" style="padding: 0.65rem 1.25rem;" title="Reset filters">
                    Reset
                </a>
            </div>
        </div>

        <!-- Row 2: Advanced Filters (Partner Name + State) -->
        <div style="border-top: 1px solid var(--border-color, #e2e8f0); padding-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; cursor: pointer;" onclick="document.getElementById('adv-filter-panel').style.display = document.getElementById('adv-filter-panel').style.display === 'none' ? 'flex' : 'none'; this.querySelector('.adv-chevron').classList.toggle('fa-chevron-down'); this.querySelector('.adv-chevron').classList.toggle('fa-chevron-up');">
                <i class="fa-solid fa-filter" style="color: var(--primary-blue); font-size: 0.8rem;"></i>
                <span style="font-weight: 600; font-size: 0.85rem; color: var(--dark-neutral);">Advanced Filters</span>
                <i class="fa-solid <?php echo (!empty($partner_name) || !empty($state_filter)) ? 'fa-chevron-up' : 'fa-chevron-down'; ?> adv-chevron" style="font-size: 0.7rem; color: var(--medium-neutral);"></i>
                <?php if (!empty($partner_name) || !empty($state_filter)): ?>
                    <span style="font-size: 0.75rem; background: var(--light-blue, #eff6ff); color: var(--primary-blue); padding: 0.15rem 0.5rem; border-radius: 10px; font-weight: 600;">Active</span>
                <?php endif; ?>
            </div>
            <div id="adv-filter-panel" style="display: <?php echo (!empty($partner_name) || !empty($state_filter)) ? 'flex' : 'none'; ?>; flex-wrap: wrap; gap: 1.25rem; align-items: flex-start;">
                
                <!-- Wakalah Partner Name Search -->
                <div class="form-group" style="margin: 0; min-width: 220px; flex: 1;">
                    <label class="form-label" for="partner_name">
                        <i class="fa-solid fa-user-tag" style="margin-right: 0.3rem; color: var(--primary-blue);"></i> Search Wakalah Partner
                    </label>
                    <input type="text" name="partner_name" id="partner_name" class="form-input" 
                           list="partner-list" 
                           placeholder="Type partner name (e.g. Kamaruddin)" 
                           value="<?php echo htmlspecialchars($partner_name); ?>"
                           autocomplete="off"
                           style="font-size: 0.9rem;">
                    <datalist id="partner-list">
                        <?php foreach ($all_partners as $p): ?>
                            <option value="<?php echo htmlspecialchars($p); ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <small style="color: var(--medium-neutral); font-size: 0.75rem; margin-top: 0.25rem; display: block;">Searches by partial name match across all Wakalah partners.</small>
                </div>

                <!-- State (Negeri) Multi-Select Dropdown -->
                <div class="form-group" style="margin: 0; min-width: 280px; flex: 2;">
                    <label class="form-label" for="state_filter_select">
                        <i class="fa-solid fa-map-location-dot" style="margin-right: 0.3rem; color: var(--primary-blue);"></i> Filter by State (Negeri)
                    </label>
                    <select name="state_filter[]" id="state_filter_select" multiple 
                            class="form-select"
                            style="min-height: 110px; font-size: 0.85rem; padding: 0.4rem;">
                        <?php if (empty($all_states)): ?>
                            <option disabled>No state data available</option>
                        <?php else: ?>
                            <?php foreach ($all_states as $st): ?>
                                <option value="<?php echo htmlspecialchars($st); ?>"
                                    <?php echo in_array($st, $state_filter) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small style="color: var(--medium-neutral); font-size: 0.75rem; margin-top: 0.25rem; display: block;">Hold Ctrl (or Cmd) and click to select multiple states.</small>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Printable Header (Hidden on Web, visible on PDF Print) -->
<div style="display: none;" class="print-header">
    <div style="text-align: center; border-bottom: 2px solid var(--primary-blue); padding-bottom: 1rem; margin-bottom: 2rem;">
        <h2 style="font-weight: 800; color: var(--primary-blue); margin-bottom: 0.25rem;">YAYASAN WAKAF PENDIDIKAN ANAK YATIM ATAU MISKIN MALAYSIA</h2>
        <p style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--medium-neutral);">Deployment and Donation Monitoring System (YAWATIM)</p>
        <h3 style="font-weight: 700; margin-top: 1rem; color: var(--dark-neutral);"><?php echo $report_title; ?></h3>
        <p style="font-size: 0.8rem; color: var(--light-neutral); margin-top: 0.25rem;">
            Generated on: <?php echo date('d/m/Y H:i:s'); ?> 
            <?php 
                $periods = [];
                if (!empty($partner_name)) $periods[] = "Partner: " . htmlspecialchars($partner_name);
                if (!empty($state_filter)) $periods[] = "States: " . htmlspecialchars(implode(', ', $state_filter));
                if (!empty($selected_month)) $periods[] = "Month: " . htmlspecialchars($selected_month);
                if (!empty($start_date)) $periods[] = "From: " . htmlspecialchars($start_date);
                if (!empty($end_date)) $periods[] = "To: " . htmlspecialchars($end_date);
                echo count($periods) > 0 ? " | Filters: " . implode(" &bull; ", $periods) : "";
            ?>
        </p>
    </div>
</div>

<!-- Report Export Content (summary + table) -->
<div id="report-export-content">
    <div style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
        <div>
            <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--dark-neutral);">Donation Details Summary</h3>
            <p style="margin: 0.35rem 0 0; color: var(--medium-neutral); font-size: 0.95rem;">Includes total donations raised, transaction count, and average collection.</p>
        </div>
    </div>
    <!-- Report Summary cards -->
    <div class="stats-grid" id="report-summary-cards" style="margin-bottom: 1.5rem;">
    <?php if ($report_type === 'donations'): ?>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Total Donations Raised</span>
                <span class="stat-val">RM <?php echo number_format($summary_stats['total'], 2); ?></span>
            </div>
            <div class="stat-icon-wrapper green"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Transactions Count</span>
                <span class="stat-val"><?php echo $summary_stats['count']; ?> collections</span>
            </div>
            <div class="stat-icon-wrapper blue"><i class="fa-solid fa-receipt"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Average Collection</span>
                <span class="stat-val">RM <?php echo number_format($summary_stats['average'], 2); ?></span>
            </div>
            <div class="stat-icon-wrapper blue"><i class="fa-solid fa-chart-line"></i></div>
        </div>
    <?php elseif ($report_type === 'wakalah'): ?>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Wakalah Individuals</span>
                <span class="stat-val"><?php echo $summary_stats['ind_count']; ?> partners</span>
            </div>
            <div class="stat-icon-wrapper blue"><i class="fa-solid fa-user-tie"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Wakalah Corporates</span>
                <span class="stat-val"><?php echo $summary_stats['corp_count']; ?> partners</span>
            </div>
            <div class="stat-icon-wrapper blue"><i class="fa-solid fa-building-user"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Total Donations Raised</span>
                <span class="stat-val">RM <?php echo number_format($summary_stats['total'], 2); ?></span>
            </div>
            <div class="stat-icon-wrapper green"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
    <?php else: ?>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Total Donation Revenue</span>
                <span class="stat-val">RM <?php echo number_format($summary_stats['total'] ?? 0, 2); ?></span>
            </div>
            <div class="stat-icon-wrapper green"><i class="fa-solid fa-money-bill-trend-up"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <span class="stat-label">Entity Breakdown Count</span>
                <span class="stat-val">
                    <?php echo isset($summary_stats['state_count']) ? $summary_stats['state_count'] . ' states' : ''; ?>
                    <?php echo isset($summary_stats['channel_count']) ? $summary_stats['channel_count'] . ' channels' : ''; ?>
                </span>
            </div>
            <div class="stat-icon-wrapper blue"><i class="fa-solid fa-chart-pie"></i></div>
        </div>
    <?php endif; ?>
</div>

<!-- Report Table Container -->
<div class="table-card" id="report-table-card">
    <div class="table-controls-container" style="border: none; padding: 1.5rem 1.5rem 0.5rem 1.5rem;" id="report-export-controls">
        <h4 style="font-weight: 700; color: var(--dark-neutral); font-size: 0.95rem;">Generated Records Table</h4>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-outline" data-export-csv="report-table" id="btn-export-excel">
                <i class="fa-solid fa-file-excel" style="color: #16a34a;"></i> Export to Excel
            </button>
            <button class="btn btn-outline" data-export-pdf="report-export-content" id="btn-export-pdf">
                <i class="fa-solid fa-file-pdf" style="color: #dc2626;"></i> Download PDF
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="table-yawatim" id="report-table">
            <?php if ($report_type === 'donations'): ?>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Donor Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Booth Location</th>
                        <th>State Branch</th>
                        <th>Channel</th>
                        <th>Wakalah</th>
                        <th style="text-align: right;">Amount (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) === 0): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--light-neutral); padding: 2rem;">No matching records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $rec): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($rec['donation_date'])); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($rec['donor_name']); ?></td>
                                <td><?php echo htmlspecialchars($rec['donor_phone'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($rec['donor_email'] ?: '-'); ?></td>
                                <td>
                                    <?php 
                                        $loc = $rec['location'] ?: '-';
                                        if (($rec['collector_type'] ?? '') === 'corporate' && $loc === '-') {
                                            $partner_name = $rec['collector_name'] ?: 'Corporate Partner';
                                            echo htmlspecialchars($partner_name . ' ' . $rec['state']);
                                        } else {
                                            echo htmlspecialchars((($rec['collector_type'] ?? '') === 'individual') ? '-' : $loc);
                                        }
                                    ?>
                                </td>
                                <td><span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue);"><?php echo htmlspecialchars($rec['state']); ?></span></td>
                                <td><span class="badge"><?php echo htmlspecialchars($rec['channel'] ?: '-'); ?></span></td>
                                <td>
                                     <div style="display: flex; flex-direction: column;">
                                         <span style="font-size: 0.95rem; font-weight: 600; color: var(--dark-neutral);">
                                             <?php 
                                                 if (($rec['collector_type'] ?? '') === 'corporate' || ($rec['collector_type'] ?? '') === 'individual') {
                                                     echo htmlspecialchars($rec['collector_name']);
                                                 } else {
                                                     echo 'Direct YAWATIM';
                                                 }
                                             ?>
                                         </span>
                                         <span style="font-size: 0.85rem; font-weight: 600; white-space: nowrap; color: <?php echo ($rec['collector_type'] ?? '') === 'corporate' ? 'var(--primary-blue)' : (($rec['collector_type'] ?? '') === 'individual' ? 'var(--success-green)' : 'var(--medium-neutral)'); ?>;">
                                             <?php echo ($rec['collector_type'] ?? '') === 'corporate' ? 'Wakalah Corporate' : (($rec['collector_type'] ?? '') === 'individual' ? 'Wakalah Individual' : 'Direct'); ?>
                                         </span>
                                     </div>
                                </td>
                                <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                    RM <?php echo number_format($rec['amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($report_type === 'wakalah'): ?>
                <thead>
                    <tr>
                        <th>Partner Name</th>
                        <th>Partner Type</th>
                        <th>Email Address</th>
                        <th>Phone Number</th>
                        <th>Home State</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total Donations Raised</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) === 0): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--light-neutral); padding: 2rem;">No wakalah partner records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $rec): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--primary-blue);"><?php echo htmlspecialchars($rec['name']); ?></td>
                                <td style="text-transform: capitalize; font-weight: 600;"><?php echo htmlspecialchars($rec['type']); ?></td>
                                <td><?php echo htmlspecialchars($rec['email']); ?></td>
                                <td><?php echo htmlspecialchars($rec['phone']); ?></td>
                                <td><?php echo htmlspecialchars($rec['state']); ?></td>
                                <td><span class="badge <?php echo strtolower($rec['status']); ?>"><?php echo $rec['status']; ?></span></td>
                                <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                    RM <?php echo number_format($rec['total_collections'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($report_type === 'states'): ?>
                <thead>
                    <tr>
                        <th>State / Territory</th>
                        <th style="text-align: center;">Transactions Count</th>
                        <th style="text-align: right;">Total Donations Raised</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--light-neutral); padding: 2rem;">No matching records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $rec): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($rec['state']); ?></td>
                                <td style="text-align: center; font-weight: 500;"><?php echo $rec['donation_count']; ?></td>
                                <td style="font-weight: 700; color: var(--primary-blue); text-align: right;">
                                    RM <?php echo number_format($rec['total_amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            <?php elseif ($report_type === 'channels'): ?>
                <thead>
                    <tr>
                        <th>Collection Channel</th>
                        <th style="text-align: center;">Transactions Count</th>
                        <th style="text-align: right;">Total Donations Raised</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--light-neutral); padding: 2rem;">No matching records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $rec): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($rec['channel']); ?></td>
                                <td style="text-align: center; font-weight: 500;"><?php echo $rec['donation_count']; ?></td>
                                <td style="font-weight: 700; color: var(--primary-blue); text-align: right;">
                                    RM <?php echo number_format($rec['total_amount'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            <?php endif; ?>
        </table>
    </div>
</div>
</div>

<style>
@media print {
    .print-header {
        display: block !important;
    }
    #card-report-filters, #report-export-controls {
        display: none !important;
    }
    #report-table-card {
        border: none !important;
        margin: 0 !important;
    }
}
</style>