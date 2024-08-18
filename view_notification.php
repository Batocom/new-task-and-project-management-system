<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$project_id = $_GET['project_id'] ?? null;
$task_id = $_GET['task_id'] ?? null;

$project = null;
$task = null;

if ($project_id) {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($task_id) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Details</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h3>Notification Details</h3>

            <?php if ($project): ?>
                <h4>Project Details</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($project['name']) ?></p>
                <p><strong>Date of Creation:</strong> <?= htmlspecialchars($project['created_at']) ?></p>
                <p><strong>Expected Completion Date:</strong> <?= htmlspecialchars($project['expected_due_date']) ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($project['description']) ?></p>
            <?php else: ?>
                <p>No project found associated with this notification.</p>
            <?php endif; ?>

            <?php if ($task): ?>
                <h4>Task Details</h4>
                <p><strong>Project Name:</strong> <?= htmlspecialchars($project['name']) ?></p>
                <p><strong>Task Name:</strong> <?= htmlspecialchars($task['name']) ?></p>
                <p><strong>Date of Creation:</strong> <?= htmlspecialchars($task['created_at']) ?></p>
                <p><strong>Expected Completion Date:</strong> <?= htmlspecialchars($task['due_date']) ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($task['description']) ?></p>
            <?php else: ?>
                <p>No task found associated with this notification.</p>
            <?php endif; ?>

            <br>
            <a href="notifications.php" class="button">Back to Notifications</a>
        </div>
    </div>
</body>
</html>
