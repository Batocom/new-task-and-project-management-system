<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $oldManagerId = $_POST['old_manager_id'];
    $newManagerId = $_POST['new_manager_id'];
    $newDepartmentName = $_POST['new_department_name'];

    if (isset($_SESSION['old_department_id']) && !empty($_SESSION['old_department_id'])) {
        $oldDepartmentId = $_SESSION['old_department_id'];

        // Update the old department with the new manager
        $conn->query("UPDATE departments SET manager_id = $newManagerId WHERE id = $oldDepartmentId");

        // Create the new department
        $conn->query("INSERT INTO departments (name, manager_id) VALUES ('$newDepartmentName', $oldManagerId)");

        // Clear session variables
        unset($_SESSION['old_department_id']);
        unset($_SESSION['old_manager_id']);
        unset($_SESSION['new_department_name']);

        echo '<script>alert("Old department\'s project manager updated and new department created successfully.");</script>';
    } else {
        echo "Error: Old department ID is not set.";
    }
}
?>
