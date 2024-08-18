<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'project_manager') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = $_POST['project_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $project_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Project status updated successfully.';
    } else {
        $_SESSION['message'] = 'Error updating project status: ' . $stmt->error;
    }
    $stmt->close();

    header("Location: project_manager_dashboard.php");
    exit();
}
?>
