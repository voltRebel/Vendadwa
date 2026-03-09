<?php
require_once 'includes/db.php';
session_start();
$user_id = $_SESSION['user_id'] ?? 1;
$company_id = $_SESSION['company_id'] ?? 1;

$logs = [
    ['profile_update', "updated their profile and avatar", $user_id, $company_id],
    ['backup_created', "created a new system backup", $user_id, $company_id],
    ['stock_update', "updated stock for 'Silk Blouse'", $user_id, $company_id],
    ['sale_created', "processed a new sale REC-20260309-001", $user_id, $company_id],
    ['login', "logged into the Alsoft Solutions POS", $user_id, $company_id]
];

try {
    $stmt = $pdo->prepare("INSERT INTO activity_logs (action_type, details, user_id, company_id, created_at) VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? MINUTE))");
    foreach ($logs as $i => $log) {
        $stmt->execute([$log[0], $log[1], $log[2], $log[3], ($i + 1) * 15]);
    }
    echo "Activity logs seeded.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
