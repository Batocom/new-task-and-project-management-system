<?php
require 'db.php'; //  database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_name = $_POST['department_name'];
    $manager_id = $_POST['manager_id'];
    $replacement_manager_id = $_POST['replacement_manager_id'];

    // Validate inputs
    if (empty($department_name) || empty($manager_id)) {
        die("Department name and manager must be selected.");
    }

    // Fetch the selected manager's details
    $manager = $conn->query("SELECT id, role FROM users WHERE id = $manager_id")->fetch_assoc();
    if (!$manager) {
        die("Selected manager does not exist.");
    }

    // Check if the manager is heading another department
    $current_department = $conn->query("SELECT id FROM departments WHERE manager_id = $manager_id")->fetch_assoc();
    if ($current_department) {
        // Validate replacement manager
        if (empty($replacement_manager_id)) {
            die("Replacement manager must be selected for the previous department.");
        }

        // Fetch the replacement manager's details
        $replacement_manager = $conn->query("SELECT id, role FROM users WHERE id = $replacement_manager_id")->fetch_assoc();
        if (!$replacement_manager) {
            die("Selected replacement manager does not exist.");
        }

        // Promote team member if necessary
        if ($replacement_manager['role'] == 'team_member') {
            $conn->query("UPDATE users SET role = 'project_manager' WHERE id = $replacement_manager_id");
        }

        // Assign the replacement manager to the previous department
        $conn->query("UPDATE departments SET manager_id = $replacement_manager_id WHERE id = {$current_department['id']}");
    }

    // Promote team member if necessary
    if ($manager['role'] == 'team_member') {
        $conn->query("UPDATE users SET role = 'project_manager' WHERE id = $manager_id");
    }

    // Create the new department and assign the manager
    $conn->query("INSERT INTO departments (name, manager_id) VALUES ('$department_name', $manager_id)");

    echo "Department created and manager assigned successfully.";
}
?>
