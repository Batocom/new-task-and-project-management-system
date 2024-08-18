<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'team_member') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$notification_id = $_GET['id'];
$conn->query("UPDATE notifications SET status='read' WHERE id=$notification_id");

header("Location: notifications.php");
exit();
?>
