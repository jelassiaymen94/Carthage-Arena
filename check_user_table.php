<?php
$dsn = 'mysql:host=mysql-carthage-arena.alwaysdata.net;dbname=carthage-arena_main;port=3306';
$user = 'carthage-arena';
$pass = 'Carthage1122';

try {
    $dbh = new PDO($dsn, $user, $pass);
    echo "Connection successful!\n";
    $stmt = $dbh->query("DESCRIBE user");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('license_id', $columns)) {
        echo "license_id column exists in user table.\n";
    } else {
        echo "license_id column DOES NOT exist in user table.\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
