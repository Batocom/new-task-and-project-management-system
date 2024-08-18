<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'project_manager') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$task_id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
$stmt->bind_param("i", $task_id);

if ($stmt->execute()) {
    $_SESSION['message'] = 'Task deleted successfully.';
} else {
    $_SESSION['message'] = 'Error deleting task: ' . $stmt->error;
}
$stmt->close();

header("Location: project_manager_dashboard.php");
exit();
?>
