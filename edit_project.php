<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'project_manager') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = $_POST['project_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $manager_id = $_POST['manager_id'];

    $stmt = $conn->prepare("UPDATE projects SET name=?, description=?, status=?, manager_id=? WHERE id=?");
    $stmt->bind_param("sssii", $name, $description, $status, $manager_id, $project_id);

    if ($stmt->execute()) {
        $message = "Project updated successfully.";
    } else {
        $message = "Failed to update project.";
    }

    $stmt->close();
    $conn->close();

    $_SESSION['success_message'] = $message;
    header("Location: project_manager_dashboard.php");
    exit();
} else {
    $project_id = $_GET['id'];
    $project = $conn->query("SELECT * FROM projects WHERE id=$project_id")->fetch_assoc();

    $team_members = $conn->query("SELECT id, username FROM users WHERE role='team_member' AND department_id IN (SELECT department_id FROM users WHERE id={$_SESSION['userid']})");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message-box"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="card">
            <h3>Edit Project</h3>
            <form action="edit_project.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                <label for="name">Project Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>

                <label for="description">Project Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($project['description']); ?></textarea>

                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="not_started" <?php echo $project['status'] == 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                    <option value="in_progress" <?php echo $project['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $project['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>

                <label for="manager_id">Assign To:</label>
                <select id="manager_id" name="manager_id" required>
                    <?php
                    while ($row = $team_members->fetch_assoc()) {
                        $selected = $row['id'] == $project['manager_id'] ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['username']}</option>";
                    }
                    ?>
                </select>

                <button type="submit">Update Project</button>
            </form>
            <br>
            <a href="project_manager_dashboard.php" class="button">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
