<?php
// task_functions.php

include 'db.php';

function updateTaskStatus($taskId, $status, $progress, $userId) {
    global $conn;
    $stmt = $conn->prepare("UPDATE tasks SET status=?, progress=? WHERE id=? AND assigned_to=?");
    $stmt->bind_param("siii", $status, $progress, $taskId, $userId);
    return $stmt->execute();
}

function sendNotification($userId, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $userId, $message);
    return $stmt->execute();
}

function getTaskById($taskId, $userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id=? AND assigned_to=?");
    $stmt->bind_param("ii", $taskId, $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>
