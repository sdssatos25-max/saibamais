<?php
$db = new PDO('sqlite:' . __DIR__ . '/cloaker.db');
$metricsStmt = $db->query("SELECT * FROM metrics");
$metrics = $metricsStmt->fetch(PDO::FETCH_ASSOC);
$metrics['sources'] = json_decode($metrics['sources'], true) ?? [];
header('Content-Type: application/json');
echo json_encode($metrics);
?>