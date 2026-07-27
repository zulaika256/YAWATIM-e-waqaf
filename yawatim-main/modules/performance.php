<?php
// modules/performance.php - Wakalah Leaderboards and Rankings (Role-Aware)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'wakalah_individual';
$wakalah_id = $_SESSION['wakalah_id'] ?? null;
$user_state = $_SESSION['state'] ?? 'Selangor';
$user_channel = $_SESSION['channel'] ?? null;

$individual_rankings = [];
$corporate_rankings = [];
$state_corporate_rankings = [];

try {
    // 1. Fetch Individual Wakalah Leaderboard
    if ($role === 'admin' || $role === 'wakalah_individual') {
        $ch_filter = ($role !== 'admin' && !empty($user_channel)) ? "AND w.channel = " . $pdo->quote($user_channel) : "";
        $individual_rankings = $pdo->query("
            SELECT 
                w.wakalah_id as id,
                w.name, 
                w.email, 
                w.state, 
                w.status,
                (SELECT COALESCE(SUM(amount), 0.0) FROM donations WHERE wakalah_id = w.wakalah_id) as total_collections
            FROM wakalah w
            WHERE w.type = 'individual' $ch_filter
            ORDER BY total_collections DESC
        ")->fetchAll();
    }

    // 2. Fetch Corporate Wakalah Leaderboard
    if ($role === 'admin' || $role === 'wakalah_corporate') {
        // Admin filters nothing; corporate users see ALL corporate partners (global ranking)
        $ch_filter = ($role === 'admin' && $user_channel) ? "AND w.channel = " . $pdo->quote($user_channel) : "";
        $corporate_rankings = $pdo->query("
            SELECT 
                w.wakalah_id as id,
                w.name, 
                w.email, 
                w.state, 
                w.status,
                (SELECT COALESCE(SUM(amount), 0.0) FROM donations WHERE wakalah_id = w.wakalah_id) as total_collections
            FROM wakalah w
            WHERE w.type = 'corporate'
            ORDER BY total_collections DESC
        ")->fetchAll();
    }

    // 3. State Rankings — filtered by same channel for wakalah users
    if ($role === 'wakalah_corporate' || $role === 'wakalah_individual') {
        $ch_filter = !empty($user_channel) ? "AND channel = " . $pdo->quote($user_channel) : "";
        $state_rankings = $pdo->query("
            SELECT 
                state, 
                SUM(amount) as total_collected
            FROM donations
            WHERE amount >= 0 AND state != '-' $ch_filter
            GROUP BY state
            ORDER BY total_collected DESC
        ")->fetchAll();
    } else {
        $state_rankings = [];
    }
} catch (PDOException $e) {
    echo "<div style='color: var(--alert-red); font-weight: bold;'>Query Error: " . $e->getMessage() . "</div>";
}
?>

<?php if ($role === 'admin'): ?>
    <!-- ADMIN LEADERBOARD VIEW (Side-by-side or double columns) -->
    <div class="charts-grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        
        <!-- Individual Leaderboard -->
        <div class="chart-card" style="padding: 0; overflow: hidden;" id="card-perf-individual-leaderboard">
            <div class="chart-header" style="padding: 1.5rem 1.5rem 0.5rem 1.5rem; border-bottom: 1px solid var(--border-light);">
                <h3 class="chart-title"><i class="fa-solid fa-user-tie" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> Individual Partner Leaderboard</h3>
                <span class="badge active" style="font-size: 0.65rem; background-color: var(--light-blue); color: var(--primary-blue);">Individual</span>
            </div>
            <div class="table-wrapper">
                <table class="table-yawatim">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Rank</th>
                            <th>Partner Name</th>
                            <th>State Branch</th>
                            <th>Status</th>
                            <th style="text-align: right;">Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($individual_rankings as $index => $rank): ?>
                            <tr>
                                <td>
                                    <?php
                                        $rank_num = $index + 1;
                                        if ($rank_num === 1) echo '<span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 0.35rem 0.6rem; border-radius: 50%;"><i class="fa-solid fa-crown"></i> 1</span>';
                                        elseif ($rank_num === 2) echo '<span class="badge" style="background-color: #f1f5f9; color: #64748b; padding: 0.35rem 0.6rem; border-radius: 50%;">2</span>';
                                        elseif ($rank_num === 3) echo '<span class="badge" style="background-color: #ffedd5; color: #ea580c; padding: 0.35rem 0.6rem; border-radius: 50%;">3</span>';
                                        else echo '<span style="padding-left: 0.5rem; font-weight: 700; color: var(--light-neutral);">' . $rank_num . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($rank['name']); ?></span>
                                        <span style="font-size: 0.72rem; color: var(--light-neutral);"><?php echo htmlspecialchars($rank['email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($rank['state'] ?: '-'); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo strtolower($rank['status']); ?>"><?php echo $rank['status']; ?></span>
                                </td>
                                <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                    RM <?php echo number_format($rank['total_collections'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Corporate Leaderboard -->
        <div class="chart-card" style="padding: 0; overflow: hidden;" id="card-perf-corporate-leaderboard">
            <div class="chart-header" style="padding: 1.5rem 1.5rem 0.5rem 1.5rem; border-bottom: 1px solid var(--border-light);">
                <h3 class="chart-title"><i class="fa-solid fa-building-user" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> Corporate Partner Leaderboard</h3>
                <span class="badge active" style="font-size: 0.65rem; background-color: var(--light-green); color: var(--success-green);">Corporate</span>
            </div>
            <div class="table-wrapper">
                <table class="table-yawatim">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Rank</th>
                            <th>Company Name</th>
                            <th>State Branch</th>
                            <th>Status</th>
                            <th style="text-align: right;">Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($corporate_rankings as $index => $rank): ?>
                            <tr>
                                <td>
                                    <?php
                                        $rank_num = $index + 1;
                                        if ($rank_num === 1) echo '<span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 0.35rem 0.6rem; border-radius: 50%;"><i class="fa-solid fa-crown"></i> 1</span>';
                                        elseif ($rank_num === 2) echo '<span class="badge" style="background-color: #f1f5f9; color: #64748b; padding: 0.35rem 0.6rem; border-radius: 50%;">2</span>';
                                        elseif ($rank_num === 3) echo '<span class="badge" style="background-color: #ffedd5; color: #ea580c; padding: 0.35rem 0.6rem; border-radius: 50%;">3</span>';
                                        else echo '<span style="padding-left: 0.5rem; font-weight: 700; color: var(--light-neutral);">' . $rank_num . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($rank['name']); ?></span>
                                        <span style="font-size: 0.72rem; color: var(--light-neutral);"><?php echo htmlspecialchars($rank['email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($rank['state'] ?: '-'); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo strtolower($rank['status']); ?>"><?php echo $rank['status']; ?></span>
                                </td>
                                <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                    RM <?php echo number_format($rank['total_collections'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($role === 'wakalah_individual'): ?>
    <!-- INDIVIDUAL WAKALAH LEADERBOARD (Highlights self) -->
    <div class="charts-grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="chart-card" style="padding: 0; overflow: hidden;" id="card-perf-individual-leaderboard">
        <div class="chart-header" style="padding: 1.5rem; border-bottom: 1px solid var(--border-light);">
            <h3 class="chart-title"><i class="fa-solid fa-user-tie" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> Individual Partner Leaderboard</h3>
        </div>
        <div class="table-wrapper">
            <table class="table-yawatim">
                <thead>
                    <tr>
                        <th style="width: 70px;">Rank</th>
                        <th>Partner Name</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total Collected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($individual_rankings as $index => $rank): ?>
                        <?php $is_self = ($rank['id'] == $wakalah_id); ?>
                        <tr style="<?php echo $is_self ? 'background-color: var(--light-blue); font-weight: 600;' : ''; ?>">
                            <td>
                                <?php
                                    $rank_num = $index + 1;
                                    if ($rank_num === 1) echo '<span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 0.35rem 0.6rem; border-radius: 50%;"><i class="fa-solid fa-crown"></i> 1</span>';
                                    elseif ($rank_num === 2) echo '<span class="badge" style="background-color: #f1f5f9; color: #64748b; padding: 0.35rem 0.6rem; border-radius: 50%;">2</span>';
                                    elseif ($rank_num === 3) echo '<span class="badge" style="background-color: #ffedd5; color: #ea580c; padding: 0.35rem 0.6rem; border-radius: 50%;">3</span>';
                                    else echo '<span style="padding-left: 0.5rem; font-weight: 700; color: var(--light-neutral);">' . $rank_num . '</span>';
                                ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 600;">
                                        <?php echo htmlspecialchars($rank['name']); ?>
                                        <?php if ($is_self): ?>
                                            <span class="badge active" style="font-size: 0.65rem; margin-left: 0.35rem; padding: 0.15rem 0.4rem;">You</span>
                                        <?php endif; ?>
                                    </span>
                                    <span style="font-size: 0.72rem; color: var(--light-neutral);"><?php echo htmlspecialchars($rank['email']); ?></span>
                                </div>
                            </td>

                            <td>
                                <span class="badge <?php echo strtolower($rank['status']); ?>"><?php echo $rank['status']; ?></span>
                            </td>
                            <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                RM <?php echo number_format($rank['total_collections'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

        <!-- State Rankings (Rank + State + Total only) -->
        <div class="chart-card" style="padding: 0; overflow: hidden;" id="card-perf-ind-state-rankings">
            <div class="chart-header" style="padding: 1.5rem 1.5rem 0.5rem 1.5rem; border-bottom: 1px solid var(--border-light);">
                <h3 class="chart-title"><i class="fa-solid fa-map-location-dot" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> State Rankings</h3>
            </div>
            <div class="table-wrapper">
                <table class="table-yawatim">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Rank</th>
                            <th>State</th>
                            <th style="text-align: right;">Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($state_rankings as $index => $rank): ?>
                            <?php $is_self = (($rank['state'] ?? '') === $user_state); ?>
                            <tr style="<?php echo $is_self ? 'background-color: var(--light-blue); font-weight: 600;' : ''; ?>">
                                <td>
                                    <?php
                                        $rank_num = $index + 1;
                                        if ($rank_num === 1) echo '<span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 0.35rem 0.6rem; border-radius: 50%;"><i class="fa-solid fa-crown"></i> 1</span>';
                                        elseif ($rank_num === 2) echo '<span class="badge" style="background-color: #f1f5f9; color: #64748b; padding: 0.35rem 0.6rem; border-radius: 50%;">2</span>';
                                        elseif ($rank_num === 3) echo '<span class="badge" style="background-color: #ffedd5; color: #ea580c; padding: 0.35rem 0.6rem; border-radius: 50%;">3</span>';
                                        else echo '<span style="padding-left: 0.5rem; font-weight: 700; color: var(--light-neutral);">' . $rank_num . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">
                                        <?php echo htmlspecialchars($rank['state']); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                    RM <?php echo number_format($rank['total_collected'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($role === 'wakalah_corporate'): ?>
    <!-- CORPORATE LEADERBOARDS (Global + State Rankings) -->
    <div class="charts-grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        
        <!-- Corporate Leaderboard (Global) -->
        <div class="chart-card" style="padding: 0; overflow: hidden;" id="card-perf-corporate-global">
            <div class="chart-header" style="padding: 1.5rem 1.5rem 0.5rem 1.5rem; border-bottom: 1px solid var(--border-light);">
                <h3 class="chart-title"><i class="fa-solid fa-earth-americas" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> Wakalah Corporate Leaderboard</h3>
            </div>
            <div class="table-wrapper">
                <table class="table-yawatim">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Rank</th>
                            <th>Company Name</th>
                            <th style="text-align: right;">Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($corporate_rankings as $index => $rank): ?>
                            <?php $is_self = ($rank['id'] == $wakalah_id); ?>
                            <tr style="<?php echo $is_self ? 'background-color: var(--light-blue); font-weight: 600;' : ''; ?>">
                                <td>
                                    <?php
                                        $rank_num = $index + 1;
                                        if ($rank_num === 1) echo '<span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 0.35rem 0.6rem; border-radius: 50%;">1</span>';
                                        else echo '<span style="padding-left: 0.5rem; font-weight: 700; color: var(--light-neutral);">' . $rank_num . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;">
                                            <?php echo htmlspecialchars($rank['name']); ?>
                                            <?php if ($is_self): ?>
                                                <span class="badge active" style="font-size: 0.65rem; margin-left: 0.35rem; padding: 0.15rem 0.4rem;">You</span>
                                            <?php endif; ?>
                                        </span>
                                        <span style="font-size: 0.72rem; color: var(--light-neutral);"><?php echo htmlspecialchars($rank['email']); ?></span>
                                    </div>
                                </td>

                                <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                    RM <?php echo number_format($rank['total_collections'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- State Rankings (Rank + State + Total only) -->
        <div class="chart-card" style="padding: 0; overflow: hidden;" id="card-perf-state-rankings">
            <div class="chart-header" style="padding: 1.5rem 1.5rem 0.5rem 1.5rem; border-bottom: 1px solid var(--border-light);">
                <h3 class="chart-title"><i class="fa-solid fa-map-location-dot" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> State Rankings</h3>
            </div>
            <div class="table-wrapper">
                <table class="table-yawatim">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Rank</th>
                            <th>State</th>
                            <th style="text-align: right;">Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($state_rankings as $index => $rank): ?>
                            <?php $is_self = (($rank['state'] ?? '') === $user_state); ?>
                            <tr style="<?php echo $is_self ? 'background-color: var(--light-blue); font-weight: 600;' : ''; ?>">
                                <td>
                                    <?php
                                        $rank_num = $index + 1;
                                        if ($rank_num === 1) echo '<span class="badge" style="background-color: #fef3c7; color: #d97706; padding: 0.35rem 0.6rem; border-radius: 50%;"><i class="fa-solid fa-crown"></i> 1</span>';
                                        elseif ($rank_num === 2) echo '<span class="badge" style="background-color: #f1f5f9; color: #64748b; padding: 0.35rem 0.6rem; border-radius: 50%;">2</span>';
                                        elseif ($rank_num === 3) echo '<span class="badge" style="background-color: #ffedd5; color: #ea580c; padding: 0.35rem 0.6rem; border-radius: 50%;">3</span>';
                                        else echo '<span style="padding-left: 0.5rem; font-weight: 700; color: var(--light-neutral);">' . $rank_num . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">
                                        <?php echo htmlspecialchars($rank['state']); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: var(--success-green); text-align: right;">
                                    RM <?php echo number_format($rank['total_collected'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>