<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/yawatim-main/yawatim-main/api.php');
curl_setopt($ch, CURLOPT_POST, 1);
$post = [
    'action' => 'add_donation',
    'amount' => '10',
    'attachment_image' => new CURLFile('c:/xampp/htdocs/yawatim-main/yawatim-main/img/logoyawatim.png')
];
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo 'Response: ' . $response;
