<?php
// modules/my_booth.php - My Booth View for Wakalah Partners
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce wakalah access only
$role = $_SESSION['role'] ?? '';
if ($role !== 'wakalah_individual' && $role !== 'wakalah_corporate') {
    header("Location: index.php?page=dashboard");
    exit;
}

$wakalah_id = $_SESSION['wakalah_id'] ?? null;
$user_channel = $_SESSION['channel'] ?? null;
$booths = [];

$malaysian_states = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis',
    'Penang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'WP Kuala Lumpur', 'WP Putrajaya', 'WP Labuan'
];
$channels = ['BSN', 'Bank Rakyat', 'Pos Malaysia', 'EBB'];

try {
    if ($wakalah_id) {
        // Fetch booths linked to this wakalah user's channel
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                b.id,
                b.name,
                b.location,
                b.state,
                b.channel,
                b.status,
                b.created_at
            FROM booths b
            INNER JOIN donations d ON d.booth_id = b.id
            WHERE d.wakalah_id = ? AND b.channel = ?
            ORDER BY b.name ASC
        ");
        $stmt->execute([$wakalah_id, $user_channel]);
        $booths = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    echo "<div style='color: var(--alert-red); font-weight: bold;'>Query Error: " . $e->getMessage() . "</div>";
}
?>

<!-- Page Intro Card -->
<div class="chart-card" style="padding: 1.5rem 1.75rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1.25rem; background: linear-gradient(135deg, var(--primary-blue) 0%, #1e40af 100%); color: #fff;" id="my-booth-intro-card">
    <div style="background: rgba(255,255,255,0.15); border-radius: 14px; padding: 1rem 1.2rem; flex-shrink: 0;">
        <i class="fa-solid fa-store" style="font-size: 2rem; color: #fff;"></i>
    </div>
    <div>
        <h2 style="font-size: 1.2rem; font-weight: 800; margin: 0 0 0.25rem 0; color: #fff;">My Collection Booths</h2>
        <p style="font-size: 0.85rem; margin: 0; color: rgba(255,255,255,0.78);">
            Manage and view donation collection booths linked to your Wakalah account.
        </p>
    </div>
    <div style="margin-left: auto; text-align: right; flex-shrink: 0;">
        <span id="booth-count-display" style="font-size: 2rem; font-weight: 800; color: #fff; line-height: 1;"><?php echo count($booths); ?></span>
        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 0.1rem;" id="booth-count-label">Booth<?php echo count($booths) !== 1 ? 's' : ''; ?> Found</div>
    </div>
</div>

<!-- Add New Booth Form Card -->
<div class="chart-card" style="padding: 0; overflow: hidden; margin-bottom: 1.5rem;" id="add-booth-form-card">
    <div class="chart-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; cursor: pointer;" id="add-booth-toggle-header">
        <h3 class="chart-title" style="margin: 0;">
            <i class="fa-solid fa-circle-plus" style="color: var(--primary-blue); margin-right: 0.5rem;"></i>
            Add New Booth
        </h3>
        <i class="fa-solid fa-chevron-down" id="add-booth-toggle-icon" style="color: var(--light-neutral); transition: transform 0.25s ease; font-size: 0.85rem;"></i>
    </div>

    <div id="add-booth-form-body" style="display: none;">
        <form id="form-add-booth" style="padding: 1.5rem;">
            <input type="hidden" name="action" value="add_booth">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="booth_name">
                        <i class="fa-solid fa-store" style="margin-right: 0.3rem; color: var(--primary-blue);"></i>
                        Booth Name
                    </label>
                    <input type="text" name="name" id="booth_name" class="form-input"
                           placeholder="e.g. BSN Mid Valley" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="booth_location">
                        <i class="fa-solid fa-location-dot" style="margin-right: 0.3rem; color: var(--primary-blue);"></i>
                        Location / Address
                    </label>
                    <input type="text" name="location" id="booth_location" class="form-input"
                           placeholder="e.g. Mid Valley Megamall, KL" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-top: 0.25rem;">
                <div class="form-group">
                    <label class="form-label" for="booth_state">
                        <i class="fa-solid fa-map-location-dot" style="margin-right: 0.3rem; color: var(--primary-blue);"></i>
                        State
                    </label>
                    <select name="state" id="booth_state" class="form-select" required>
                        <option value="">-- Select State --</option>
                        <?php foreach ($malaysian_states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="booth_channel">
                        <i class="fa-solid fa-building-columns" style="margin-right: 0.3rem; color: var(--primary-blue);"></i>
                        Channel
                    </label>
                    <select name="channel" id="booth_channel" class="form-select" required>
                        <option value="">-- Select Channel --</option>
                        <?php foreach ($channels as $ch): ?>
                            <option value="<?php echo htmlspecialchars($ch); ?>"
                                <?php echo ($user_channel === $ch) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ch); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($user_channel): ?>
                        <input type="hidden" name="channel" value="<?php echo htmlspecialchars($user_channel); ?>">
                        <p style="font-size: 0.75rem; color: var(--light-neutral); margin-top: 0.3rem;">
                            <i class="fa-solid fa-lock" style="margin-right: 0.25rem;"></i>
                            Locked to your channel: <strong><?php echo htmlspecialchars($user_channel); ?></strong>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="booth_status">
                        <i class="fa-solid fa-toggle-on" style="margin-right: 0.3rem; color: var(--primary-blue);"></i>
                        Status
                    </label>
                    <select name="status" id="booth_status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 1.25rem; gap: 0.75rem;">
                <button type="reset" class="btn btn-outline" id="btn-reset-booth-form">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary" id="btn-add-booth-submit">
                    <i class="fa-solid fa-plus-circle"></i> Add Booth
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Booths Table Card -->
<div class="table-card" id="my-booth-table-card">
    <div class="chart-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between;">
        <h3 class="chart-title" style="margin: 0;">
            <i class="fa-solid fa-store" style="color: var(--primary-blue); margin-right: 0.5rem;"></i>
            Assigned Booth Locations
        </h3>
        <span class="badge active" id="booth-table-count-badge" style="font-size: 0.7rem; padding: 0.3rem 0.75rem; <?php echo count($booths) === 0 ? 'display:none;' : ''; ?>">
            <span id="booth-table-count-num"><?php echo count($booths); ?></span> Booth<?php echo count($booths) !== 1 ? 's' : ''; ?>
        </span>
    </div>

    <div class="table-wrapper">
        <table class="table-yawatim" id="my-booth-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Booth Name</th>
                    <th>Location</th>
                    <th>State</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody id="my-booth-tbody">
                <?php if (count($booths) === 0): ?>
                    <tr id="booth-empty-row">
                        <td colspan="6">
                            <div style="text-align: center; padding: 3rem 1rem;">
                                <div style="background: var(--light-blue); border-radius: 50%; width: 72px; height: 72px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto;">
                                    <i class="fa-solid fa-store-slash" style="font-size: 2rem; color: var(--primary-blue);"></i>
                                </div>
                                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--dark-neutral); margin: 0 0 0.5rem 0;">No Booths Found</h3>
                                <p style="color: var(--light-neutral); font-size: 0.875rem; margin: 0 auto; max-width: 380px;">
                                    You have not been associated with any donation collection booths yet.
                                    Use the form above to add your first booth.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($booths as $index => $booth): ?>
                        <tr id="booth-row-<?php echo $booth['id']; ?>">
                            <td style="color: var(--light-neutral); font-weight: 600; font-size: 0.85rem;">
                                <?php echo $index + 1; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <div style="background: var(--light-blue); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa-solid fa-store" style="font-size: 0.8rem; color: var(--primary-blue);"></i>
                                    </div>
                                    <span style="font-weight: 700; color: var(--dark-neutral);">
                                        <?php echo htmlspecialchars($booth['name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td style="color: var(--medium-neutral);">
                                <?php echo htmlspecialchars($booth['location']); ?>
                            </td>
                            <td>
                                <span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">
                                    <?php echo htmlspecialchars($booth['state']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (strtolower($booth['status']) === 'active'): ?>
                                    <span class="badge active">Active</span>
                                <?php else: ?>
                                    <span class="badge inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--medium-neutral);">
                                <?php echo date('d M Y', strtotime($booth['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // ----- Collapsible form toggle -----
    const toggleHeader = document.getElementById('add-booth-toggle-header');
    const formBody     = document.getElementById('add-booth-form-body');
    const toggleIcon   = document.getElementById('add-booth-toggle-icon');

    toggleHeader.addEventListener('click', () => {
        const isOpen = formBody.style.display !== 'none';
        formBody.style.display = isOpen ? 'none' : 'block';
        toggleIcon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    });

    // ----- Track booth count globally -----
    let boothCount = <?php echo count($booths); ?>;

    // Helper: build a table row HTML string for a new booth
    function buildBoothRow(booth, rowIndex) {
        const statusBadge = booth.status === 'Active'
            ? `<span class="badge active">Active</span>`
            : `<span class="badge inactive">Inactive</span>`;

        const stateBadge = `<span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">${escapeHtml(booth.state)}</span>`;

        return `
            <tr id="booth-row-${booth.id}">
                <td style="color: var(--light-neutral); font-weight: 600; font-size: 0.85rem;">${rowIndex}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <div style="background: var(--light-blue); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-store" style="font-size: 0.8rem; color: var(--primary-blue);"></i>
                        </div>
                        <span style="font-weight: 700; color: var(--dark-neutral);">${escapeHtml(booth.name)}</span>
                    </div>
                </td>
                <td style="color: var(--medium-neutral);">${escapeHtml(booth.location)}</td>
                <td>${stateBadge}</td>
                <td>${statusBadge}</td>
                <td style="font-size: 0.82rem; color: var(--medium-neutral);">${escapeHtml(booth.created_at_formatted)}</td>
            </tr>`;
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    function updateBoothCountUI(count) {
        // Update intro card counter
        const countEl  = document.getElementById('booth-count-display');
        const labelEl  = document.getElementById('booth-count-label');
        const badgeEl  = document.getElementById('booth-table-count-badge');
        const numEl    = document.getElementById('booth-table-count-num');
        if (countEl) countEl.textContent = count;
        if (labelEl) labelEl.textContent = count !== 1 ? 'Booths Found' : 'Booth Found';
        if (badgeEl) {
            badgeEl.style.display = count > 0 ? '' : 'none';
            badgeEl.innerHTML = `<span id="booth-table-count-num">${count}</span> Booth${count !== 1 ? 's' : ''}`;
        }
    }

    // ----- Add Booth form AJAX submit -----
    const form = document.getElementById('form-add-booth');
    const submitBtn = document.getElementById('btn-add-booth-submit');

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const payload = {};
        formData.forEach((val, key) => { payload[key] = val; });

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';

        fetch('api.php?action=add_booth', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showToast(res.message, 'success');

                // Remove empty-state row if present
                const emptyRow = document.getElementById('booth-empty-row');
                if (emptyRow) emptyRow.remove();

                boothCount++;
                updateBoothCountUI(boothCount);

                // Inject new row into table
                const tbody = document.getElementById('my-booth-tbody');
                tbody.insertAdjacentHTML('beforeend', buildBoothRow(res.booth, boothCount));

                // Highlight new row briefly
                const newRow = document.getElementById(`booth-row-${res.booth.id}`);
                if (newRow) {
                    newRow.style.transition = 'background-color 0.6s ease';
                    newRow.style.backgroundColor = 'var(--light-blue)';
                    setTimeout(() => { newRow.style.backgroundColor = ''; }, 1800);
                }

                // Reset form and collapse it
                form.reset();
                formBody.style.display = 'none';
                toggleIcon.style.transform = 'rotate(0deg)';
            } else {
                showToast(res.message || 'Failed to add booth.', 'error');
            }
        })
        .catch(() => showToast('Server error. Please try again.', 'error'))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-plus-circle"></i> Add Booth';
        });
    });
});
</script>
