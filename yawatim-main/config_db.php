<?php
require_once __DIR__ . '/database.php';
try {
    $q = $pdo->query("DESCRIBE donations");
    echo "Columns in donations:\n";
    while ($row = $q->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error describing donations: " . $e->getMessage() . "\n";
}
?>
