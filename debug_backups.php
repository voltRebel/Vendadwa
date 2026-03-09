require_once 'includes/db.php';
try {
    $stmt = $pdo->query("SELECT * FROM backups ORDER BY created_at DESC");
    $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total Backups in DB: " . count($backups) . "\n";
    foreach ($backups as $b) {
        echo "- ID: {$b['id']}, Company: {$b['company_id']}, Filename: {$b['filename']}, Created: {$b['created_at']}\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
