<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
$db = getDB();
$colleges = $db->query("SELECT * FROM colleges ORDER BY name")->fetchAll();
echo json_encode(['colleges' => $colleges]);
