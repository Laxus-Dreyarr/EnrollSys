<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=enrollsys", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "CONNECTED SUCCESSFULY\n";
    
    // Check payments schema
    $q = $pdo->query("DESCRIBE payments");
    echo "\nPAYMENTS TABLE COLUMNS:\n";
    while ($row = $q->fetch()) {
        echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    // Check students schema
    $q = $pdo->query("DESCRIBE students");
    echo "\nSTUDENTS TABLE COLUMNS:\n";
    while ($row = $q->fetch()) {
        echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    // Get sample payments and their types / statuses
    echo "\nSAMPLE PAYMENTS DATA:\n";
    $q = $pdo->query("SELECT * FROM payments LIMIT 5");
    while ($row = $q->fetch()) {
        print_r($row);
    }
    
    // Get sample students and their statuses
    echo "\nSAMPLE STUDENTS DATA:\n";
    $q = $pdo->query("SELECT id, id_no, status, student_id FROM students LIMIT 5");
    while ($row = $q->fetch()) {
        print_r($row);
    }
    
    // Let's count how many students have payments or organization fees
    echo "\nSTUDENT PAYMENT STATS:\n";
    $q = $pdo->query("SELECT student_id, COUNT(*) as cnt, GROUP_CONCAT(status) as statuses, GROUP_CONCAT(type) as types FROM payments GROUP BY student_id LIMIT 10");
    while ($row = $q->fetch()) {
        print_r($row);
    }
    
    // Let's count how many students have organization fees
    echo "\nORGANIZATION FEES DISTINCT STATUSES:\n";
    $q = $pdo->query("SELECT DISTINCT status FROM organizationfees");
    while ($row = $q->fetch()) {
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
