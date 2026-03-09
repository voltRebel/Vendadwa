<?php
require_once 'includes/db.php';

try {
    $sql = file_get_contents('database/migrations/create_backups_table.sql');
    $pdo->exec($sql);
    echo "Migration successful: backups table created.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
