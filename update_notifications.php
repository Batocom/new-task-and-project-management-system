<?php
session_start();
include 'db.php';

// Function to update notifications with correct project and task IDs
function updateNotificationIDs($conn) {
    // Fetch all notifications
    $notifications = $conn->query("SELECT id, user_id, message FROM notifications");

    while ($notification = $notifications->fetch_assoc()) {
        $notification_id = $notification['id'];
        $user_id = $notification['user_id'];
        $message = $notification['message'];
        
        // Initialize project_id and task_id as NULL
        $project_id = null;
        $task_id = null;

        // Determine if the message is related to a project or a task
        if (strpos($message, 'task') !== false) {
            // Fetch the latest task assigned to the user
            $task_result = $conn->query("SELECT id, project_id FROM tasks WHERE assigned_to = $user_id ORDER BY id DESC LIMIT 1");
            if ($task_row = $task_result->fetch_assoc()) {
                $task_id = $task_row['id'];
                $project_id = $task_row['project_id'];
            }
        } elseif (strpos($message, 'project') !== false) {
            // Fetch the latest project the user is part of
            $project_result = $conn->query("SELECT id FROM projects WHERE manager_id = $user_id ORDER BY id DESC LIMIT 1");
            if ($project_row = $project_result->fetch_assoc()) {
                $project_id = $project_row['id'];
            }
        }

        // Update the notification with the correct project_id and task_id
        $stmt = $conn->prepare("UPDATE notifications SET project_id = ?, task_id = ? WHERE id = ?");
        $stmt->bind_param("iii", $project_id, $task_id, $notification_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Run the update function
updateNotificationIDs($conn);

// Output success message
echo "Notifications table updated successfully.";

?>
