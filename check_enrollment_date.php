<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=enrollsys', 'root', '');
    $stmt = $db->query("DESCRIBE enrollment_date");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
