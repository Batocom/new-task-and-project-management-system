<?php
require 'db.php'; // include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $manager_id = $_POST['manager_id'];
    
    // Check if the manager is heading another department
    $current_department = $conn->query("SELECT id FROM departments WHERE manager_id = $manager_id")->fetch_assoc();
    if ($current_department) {
        echo json_encode(['isHeading' => true]);
    } else {
        echo json_encode(['isHeading' => false]);
    }
}
?>
