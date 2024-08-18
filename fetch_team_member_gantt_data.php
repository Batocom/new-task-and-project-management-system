<?php
session_start();
include 'db.php';

// Ensure user is logged in and has the right role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'team_member') {
    http_response_code(403);
    exit();
}

$team_member_id = $_SESSION['userid'] ?? null;
if ($team_member_id) {
    // Fetch projects
    $projects = $conn->query("SELECT id, name, created_at, expected_due_date FROM projects WHERE id IN (SELECT project_id FROM tasks WHERE assigned_to=$team_member_id)");

    // Fetch tasks
    $tasks = $conn->query("SELECT id, name, created_at, due_date, project_id FROM tasks WHERE assigned_to=$team_member_id");

    $gantt_data = [];

    while ($project = $projects->fetch_assoc()) {
        $gantt_data[] = [
            'id' => 'project_'.$project['id'],
            'text' => $project['name'],
            'start_date' => $project['created_at'],
            'duration' => (new DateTime($project['expected_due_date']))->diff(new DateTime($project['created_at']))->days,
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
} else {
    http_response_code(403);
}
?>
