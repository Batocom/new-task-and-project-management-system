<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'team_member') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $taskId = $_POST['task_id'];
    $newStatus = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
    $stmt->bind_param("sii", $newStatus, $taskId, $_SESSION['userid']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Task status updated successfully.";
    } else {
        echo "Failed to update task status.";
    }

    $stmt->close();
    $conn->close();
    header("Location: team_member_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Task Status</title>
</head>
<body>
    <form action="update_task_status.php" method="post">
        <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
        <label for="status">New Status:</label>
        <select name="status" id="status">
            <option value="complete">Complete</option>
            <option value="inprogress">In Progress</option>
            <option value="assigned but not started">Assigned but Not Started</option>
            <option value="pending">Pending</option>
        </select>
        <button type="submit">Update Status</button>
    </form>
</body>
</html>
