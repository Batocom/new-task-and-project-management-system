<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teamMemberId = $_POST['teamMemberId'];

    // Update the user's role to project manager
    $stmt = $conn->prepare("UPDATE users SET role = 'project_manager' WHERE id = ?");
    $stmt->bind_param("i", $teamMemberId);

    if ($stmt->execute()) {
        echo "User promoted to project manager successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
