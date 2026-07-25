<?php
// modules/dashboard.php - Wakalah Refactored Dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'wakalah_individual';
$wakalah_id = $_SESSION['wakalah_id'] ?? null;
$user_state = $_SESSION['state'] ?? 'Selangor';
$user_channel = $_SESSION['channel'] ?? null;

// Initialize Statistics Variables
$total_donations = 0.0;
$total_ind = 0;
$total_corp = 0;
$active_booths = 0;
$my_rank = 1;
$leaderboard = [];
$leaderboard_corp = [];
$recent_donations = [];
$state_labels = [];
$state_values = [];
$channel_labels = [];
$channel_values = [];
$trend_labels = [];
$trend_values = [];
$corp_states = [];

// Month mapping for chronological sorting
$month_names = [
    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun',
    '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
];

try {
    if ($role === 'admin') {
        // ADMIN DASHBOARD QUERIES
        $total_donations = $pdo->query("SELECT SUM(amount) FROM donations")->fetchColumn() ?? 0.0;
        $total_ind = $pdo->query("SELECT COUNT(*) FROM wakalah WHERE type = 'individual'")->fetchColumn();
        $total_corp = $pdo->query("SELECT COUNT(*) FROM wakalah WHERE type = 'corporate'")->fetchColumn();
        $active_booths = $pdo->query("SELECT COUNT(*) FROM booths WHERE status = 'Active'")->fetchColumn();

        // 1. Data for State Chart
        $state_query = $pdo->query("SELECT state, SUM(amount) as total FROM donations GROUP BY state ORDER BY total DESC LIMIT 6");
        $state_labels = [];
        $state_values = [];
        while ($row = $state_query->fetch()) {
            $state_labels[] = $row['state'];
            $state_values[] = (float)$row['total'];
        }

        // 2. Data for Channel Chart
        $channel_query = $pdo->query("SELECT channel, SUM(amount) as total FROM donations GROUP BY channel");
        $channel_labels = [];
        $channel_values = [];
        while ($row = $channel_query->fetch()) {
            $channel_labels[] = $row['channel'];
            $channel_values[] = (float)$row['total'];
        }

        // 3. Data for Monthly Trends (Chronological)
        $trend_query = $pdo->query("
            SELECT DATE_FORMAT(donation_date, '%m') as month_num, SUM(amount) as total 
            FROM donations 
            GROUP BY month_num 
            ORDER BY month_num ASC
        ");
        $trend_labels = [];
        $trend_values = [];
        while ($row = $trend_query->fetch()) {
            $trend_labels[] = $month_names[$row['month_num']] ?? $row['month_num'];
            $trend_values[] = (float)$row['total'];
        }

        // 4. Top Performing Individual Leaders
        $leaderboard = $pdo->query("
            SELECT w.name, w.email, w.state, SUM(d.amount) as total 
            FROM donations d 
            JOIN wakalah w ON d.wakalah_id = w.id 
            WHERE w.type = 'individual'
            GROUP BY w.id 
            ORDER BY total DESC 
            LIMIT 5
        ")->fetchAll();

        // 4b. Top Performing Corporate Leaders
        $leaderboard_corp = $pdo->query("
            SELECT w.name, w.email, w.state, SUM(d.amount) as total 
            FROM donations d 
            JOIN wakalah w ON d.wakalah_id = w.id 
            WHERE w.type = 'corporate'
            GROUP BY w.id 
            ORDER BY total DESC 
            LIMIT 5
        ")->fetchAll();

        // 5. Recent Donations
        $recent_donations = $pdo->query("
            SELECT d.*, w.name as collector_name, w.type as collector_type 
            FROM donations d
            LEFT JOIN wakalah w ON d.wakalah_id = w.id
            ORDER BY d.donation_date DESC, d.id DESC
            LIMIT 5
        ")->fetchAll();

    } else {
        // WAKALAH PARTNER DASHBOARD (Individual & Corporate) - filtered by channel
        $channel_filter = $user_channel ? "AND channel = " . $pdo->quote($user_channel) : "";

        $total_donations = $pdo->query("SELECT SUM(amount) FROM donations WHERE wakalah_id = $wakalah_id")->fetchColumn() ?? 0.0;
        
        // Count active assets filtered by channel
        $active_booths = $pdo->query("SELECT COUNT(*) FROM booths WHERE state = '$user_state' AND status = 'Active' AND channel = '$user_channel'")->fetchColumn();
        $total_ind = $pdo->query("SELECT COUNT(*) FROM wakalah WHERE state = '$user_state' AND type = 'individual' $channel_filter")->fetchColumn();
        $total_corp = $pdo->query("SELECT COUNT(*) FROM wakalah WHERE state = '$user_state' AND type = 'corporate' $channel_filter")->fetchColumn();

        // If corporate, fetch collections per state for the Collection States pie chart
        $corp_states = [];
        if ($role === 'wakalah_corporate') {
            $corp_states = $pdo->query("
                SELECT
                    state,
                    COUNT(id) as count,
                    SUM(amount) as total
                FROM donations
                WHERE wakalah_id = $wakalah_id
                  AND state IS NOT NULL AND state != '-'
                GROUP BY state
                ORDER BY total DESC
            ")->fetchAll();
        }

        // Resolve Rank within same channel
        $type = ($role === 'wakalah_individual') ? 'individual' : 'corporate';
        $rankings = $pdo->query("
            SELECT w.id, COALESCE(SUM(d.amount), 0.0) as total_collected
            FROM wakalah w
            LEFT JOIN donations d ON w.id = d.wakalah_id
            WHERE w.type = '$type' AND w.channel = '$user_channel'
            GROUP BY w.id
            ORDER BY total_collected DESC
        ")->fetchAll();
        
        foreach ($rankings as $index => $r) {
            if ($r['id'] == $wakalah_id) {
                $my_rank = $index + 1;
                break;
            }
        }

        // 1. Personal Channel Chart (donations by channel — only own channel)
        $channel_query = $pdo->query("SELECT channel, SUM(amount) as total FROM donations WHERE wakalah_id = $wakalah_id GROUP BY channel");
        $channel_labels = [];
        $channel_values = [];
        while ($row = $channel_query->fetch()) {
            $channel_labels[] = $row['channel'];
            $channel_values[] = (float)$row['total'];
        }

        // 2. Personal Monthly Trends (Chronological)
        $trend_query = $pdo->query("
            SELECT DATE_FORMAT(donation_date, '%m') as month_num, SUM(amount) as total 
            FROM donations 
            WHERE wakalah_id = $wakalah_id 
            GROUP BY month_num 
            ORDER BY month_num ASC
        ");
        $trend_labels = [];
        $trend_values = [];
        while ($row = $trend_query->fetch()) {
            $trend_labels[] = $month_names[$row['month_num']] ?? $row['month_num'];
            $trend_values[] = (float)$row['total'];
        }

        // 3. Leaderboard
        // - For corporate: show ALL 4 corporate partners ranked (no channel filter)
        // - For individual: filter by same channel so they compete within their channel
        if ($role === 'wakalah_corporate') {
            $leaderboard = $pdo->query("
                SELECT w.name, w.email, w.channel as state, COALESCE(SUM(d.amount), 0) as total 
                FROM wakalah w
                LEFT JOIN donations d ON d.wakalah_id = w.id
                WHERE w.type = 'corporate'
                GROUP BY w.id 
                ORDER BY total DESC 
                LIMIT 5
            ")->fetchAll();
        } else {
            $leaderboard = $pdo->query("
                SELECT w.name, w.email, w.state, COALESCE(SUM(d.amount), 0) as total 
                FROM wakalah w
                LEFT JOIN donations d ON d.wakalah_id = w.id
                WHERE w.type = 'individual' AND w.channel = '$user_channel'
                GROUP BY w.id 
                ORDER BY total DESC 
                LIMIT 5
            ")->fetchAll();
        }

        // 4. Recent Personal Donations
        $recent_donations = $pdo->query("
            SELECT d.*, w.name as collector_name 
            FROM donations d
            LEFT JOIN wakalah w ON d.wakalah_id = w.id
            WHERE d.wakalah_id = $wakalah_id
            ORDER BY d.donation_date DESC, d.id DESC
            LIMIT 5
        ")->fetchAll();
    }

    // Pack Chart Data as JSON
    $chart_js_data = [
        'byState' => ($role === 'admin') ? ['labels' => $state_labels, 'data' => $state_values] : null,
        'corpStates' => ($role === 'wakalah_corporate' ? ['labels' => array_column($corp_states, 'state'), 'data' => array_column($corp_states, 'total')] : null),
        'byChannel' => ['labels' => $channel_labels, 'data' => $channel_values],
        'trends' => ['labels' => $trend_labels, 'data' => $trend_values]
    ];

} catch (PDOException $e) {
    echo "<div style='color: var(--alert-red); font-weight: bold;'>Query Error: " . $e->getMessage() . "</div>";
}
?>

<!-- Stat Cards - Only Total Donations -->
<div class="stats-grid" id="stats-dashboard-grid">
    <!-- Stat Card 1 - Total Donations Only -->
    <div class="stat-card" id="card-total-donations">
        <div class="stat-info">
            <span class="stat-label"><?php echo $role === 'admin' ? 'Total Donations Raised' : 'My Total Collections'; ?></span>
            <span class="stat-val">RM <?php echo number_format($total_donations, 2); ?></span>
        </div>
        <div class="stat-icon-wrapper green">
            <i class="fa-solid fa-sack-dollar"></i>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid" id="charts-dashboard-grid" style="<?php echo $role === 'wakalah_individual' ? 'display: flex; justify-content: center;' : ''; ?>">
    <!-- Main Trend Chart -->
    <div class="chart-card" id="card-trend-chart" style="<?php echo $role === 'wakalah_individual' ? 'width: 100%; max-width: 800px;' : ''; ?>">
        <div class="chart-header">
            <h3 class="chart-title">
                <?php echo $role === 'admin' ? 'Monthly Donation Trends (Overall)' : 'My Monthly Donation Trends'; ?>
            </h3>
            <span style="font-size: 0.8rem; color: var(--light-neutral); font-weight: 500;">Chronological Month Split</span>
        </div>
        <div class="chart-container">
            <canvas id="chartMonthlyTrends"></canvas>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
    <!-- Doughnut Channel Chart -->
    <div class="chart-card" id="card-channel-chart">
        <div class="chart-header">
            <h3 class="chart-title">Collection Channels</h3>
        </div>
        <div class="chart-container">
            <canvas id="chartDonationsByChannel"></canvas>
        </div>
    </div>
    <?php elseif ($role === 'wakalah_corporate'): ?>
    <!-- Collection States -->
    <div class="chart-card" id="card-corporate-states-info">
        <div class="chart-header" style="border-bottom: 1px solid var(--border-light); padding-bottom: 0.75rem;">
            <h3 class="chart-title"><i class="fa-solid fa-map-location-dot" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> Collection States</h3>
            <span class="badge" style="background-color: var(--light-green); color: var(--success-green); font-weight: 600;"><?php echo count($corp_states); ?> States</span>
        </div>
        <div class="chart-container">
            <canvas id="chartCorporateStates"></canvas>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Leaderboard Section -->
<div class="charts-grid" style="grid-template-columns: <?php echo $role === 'admin' ? 'repeat(auto-fit, minmax(300px, 1fr))' : '1fr'; ?>;" id="leaderboard-full-width">
    <?php if ($role === 'admin'): ?>
        <!-- Admin Leaderboard - Individual -->
        <div class="chart-card" id="card-leaderboard-ind" style="padding: 1.5rem;">
            <div class="chart-header" style="margin-bottom: 1.5rem;">
                <div>
                    <h3 class="chart-title" style="font-size: 1.28rem;">
                        <i class="fa-solid fa-trophy" style="color: #FFD700; margin-right: 0.5rem;"></i>
                        Top Individual Partners
                    </h3>
                    <span style="font-size: 0.8rem; color: var(--light-neutral);">Top 5 performers by collection amount</span>
                </div>
            </div>
            <div class="leaderboard-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($leaderboard as $index => $leader): ?>
                    <div class="leaderboard-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: <?php echo $index === 0 ? 'var(--light-gold, #FFF8E7)' : 'var(--bg-light, #F8F9FA)'; ?>; border-radius: 10px; border-left: 4px solid <?php 
                        echo $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : ($index === 2 ? '#CD7F32' : 'transparent')); 
                    ?>;">
                        <div class="leaderboard-rank-profile" style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                            <div class="leaderboard-rank" style="display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background: <?php 
                                echo $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : ($index === 2 ? '#CD7F32' : 'var(--light-neutral)')); 
                            ?>; color: <?php echo $index < 3 ? '#fff' : 'var(--dark-neutral)'; ?>; font-weight: 700; font-size: 0.9rem;">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="leaderboard-name-section" style="display: flex; flex-direction: column;">
                                <span class="leaderboard-name" style="font-weight: 600; font-size: 1.05rem; color: var(--dark-neutral);">
                                    <?php echo htmlspecialchars($leader['name']); ?>
                                </span>
                                <span class="leaderboard-sub" style="font-size: 0.85rem; color: var(--light-neutral);">
                                    <?php echo htmlspecialchars($leader['email']); ?>
                                </span>
                            </div>
                        </div>
                        <span class="leaderboard-amount" style="font-weight: 700; font-size: 1.15rem; color: var(--success-green);">
                            RM <?php echo number_format($leader['total'], 2); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Admin Leaderboard - Corporate -->
        <div class="chart-card" id="card-leaderboard-corp" style="padding: 1.5rem;">
            <div class="chart-header" style="margin-bottom: 1.5rem;">
                <div>
                    <h3 class="chart-title" style="font-size: 1.28rem;">
                        <i class="fa-solid fa-building" style="color: var(--primary-blue); margin-right: 0.5rem;"></i>
                        Top Corporate Partners
                    </h3>
                    <span style="font-size: 0.8rem; color: var(--light-neutral);">Top 5 performers by collection amount</span>
                </div>
            </div>
            <div class="leaderboard-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($leaderboard_corp as $index => $leader): ?>
                    <div class="leaderboard-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: <?php echo $index === 0 ? 'var(--light-gold, #FFF8E7)' : 'var(--bg-light, #F8F9FA)'; ?>; border-radius: 10px; border-left: 4px solid <?php 
                        echo $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : ($index === 2 ? '#CD7F32' : 'transparent')); 
                    ?>;">
                        <div class="leaderboard-rank-profile" style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                            <div class="leaderboard-rank" style="display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background: <?php 
                                echo $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : ($index === 2 ? '#CD7F32' : 'var(--light-neutral)')); 
                            ?>; color: <?php echo $index < 3 ? '#fff' : 'var(--dark-neutral)'; ?>; font-weight: 700; font-size: 0.9rem;">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="leaderboard-name-section" style="display: flex; flex-direction: column;">
                                <span class="leaderboard-name" style="font-weight: 600; font-size: 1.05rem; color: var(--dark-neutral);">
                                    <?php echo htmlspecialchars($leader['name']); ?>
                                </span>
                                <span class="leaderboard-sub" style="font-size: 0.85rem; color: var(--light-neutral);">
                                    <?php echo htmlspecialchars($leader['email']); ?>
                                </span>
                            </div>
                        </div>
                        <span class="leaderboard-amount" style="font-weight: 700; font-size: 1.15rem; color: var(--success-green);">
                            RM <?php echo number_format($leader['total'], 2); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Partner Leaderboard -->
        <div class="chart-card" id="card-leaderboard" style="padding: 1.5rem;">
            <div class="chart-header" style="margin-bottom: 1.5rem;">
                <div>
                    <h3 class="chart-title" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-trophy" style="color: #FFD700; margin-right: 0.75rem;"></i>
                        Top Wakalah Leaderboard
                    </h3>
                    <span style="font-size: 0.8rem; color: var(--light-neutral);">Top 5 performers by collection amount</span>
                </div>
                <span class="badge" style="background-color: var(--light-green); color: var(--success-green); padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 600;">
                    <i class="fa-solid fa-crown" style="margin-right: 0.25rem;"></i> Leaderboard
                </span>
            </div>
            <div class="leaderboard-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($leaderboard as $index => $leader): ?>
                    <div class="leaderboard-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: <?php echo $index === 0 ? 'var(--light-gold, #FFF8E7)' : 'var(--bg-light, #F8F9FA)'; ?>; border-radius: 10px; border-left: 4px solid <?php 
                        echo $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : ($index === 2 ? '#CD7F32' : 'transparent')); 
                    ?>;">
                        <div class="leaderboard-rank-profile" style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                            <div class="leaderboard-rank" style="display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border-radius: 50%; background: <?php 
                                echo $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : ($index === 2 ? '#CD7F32' : 'var(--light-neutral)')); 
                            ?>; color: <?php echo $index < 3 ? '#fff' : 'var(--dark-neutral)'; ?>; font-weight: 700; font-size: 0.9rem;">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="leaderboard-name-section" style="display: flex; flex-direction: column;">
                                <span class="leaderboard-name" style="font-weight: 600; font-size: 1.05rem; color: var(--dark-neutral);">
                                    <?php echo htmlspecialchars($leader['name']); ?>
                                </span>
                                <span class="leaderboard-sub" style="font-size: 0.85rem; color: var(--light-neutral);">
                                    <?php echo htmlspecialchars($leader['email']); ?>
                                    <?php if ($role === 'wakalah_corporate'): ?>
                                        &bull; <?php echo htmlspecialchars($leader['state']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <span class="leaderboard-amount" style="font-weight: 700; font-size: 1.15rem; color: var(--success-green);">
                            RM <?php echo number_format($leader['total'], 2); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($role !== 'wakalah_individual'): ?>
<!-- Recent Donations Table -->
<div class="table-card" style="margin-top: 1.5rem;" id="card-recent-donations-table">
    <div class="chart-header" style="padding: 1.5rem 1.5rem 0.5rem 1.5rem;">
        <h3 class="chart-title"><i class="fa-solid fa-list-check" style="margin-right: 0.5rem; color: var(--primary-blue);"></i> Recent Donation Logs</h3>
        <a href="index.php?page=donations" class="btn btn-outline" style="padding: 0.35rem 0.75rem; font-size: 0.75rem; border-radius: 6px;">View All Logs</a>
    </div>
    <div class="table-wrapper">
        <table class="table-yawatim">
            <thead>
                <tr>
                    <th>Date Collected</th>
                    <th>Month</th>
                    <th>Donor Name</th>
                    <?php if ($role === 'admin'): ?>
                        <th>State Branch</th>
                        <th>Wakalah</th>
                    <?php elseif ($role === 'wakalah_corporate'): ?>
                        <th>Booth Location</th>
                        <th>State Branch</th>
                    <?php endif; ?>
                    <th style="text-align: right;">Amount Raised</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_donations) === 0): ?>
                    <tr>
                        <td colspan="<?php echo $role === 'wakalah_individual' ? 4 : 6; ?>" style="text-align: center; color: var(--light-neutral); padding: 1.5rem;">No recent donations recorded.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_donations as $don): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($don['donation_date'])); ?></td>
                            <td><?php echo htmlspecialchars($don['donation_month']); ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($don['donor_name']); ?></td>
                            <?php if ($role === 'admin'): ?>
                                <td>
                                    <span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">
                                        <?php echo htmlspecialchars($don['state']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600; color: var(--dark-neutral);">
                                            <?php echo htmlspecialchars($don['collector_name'] ?: 'Direct YAWATIM'); ?>
                                        </span>
                                        <span style="font-size: 0.8rem; font-weight: 600; color: <?php echo ($don['collector_type'] ?? '') === 'corporate' ? 'var(--primary-blue)' : (($don['collector_type'] ?? '') === 'individual' ? 'var(--success-green)' : 'var(--medium-neutral)'); ?>;">
                                            <?php echo ($don['collector_type'] ?? '') === 'corporate' ? 'Wakalah Corporate' : (($don['collector_type'] ?? '') === 'individual' ? 'Wakalah Individual' : 'Direct'); ?>
                                        </span>
                                    </div>
                                </td>
                            <?php elseif ($role === 'wakalah_corporate'): ?>
                                <td>
                                    <?php 
                                        $loc = htmlspecialchars($don['location']);
                                        if ($loc === '-') {
                                            $partner_name = $don['collector_name'] ?: ($_SESSION['name'] ?? 'Corporate Partner');
                                            echo htmlspecialchars($partner_name . ' ' . $don['state']);
                                        } else {
                                            echo $loc;
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($don['state']); ?></td>
                            <?php endif; ?>
                            <td style="font-weight: 700; color: var(--success-green); text-align: right;">RM <?php echo number_format($don['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Load Charts JS Data -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const chartData = <?php echo json_encode($chart_js_data); ?>;
    renderDashboardCharts(chartData);
});
</script>