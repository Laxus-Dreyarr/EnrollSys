<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=enrollsys', 'root', '');
    
    // Get all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "=== TABLES ===\n";
    print_r($tables);
    
    // Check curriculum table
    if (in_array('curriculum', $tables)) {
        $stmt = $db->query('SELECT * FROM curriculum LIMIT 2');
        echo "\n=== SAMPLE CURRICULUM ===\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Check users table
    if (in_array('users', $tables)) {
        $stmt = $db->query('DESCRIBE users');
        echo "\n=== USERS TABLE COLUMNS ===\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        
        $stmt = $db->query('SELECT * FROM users LIMIT 2');
        echo "\n=== SAMPLE USER ===\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    // Check csv table
    if (in_array('csv', $tables)) {
        $stmt = $db->query('DESCRIBE csv');
        echo "\n=== CSV TABLE COLUMNS ===\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        
        $stmt = $db->query('SELECT * FROM csv LIMIT 2');
        echo "\n=== SAMPLE CSV RECORD ===\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
