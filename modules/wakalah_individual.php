<?php
// modules/wakalah_individual.php - Admin Wakalah Individual Management View
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=dashboard");
    exit;
}

try {
    // Fetch all individual wakalah partners
    $stmt = $pdo->query("SELECT * FROM wakalah WHERE type = 'individual' ORDER BY name ASC");
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
                <?php if (count($wakalah_list) === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--light-neutral); padding: 2rem;">No individual partner records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($wakalah_list as $wak): ?>
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
                                            data-address="<?php echo htmlspecialchars($wak['address'] ?? ''); ?>"
                                            title="Edit Individual Details">
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

<!-- MODAL: ADD INDIVIDUAL WAKALAH -->
<div class="modal-overlay" id="modal-add-wakalah-individual">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Register Individual Partner</h3>
            <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-add-wak-ind">
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
        <form id="form-edit-wak-ind">
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
document.addEventListener('DOMContentLoaded', () => {
    // 1. Hook up the AJAX submits
    handleFormSubmit('form-add-wak-ind', 'Wakalah Individual registered successfully!');
    handleFormSubmit('form-edit-wak-ind', 'Wakalah Individual details updated successfully!');

    // 2. Open edit staff modal & prepopulate values
    document.querySelectorAll('.btn-edit-wak-ind').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_email').value = btn.getAttribute('data-email');
            document.getElementById('edit_phone').value = btn.getAttribute('data-phone');
            document.getElementById('edit_status').value = btn.getAttribute('data-status');
            document.getElementById('edit_ic').value = btn.getAttribute('data-ic');
            document.getElementById('edit_ind_address').value = btn.getAttribute('data-address') || '';
            
            openModal('modal-edit-wakalah-individual');
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
                        const row = document.getElementById(`wak-ind-row-${id}`);
                        if (row) {
                            const badge = row.querySelector('.wak-status-cell .badge');
                            const editBtn = row.querySelector('.btn-edit-wak-ind');
                            
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
