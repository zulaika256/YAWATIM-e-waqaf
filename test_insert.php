<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'c:/xampp/htdocs/yawatim-main/yawatim-main/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO donations (donor_name, donor_phone, donor_email, amount, donation_date, donation_month, channel, state, location, wakalah_id, booth_id, attachment_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test', '123', 'test@test.com', 10, '2026-07-25', 'July', 'BSN', 'Selangor', '-', 1, null, null]);
    echo "Success";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
