<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT name FROM companies LIMIT 1");
$company = $stmt->fetch();
echo "Current Company Name: " . ($company['name'] ?? 'Not Found');
?>
