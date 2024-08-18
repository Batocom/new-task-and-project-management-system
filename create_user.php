<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$username = $_POST['username'];
$email = $_POST['email'];
$role = $_POST['role'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$department_id = isset($_POST['department_id']) ? $_POST['department_id'] : null;

if ($role == 'project_manager' || $role == 'team_member') {
    if (empty($department_id)) {
        $_SESSION['message'] = 'Department must be selected for Project Managers and Team Members.';
        header("Location: admin_dashboard.php");
        exit();
    }

    if ($role == 'project_manager') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE department_id = ? AND role = 'project_manager'");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $_SESSION['message'] = 'This department already has a Project Manager.';
            header("Location: admin_dashboard.php");
            exit();
        }
    }
}

$stmt = $conn->prepare("INSERT INTO users (username, email, role, password, department_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $username, $email, $role, $password, $department_id);

if ($stmt->execute()) {
    $_SESSION['message'] = 'User created successfully.';
} else {
    $_SESSION['message'] = 'Error creating user.';
}

$stmt->close();
header("Location: admin_dashboard.php");
exit();
?>
