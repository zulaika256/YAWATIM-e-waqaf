<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'wakalah_individual';
$email = $_SESSION['email'] ?? '';
$name = $_SESSION['name'] ?? 'Partner Name';
$active_page = $_GET['page'] ?? 'dashboard';
$current_channel = $_SESSION['channel'] ?? 'Admin';
?>
<aside class="sidebar" id="app-sidebar">
    <div class="sidebar-brand">
        <img src="img/logoyawatim.png" alt="YAWATIM Logo" style="height: 38px; width: auto; object-fit: contain; border-radius: 6px; background-color: var(--white); padding: 2px; box-shadow: var(--shadow-sm);">
        <div class="sidebar-title">YAWATIM</div>
    </div>

    <div class="sidebar-state-info">
        <span class="sidebar-state-label">
            <?php
                if ($role === 'admin') echo 'System';
                elseif ($role === 'wakalah_individual') echo 'Wakalah Individual';
                else echo 'Wakalah Corporate';
            ?>
        </span>
        <span class="sidebar-state-value"><?php echo htmlspecialchars($name ?: 'YAWATIM'); ?></span>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-menu-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
            <a href="index.php?page=dashboard" class="sidebar-link" id="nav-dashboard">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if ($role === 'admin'): ?>
            <li class="sidebar-menu-item <?php echo $active_page === 'wakalah_individual' ? 'active' : ''; ?>">
                <a href="index.php?page=wakalah_individual" class="sidebar-link" id="nav-wakalah-individual">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Wakalah Individuals</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $active_page === 'wakalah_corporate' ? 'active' : ''; ?>">
                <a href="index.php?page=wakalah_corporate" class="sidebar-link" id="nav-wakalah-corporate">
                    <i class="fa-solid fa-building-user"></i>
                    <span>Wakalah Corporates</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $active_page === 'donations' ? 'active' : ''; ?>">
                <a href="index.php?page=donations" class="sidebar-link" id="nav-donations">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Donations</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $active_page === 'performance' ? 'active' : ''; ?>">
                <a href="index.php?page=performance" class="sidebar-link" id="nav-performance">
                    <i class="fa-solid fa-ranking-star"></i>
                    <span>Performance</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $active_page === 'reports' ? 'active' : ''; ?>">
                <a href="index.php?page=reports" class="sidebar-link" id="nav-reports">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Reports</span>
                </a>
            </li>
        <?php else: ?>
            <li class="sidebar-menu-item <?php echo $active_page === 'donations' ? 'active' : ''; ?>">
                <a href="index.php?page=donations" class="sidebar-link" id="nav-partner-donations">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                    <span>My Donation Log</span>
                </a>
            </li>

            <?php if ($role === 'wakalah_corporate'): ?>
            <li class="sidebar-menu-item <?php echo $active_page === 'my_booth' ? 'active' : ''; ?>">
                <a href="index.php?page=my_booth" class="sidebar-link" id="nav-partner-my-booth">
                    <i class="fa-solid fa-store"></i>
                    <span>My Booth</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="sidebar-menu-item <?php echo $active_page === 'performance' ? 'active' : ''; ?>">
                <a href="index.php?page=performance" class="sidebar-link" id="nav-partner-performance">
                    <i class="fa-solid fa-award"></i>
                    <span>My Statistics</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info-brief">
            <span class="user-info-name" title="<?php echo htmlspecialchars($name); ?>">
                <?php echo htmlspecialchars($name); ?>
            </span>
            <span class="user-info-role">
                <?php
                    if ($role === 'admin') echo 'Administrator';
                    elseif ($role === 'wakalah_individual') echo 'Individual Partner';
                    else echo 'Corporate Partner';
                ?>
            </span>
            <span style="font-size: 0.65rem; color: rgba(255, 255, 255, 0.4); text-transform: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($email); ?>">
                <?php echo htmlspecialchars($email); ?>
            </span>
        </div>
    </div>
</aside>
