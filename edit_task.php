<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'project_manager') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $project_id = $_POST['project_id'];
    $priority = $_POST['priority'];
    $assigned_to = $_POST['assigned_to'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tasks SET name=?, description=?, project_id=?, assigned_to=?, priority=?, status=? WHERE id=?");
    $stmt->bind_param("ssiiisi", $name, $description, $project_id, $assigned_to, $priority, $status, $task_id);

    if ($stmt->execute()) {
        $message = "Task updated successfully.";
    } else {
        $message = "Failed to update task.";
    }

    $stmt->close();
    $conn->close();

    $_SESSION['success_message'] = $message;
    header("Location: project_manager_dashboard.php");
    exit();
} else {
    $task_id = $_GET['id'];
    $task = $conn->query("SELECT * FROM tasks WHERE id=$task_id")->fetch_assoc();

    $projects = $conn->query("SELECT id, name FROM projects WHERE manager_id={$_SESSION['userid']}");
    $team_members = $conn->query("SELECT id, username FROM users WHERE role='team_member' AND department_id IN (SELECT department_id FROM users WHERE id={$_SESSION['userid']})");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message-box"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="card">
            <h3>Edit Task</h3>
            <form action="edit_task.php" method="post">
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <label for="name">Task Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($task['name']); ?>" required>

                <label for="description">Task Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($task['description']); ?></textarea>

                <label for="project_id">Project:</label>
                <select id="project_id" name="project_id" required>
                    <?php
                    while ($row = $projects->fetch_assoc()) {
                        $selected = $row['id'] == $task['project_id'] ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                    }
                    ?>
                </select>

                <label for="assigned_to">Assign To:</label>
                <select id="assigned_to" name="assigned_to" required>
                    <?php
                    while ($row = $team_members->fetch_assoc()) {
                        $selected = $row['id'] == $task['assigned_to'] ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['username']}</option>";
                    }
                    ?>
                </select>

                <label for="priority">Priority:</label>
                <select id="priority" name="priority" required>
                    <option value="high" <?php echo $task['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo $task['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo $task['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                </select>

                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="not_started" <?php echo $task['status'] == 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                    <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>

                <button type="submit">Update Task</button>
            </form>
            <br>
            <a href="project_manager_dashboard.php" class="button">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
