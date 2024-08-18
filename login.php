<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_username, $db_password, $role);
        $stmt->fetch();

        // Verify password (hashed password comparison)
        if (password_verify($password, $db_password)) {
            // Store user info in session
            $_SESSION['userid'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($role == 'project_manager') {
                header("Location: project_manager_dashboard.php");
            } elseif ($role == 'team_member') {
                header("Location: team_member_dashboard.php");
            }
            exit();
        } else {
            $error = "Credentials do not match our records.";
        }
    } else {
        $error = "Credentials do not match our records.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
    .container {
        max-width: 400px;
        width: 100%;
        background: rgba(255, 255, 255, 0.9); /* Semi-transparent background */
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        text-align: center; /* Center the text and logo */
    }
    .logo {
        max-width: 100px; /* Adjust the size as needed */
        margin-bottom: 1rem;
    }
    .error-message {
        color: red;
        text-align: center;
        margin-bottom: 1rem;
    }
</style>
<body>
    <div class="login-container">
        <img src="./logo and images/logo.png.png" alt="Logo" class="logo">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" required><br><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
