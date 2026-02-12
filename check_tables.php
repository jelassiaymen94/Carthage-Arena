<?php
$dsn = 'mysql:host=mysql-carthage-arena.alwaysdata.net;dbname=carthage-arena_main;port=3306';
$user = 'carthage-arena';
$pass = 'Carthage1122';

try {
    $dbh = new PDO($dsn, $user, $pass);
    echo "Connection successful!\n";
    $stmt = $dbh->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found.\n";
    } else {
        echo "Tables found:\n";
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
