<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $oldManagerId = $_POST['oldManagerId'];
    $newManagerId = $_POST['newManagerId'];

    // Update the old department with the new manager
    $stmt = $conn->prepare("UPDATE departments SET manager_id = ? WHERE manager_id = ?");
    $stmt->bind_param("ii", $newManagerId, $oldManagerId);

    if ($stmt->execute()) {
        echo "Old department's project manager updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
