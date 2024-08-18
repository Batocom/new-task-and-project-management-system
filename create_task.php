<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_name = isset($_POST['task_name']) ? $_POST['task_name'] : null;
    $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : null;
    $priority = isset($_POST['priority']) ? $_POST['priority'] : null;
    $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $role = $_SESSION['role'];
    $assigned_to = isset($_POST['assigned_to']) ? $_POST['assigned_to'] : $_SESSION['userid'];

    if ($task_name && $project_id && $priority && $due_date && $description) {
        $stmt = $conn->prepare("INSERT INTO tasks (name, project_id, priority, due_date, description, assigned_to) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssi", $task_name, $project_id, $priority, $due_date, $description, $assigned_to);

        if ($stmt->execute()) {
            $task_id = $stmt->insert_id;
            $message = "Task created successfully.";

            // Send notification to assigned user
            $notification_message = "You have been assigned a new task: $task_name.";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->bind_param("is", $assigned_to, $notification_message);
            $notif_stmt->execute();
            $notif_stmt->close();

            if ($role === 'project_manager') {
                header("Location: project_manager_dashboard.php");
            } else {
                header("Location: team_member_dashboard.php");
            }
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Please fill in all fields.";
    }
} else {
    header("Location: login.php");
    exit();
}
?>
