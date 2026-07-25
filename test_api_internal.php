<?php
error_reporting(E_ALL);
session_start();
// set session to admin
$_SESSION['user_id'] = 25;
$_SESSION['role'] = 'admin';
$_SESSION['channel'] = null;
require_once 'c:/xampp/htdocs/yawatim-main/yawatim-main/db.php';

$_POST = [
    'action' => 'add_donation',
    'amount' => '10',
    'donor_name' => 'John Doe',
    'state' => '-',
    'booth_id' => '-'
];
$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';

ob_start();
include 'c:/xampp/htdocs/yawatim-main/yawatim-main/api.php';
$out = ob_get_clean();
echo "API OUTPUT: " . $out;
