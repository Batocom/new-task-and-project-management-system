<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'project_manager') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$project_id = $_GET['id'];
$action = $_GET['action'];

// Function to send notifications
function sendNotifications($conn, $project_id, $message, $description = '', $task_id = null) {
    // Send notification to the project manager
    $stmt = $conn->prepare("SELECT manager_id FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($row = $result->fetch_assoc()) {
        $manager_id = $row['manager_id'];
        if ($manager_id) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, project_id, task_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isii", $manager_id, $message, $project_id, $task_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Fetch all team members associated with the project
    $stmt = $conn->prepare("SELECT u.id AS user_id
                            FROM users u
                            JOIN tasks t ON u.id = t.assigned_to
                            WHERE t.project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        if ($user_id) {
            $full_message = $message;
            if ($description) {
                $full_message .= " Click <a href='view_notification.php?project_id=$project_id&task_id=$task_id'>here</a> to view details.";
            }
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, project_id, task_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isii", $user_id, $full_message, $project_id, $task_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($action == 'delete') {
    // Delete project
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Project deleted successfully.';
        sendNotifications($conn, $project_id, 'A project you were part of has been deleted.');
        $notification_type = 'success';
    } else {
        $_SESSION['message'] = 'Error deleting project: ' . $stmt->error;
        $notification_type = 'error';
    }
    $stmt->close();

} elseif ($action == 'disable') {
    // Disable project with description
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $description = $_POST['description'];
        $stmt = $conn->prepare("UPDATE projects SET state = 'disabled', disable_description = ? WHERE id = ?");
        $stmt->bind_param("si", $description, $project_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Project disabled successfully.';
            sendNotifications($conn, $project_id, 'A project you were part of has been disabled.', $description);
            $notification_type = 'success';
        } else {
            $_SESSION['message'] = 'Error disabling project: ' . $stmt->error;
            $notification_type = 'error';
        }
        $stmt->close();
    } else {
        // Display disable form
        echo '<form method="POST" action="project_action.php?id=' . $project_id . '&action=disable">
                <label for="description">Reason for disabling:</label>
                <textarea id="description" name="description" required></textarea>
                <button type="submit">Disable Project</button>
              </form>';
        exit();
    }

} elseif ($action == 'enable') {
    // Enable project
    $stmt = $conn->prepare("UPDATE projects SET state = 'active', disable_description = NULL WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Project enabled successfully.';
        sendNotifications($conn, $project_id, 'A project you were part of has been enabled.');
        $notification_type = 'success';
    } else {
        $_SESSION['message'] = 'Error enabling project: ' . $stmt->error;
        $notification_type = 'error';
    }
    $stmt->close();

} else {
    $_SESSION['message'] = 'Invalid action.';
    $notification_type = 'error';
}

// Output JavaScript to display custom notification
echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('{$_SESSION['message']}', '$notification_type');
    });
</script>";

header("Location: project_manager_dashboard.php");
exit();
?>
