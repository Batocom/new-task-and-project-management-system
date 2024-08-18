<?php
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input data
    $title = isset($_POST['title']) ? $_POST['title'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $expected_due_date = isset($_POST['expected_due_date']) ? $_POST['expected_due_date'] : null;
    $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $manager_id = isset($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;
    $created_at = date('Y-m-d H:i:s'); // Get the current date and time

    if ($title && $description && $expected_due_date && $department_id && $manager_id) {
        // Insert the new project into the database
        $stmt = $conn->prepare("INSERT INTO projects (name, description, department_id, created_at, expected_due_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $title, $description, $department_id, $created_at, $expected_due_date);
        $stmt->execute();
        $project_id = $stmt->insert_id;

        // Send notification to the project manager
        $notification = "You have been assigned a new project: $title.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, project_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $manager_id, $notification, $project_id);
        $stmt->execute();

        // Fetch all team members in the department
        $result = $conn->query("SELECT id FROM users WHERE department_id = $department_id AND role = 'team_member'");
        while ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];
            // Send notification to each team member
            $team_notification = "A new project has been assigned to your department: $title.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, project_id) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $user_id, $team_notification, $project_id);
            $stmt->execute();
        }

        echo "Project created and notifications sent successfully.";
    } else {
        echo "Error: Please ensure all fields are filled out correctly.";
    }
}
?>
