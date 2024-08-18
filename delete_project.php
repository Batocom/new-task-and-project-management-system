<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'project_manager') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$project_id = $_GET['id'];
$action = $_GET['action'];

// Function to add notifications
function addNotification($conn, $project_id, $message) {
    $stmt = $conn->prepare("SELECT u.id FROM users u JOIN team_members tm ON u.id = tm.user_id WHERE tm.project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['id'];
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notif_stmt->bind_param("is", $user_id, $message);
        $notif_stmt->execute();
        $notif_stmt->close();
    }
    $stmt->close();
}

switch ($action) {
    case 'delete':
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $project_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Project deleted successfully.';
            addNotification($conn, $project_id, "The project has been deleted.");
        } else {
            $_SESSION['message'] = 'Error deleting project: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'disable':
        $stmt = $conn->prepare("UPDATE projects SET state = 'disabled' WHERE id = ?");
        $stmt->bind_param("i", $project_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Project disabled successfully.';
            addNotification($conn, $project_id, "The project has been disabled.");
        } else {
            $_SESSION['message'] = 'Error disabling project: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'enable':
        $stmt = $conn->prepare("UPDATE projects SET state = 'active' WHERE id = ?");
        $stmt->bind_param("i", $project_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Project enabled successfully.';
            addNotification($conn, $project_id, "The project has been enabled.");
        } else {
            $_SESSION['message'] = 'Error enabling project: ' . $stmt->error;
        }
        $stmt->close();
        break;

    default:
        $_SESSION['message'] = 'Invalid action.';
        break;
}

header("Location: project_manager_dashboard.php");
exit();
?>
