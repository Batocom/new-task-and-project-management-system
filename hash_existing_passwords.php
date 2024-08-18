<?php
include 'db.php';

// Clear the users table
$conn->query("TRUNCATE TABLE users");

// Define users to be added
$users = [
    ['id' => 1, 'username' => 'admin1', 'email' => 'admin1@example.com', 'password' => 'adminpass', 'role' => 'admin', 'department_id' => NULL],
    ['id' => 2, 'username' => 'admin2', 'email' => 'admin2@example.com', 'password' => 'adminpass', 'role' => 'admin', 'department_id' => NULL],
    ['id' => 3, 'username' => 'admin3', 'email' => 'admin3@example.com', 'password' => 'adminpass', 'role' => 'admin', 'department_id' => NULL],
    ['id' => 4, 'username' => 'pm1', 'email' => 'pm1@example.com', 'password' => 'pmpass', 'role' => 'project_manager', 'department_id' => 1],
    ['id' => 5, 'username' => 'pm2', 'email' => 'pm2@example.com', 'password' => 'pmpass', 'role' => 'project_manager', 'department_id' => 2],
    ['id' => 6, 'username' => 'pm3', 'email' => 'pm3@example.com', 'password' => 'pmpass', 'role' => 'project_manager', 'department_id' => 3],
    ['id' => 7, 'username' => 'tm1', 'email' => 'tm1@example.com', 'password' => 'password123', 'role' => 'team_member', 'department_id' => 1],
    ['id' => 8, 'username' => 'tm2', 'email' => 'tm2@example.com', 'password' => 'tmpass', 'role' => 'team_member', 'department_id' => 2],
    ['id' => 9, 'username' => 'tm3', 'email' => 'tm3@example.com', 'password' => 'tmpass', 'role' => 'team_member', 'department_id' => 3],
];

// Insert users with hashed passwords
foreach ($users as $user) {
    $id = $user['id'];
    $username = $user['username'];
    $email = $user['email'];
    $password = password_hash($user['password'], PASSWORD_DEFAULT);
    $role = $user['role'];
    $department_id = $user['department_id'];

    $stmt = $conn->prepare("INSERT INTO users (id, username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $id, $username, $email, $password, $role, $department_id);

    if ($stmt->execute()) {
        echo "User $username created successfully.<br>";
    } else {
        echo "Error creating user $username: " . $conn->error . "<br>";
    }

    $stmt->close();
}

$conn->close();
?>
