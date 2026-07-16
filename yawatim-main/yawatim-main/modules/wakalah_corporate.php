<?php
// modules/wakalah_corporate.php - Admin Wakalah Corporate Management View
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=dashboard");
    exit;
}

try {
    // Fetch all corporate wakalah partners
    $stmt = $pdo->query("SELECT * FROM wakalah WHERE type = 'corporate' ORDER BY name ASC");
    $wakalah_list = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div style='color: var(--alert-red); font-weight: bold;'>Query Error: " . $e->getMessage() . "</div>";
}

$malaysian_states = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 
    'Penang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'WP Kuala Lumpur', 'WP Putrajaya', 'WP Labuan'
];
?>

<!-- Table Controls Header -->
<div class="table-controls-container" id="wakalah-corporate-controls">
    <div class="search-filter-group">
        <input type="text" class="input-search" placeholder="Search company name, SSM, email..." data-search-target="true" id="wak-corp-search-input">
        
        <select class="select-filter" data-filter-column="5" id="wak-corp-filter-state">
            <option value="">All States</option>
            <?php foreach ($malaysian_states as $state): ?>
                <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
            <?php endforeach; ?>
        </select>
        
        <select class="select-filter" data-filter-column="6" id="wak-corp-filter-status">
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>
    
    <button class="btn btn-primary" data-modal-open="modal-add-wakalah-corporate" id="btn-add-wakalah-corporate-modal">
        <i class="fa-solid fa-building-circle-plus"></i> Register Corporate Partner
    </button>
</div>

<!-- Table Card -->
<div class="table-card" id="wakalah-corporate-table-card">
    <div class="table-wrapper">
        <table class="table-yawatim" id="wakalah-corporate-table">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Branch Name</th>
                    <th>SSM Number</th>
                    <th>Representative</th>
                    <th>HQ Address</th>
                    <th>Email Address</th>
                    <th>Phone Number</th>
                    <th>Assigned State</th>
                    <th>Status</th>
                    <th class="actions-td">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($wakalah_list) === 0): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--light-neutral); padding: 2rem;">No corporate partner records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($wakalah_list as $wak): ?>
                        <tr id="wak-corp-row-<?php echo $wak['id']; ?>">
                            <td style="font-weight: 700; color: var(--primary-blue);" class="wak-name-cell"><?php echo htmlspecialchars($wak['name']); ?></td>
                            <td class="wak-branch-cell"><?php echo htmlspecialchars($wak['branch_name'] ?? ''); ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($wak['ssm_number']); ?></td>
                            <td><?php echo htmlspecialchars($wak['company_representative']); ?></td>
                            <td style="font-size: 0.8rem; color: var(--medium-neutral);"><?php echo htmlspecialchars($wak['hq_address']); ?></td>
                            <td><?php echo htmlspecialchars($wak['email']); ?></td>
                            <td><?php echo htmlspecialchars($wak['phone']); ?></td>
                            <td>
                                <span class="badge" style="background-color: var(--light-blue); color: var(--primary-blue); font-weight: 600;">
                                    <?php echo htmlspecialchars($wak['state']); ?>
                                </span>
                            </td>
                            <td class="wak-status-cell">
                                <span class="badge <?php echo strtolower($wak['status']); ?>"><?php echo $wak['status']; ?></span>
                            </td>
                            <td class="actions-td">
                                <div class="action-group">
                                    <button class="action-btn edit btn-edit-wak-corp" 
                                            data-id="<?php echo $wak['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($wak['name']); ?>"
                                            data-branch="<?php echo htmlspecialchars($wak['branch_name'] ?? ''); ?>"
                                            data-email="<?php echo htmlspecialchars($wak['email']); ?>"
                                            data-phone="<?php echo htmlspecialchars($wak['phone']); ?>"
                                            data-state="<?php echo htmlspecialchars($wak['state']); ?>"
                                            data-status="<?php echo htmlspecialchars($wak['status']); ?>"
                                            data-ssm="<?php echo htmlspecialchars($wak['ssm_number']); ?>"
                                            data-rep="<?php echo htmlspecialchars($wak['company_representative']); ?>"
                                            data-addr="<?php echo htmlspecialchars($wak['hq_address']); ?>"
                                            data-address="<?php echo htmlspecialchars($wak['address'] ?? ''); ?>"
                                            title="Edit Corporate Details">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <button class="action-btn btn-toggle-status" 
                                            data-id="<?php echo $wak['id']; ?>"
                                            title="Toggle Status (Active/Inactive)">
                                        <i class="fa-solid fa-power-off"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: ADD CORPORATE WAKALAH -->
<div class="modal-overlay" id="modal-add-wakalah-corporate">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Register Corporate Partner</h3>
            <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-add-wak-corp">
            <input type="hidden" name="action" value="add_wakalah_corporate">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="add_company_name">Company Name</label>
                        <input type="text" name="company_name" id="add_company_name" class="form-input" placeholder="e.g. Syarikat Prihatin Sdn Bhd" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="add_branch_name">Branch Name</label>
                        <input type="text" name="branch_name" id="add_branch_name" class="form-input" placeholder="e.g. Mid Valley Branch" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="add_corp_rep">Company Representative</label>
                        <input type="text" name="company_representative" id="add_corp_rep" class="form-input" placeholder="e.g. Mr. Tan Wei Beng" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="add_ssm">SSM Registration Number</label>
                        <input type="text" name="ssm_number" id="add_ssm" class="form-input" placeholder="e.g. 1234567-X" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_address">HQ Registered Address</label>
                    <input type="text" name="hq_address" id="add_address" class="form-input" placeholder="e.g. No. 1, Jalan Tebrau, JB" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="add_corp_email">Company Email Address</label>
                        <input type="email" name="company_email" id="add_corp_email" class="form-input" placeholder="e.g. finance@corp.com.my" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="add_corp_phone">Company Phone Number</label>
                        <input type="text" name="company_phone" id="add_corp_phone" class="form-input" placeholder="e.g. 03-5558888" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_corp_state">State Branch Office</label>
                    <select name="state" id="add_corp_state" class="form-select" required>
                        <option value="">-- Select State --</option>
                        <?php foreach ($malaysian_states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_status">Account Status</label>
                    <select name="status" id="add_status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add_corp_address">Office / Mailing Address <span style="font-size: 0.75rem; color: var(--light-neutral);">(Optional)</span></label>
                    <textarea name="address" id="add_corp_address" class="form-input" rows="2" placeholder="e.g. Level 5, Wisma ABC, No. 10 Jalan Utama, 50000 Kuala Lumpur" style="resize: vertical;"></textarea>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close="modal-add-wakalah-corporate">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Partner</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT CORPORATE WAKALAH -->
<div class="modal-overlay" id="modal-edit-wakalah-corporate">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Edit Corporate Details</h3>
            <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-edit-wak-corp">
            <input type="hidden" name="action" value="edit_wakalah_corporate">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_company_name">Company Name</label>
                        <input type="text" name="company_name" id="edit_company_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_branch_name">Branch Name</label>
                        <input type="text" name="branch_name" id="edit_branch_name" class="form-input" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_corp_rep">Company Representative</label>
                        <input type="text" name="company_representative" id="edit_corp_rep" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_ssm">SSM Registration Number</label>
                        <input type="text" name="ssm_number" id="edit_ssm" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_address">HQ Registered Address</label>
                    <input type="text" name="hq_address" id="edit_address" class="form-input" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="edit_corp_email">Company Email Address</label>
                        <input type="email" name="company_email" id="edit_corp_email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_corp_phone">Company Phone Number</label>
                        <input type="text" name="company_phone" id="edit_corp_phone" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_state">State Branch Office</label>
                    <select name="state" id="edit_state" class="form-select" required>
                        <?php foreach ($malaysian_states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_status">Account Status</label>
                    <select name="status" id="edit_status" class="form-select" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit_corp_address">Office / Mailing Address <span style="font-size: 0.75rem; color: var(--light-neutral);">(Optional)</span></label>
                    <textarea name="address" id="edit_corp_address" class="form-input" rows="2" style="resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-modal-close="modal-edit-wakalah-corporate">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Details</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Hook up the AJAX submits
    handleFormSubmit('form-add-wak-corp', 'Wakalah Corporate registered successfully!');
    handleFormSubmit('form-edit-wak-corp', 'Wakalah Corporate details updated successfully!');

    // 2. Open edit modal & prepopulate values
    document.querySelectorAll('.btn-edit-wak-corp').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_company_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_branch_name').value = btn.getAttribute('data-branch') || '';
            document.getElementById('edit_corp_email').value = btn.getAttribute('data-email');
            document.getElementById('edit_corp_phone').value = btn.getAttribute('data-phone');
            document.getElementById('edit_state').value = btn.getAttribute('data-state');
            document.getElementById('edit_status').value = btn.getAttribute('data-status');
            document.getElementById('edit_ssm').value = btn.getAttribute('data-ssm');
            document.getElementById('edit_corp_rep').value = btn.getAttribute('data-rep');
            document.getElementById('edit_address').value = btn.getAttribute('data-addr');
            document.getElementById('edit_corp_address').value = btn.getAttribute('data-address') || '';
            
            openModal('modal-edit-wakalah-corporate');
        });
    });

    // 3. Toggle active status via AJAX
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            
            if (confirm('Are you sure you want to toggle this Wakalah account status?')) {
                fetch('api.php?action=toggle_wakalah_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        showToast(res.message, 'success');
                        
                        // Update UI status badge instantly
                        const row = document.getElementById(`wak-corp-row-${id}`);
                        if (row) {
                            const badge = row.querySelector('.wak-status-cell .badge');
                            const editBtn = row.querySelector('.btn-edit-wak-corp');
                            
                            badge.className = `badge ${res.status.toLowerCase()}`;
                            badge.innerText = res.status;
                            
                            if (editBtn) {
                                editBtn.setAttribute('data-status', res.status);
                            }
                        }
                    } else {
                        showToast(res.message, 'error');
                    }
                })
                .catch(err => {
                    showToast('Failed to contact server.', 'error');
                });
            }
        });
    });
});
</script>