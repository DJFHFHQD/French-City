<?php
require_once 'db_connect.php';

$stmt = $pdo->query("SELECT * FROM server_settings LIMIT 1");
$server_settings = $stmt->fetch(PDO::FETCH_ASSOC);

$apiKey = $server_settings['api_key'];
$serverIp = $server_settings['server_ip'];
$serverPort = $server_settings['server_port'];
$appId = $server_settings['app_id'];
?>