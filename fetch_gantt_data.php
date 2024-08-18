<?php
session_start();
include 'db.php';

$manager_id = $_SESSION['userid'];
$department_id = $conn->query("SELECT department_id FROM users WHERE id=$manager_id")->fetch_assoc()['department_id'];

// Fetch projects and tasks for the department
$projects = $conn->query("SELECT * FROM projects WHERE department_id=$department_id");
$tasks = $conn->query("SELECT * FROM tasks WHERE project_id IN (SELECT id FROM projects WHERE department_id=$department_id)");

$gantt_data = [];

while ($project = $projects->fetch_assoc()) {
    $gantt_data[] = [
        'id' => 'project_'.$project['id'],
        'text' => $project['name'],
        'start_date' => $project['created_at'],
        'duration' => (new DateTime($project['due_date']))->diff(new DateTime($project['created_at']))->days,
        'type' => 'project'
    ];
}

while ($task = $tasks->fetch_assoc()) {
    $gantt_data[] = [
        'id' => 'task_'.$task['id'],
        'text' => $task['name'],
        'start_date' => $task['created_at'],
        'duration' => (new DateTime($task['due_date']))->diff(new DateTime($task['created_at']))->days,
        'parent' => 'project_'.$task['project_id'],
        'type' => 'task'
    ];
}

echo json_encode($gantt_data);
?>
