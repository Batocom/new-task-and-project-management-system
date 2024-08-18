<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_GET['id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $user['password'];
    $department_id = isset($_POST['department_id']) ? $_POST['department_id'] : null;

    // Validate department assignment
    if ($role == 'project_manager' || $role == 'team_member') {
        if (empty($department_id)) {
            $_SESSION['message'] = 'Department must be selected for Project Managers and Team Members.';
            header("Location: edit_user.php?id=$user_id");
            exit();
        }

        if ($role == 'project_manager') {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE department_id = ? AND role = 'project_manager' AND id != ?");
            $stmt->bind_param("ii", $department_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $_SESSION['message'] = 'This department already has a Project Manager.';
                header("Location: edit_user.php?id=$user_id");
                exit();
            }
        }
    }

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ?, department_id = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $username, $email, $role, $password, $department_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'User updated successfully.';
    } else {
        $_SESSION['message'] = 'Error updating user: ' . $stmt->error;
    }
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h2>Edit User</h2>
    <form method="POST" action="edit_user.php?id=<?php echo $user_id; ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>
        <label for="role">Role:</label>
        <select id="role" name="role" required onchange="toggleDepartmentField(this)">
            <option value="project_manager" <?php if ($user['role'] == 'project_manager') echo 'selected'; ?>>Project Manager</option>
            <option value="team_member" <?php if ($user['role'] == 'team_member') echo 'selected'; ?>>Team Member</option>
        </select><br><br>
        <div id="departmentField" style="display: <?php echo ($user['role'] == 'project_manager' || $user['role'] == 'team_member') ? 'block' : 'none'; ?>;">
            <label for="department_id">Department:</label>
            <select id="department_id" name="department_id">
                <option value="">Select Department</option>
                <?php
                $departments = $conn->query("SELECT id, name FROM departments");
                while ($row = $departments->fetch_assoc()) {
                    $selected = $row['id'] == $user['department_id'] ? 'selected' : '';
                    echo "<option value=\"{$row['id']}\" $selected>" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select><br><br>
        </div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password"><br><br>
        <button type="submit">Update User</button>
    </form>

    <script>
        function toggleDepartmentField(select) {
            var departmentField = document.getElementById('departmentField');
            if (select.value === 'project_manager' || select.value === 'team_member') {
                departmentField.style.display = 'block';
            } else {
                departmentField.style.display = 'none';
            }
        }
    </script>
</body>
</html>
