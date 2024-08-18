<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Get the current user's ID and role from the session
$user_id = $_SESSION['userid'];
$role = $_SESSION['role'];

// Fetch notifications specific to the user
$notifications_query = "SELECT * FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC";
$notifications = $conn->query($notifications_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h3>Notifications</h3>
            <table>
                <tr>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                <?php
                while ($row = $notifications->fetch_assoc()) {
                    $details_link = "view_notification.php?project_id={$row['project_id']}&task_id={$row['task_id']}";
                    echo "<tr>
                            <td><a href='$details_link'>".htmlspecialchars($row['message'])."</a></td>
                            <td>".htmlspecialchars($row['status'])."</td>
                            <td>".htmlspecialchars($row['created_at'])."</td>
                            <td>
                                <a href='mark_as_read.php?id={$row['id']}'>Mark as Read</a>
                            </td>
                          </tr>";
                }
                ?>
            </table>
            <br>
            <a href="<?php echo $role == 'project_manager' ? 'project_manager_dashboard.php' : 'team_member_dashboard.php'; ?>" class="button">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
