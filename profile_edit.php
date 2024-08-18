<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$userId = $_SESSION['userid'];

// Handle form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password before saving it to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update user details in the database
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $hashedPassword, $userId);

    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
        $_SESSION['username'] = $username; // Update session username
    } else {
        $message = "Failed to update profile.";
    }

    $stmt->close();
}

// Fetch current user details
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($currentUsername, $currentEmail);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="header">
        <div>
            <img src="./logo and images/logo.png.png" alt="Logo" class="logo">
        </div>
        <h2>Edit Profile</h2>
        <div class="nav-buttons">
            <a href="profile_edit.php" class="nav-link">Profile</a>
            <form method="POST" action="logout.php" style="display:inline;">
                <button type="submit" class="nav-link" style="background:none;border:none;color:white;cursor:pointer;">Logout</button>
            </form>
        </div>
    </div>

    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="create_project.php">Create Project</a>
        <a href="create_user.php">Create User</a>
        <a href="create_department.php">Create Department</a>
    </div>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message-box"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="card">
            <h3>Edit Profile</h3>
            <form action="profile_edit.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($currentUsername); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($currentEmail); ?>" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Update Profile</button>
            </form>
            <br>
            <a href="javascript:history.back()" class="button">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
