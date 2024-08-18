<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'User is not authenticated']);
    exit();
}

include 'db.php';

// Fetch Gantt data for all departments
$gantt_data = [];

$projects = $conn->query("SELECT id, name, start_date, due_date FROM projects");
while ($project = $projects->fetch_assoc()) {
    $start_date = new DateTime($project['start_date']);
    $end_date = new DateTime($project['due_date']);
    $duration = $start_date->diff($end_date)->days;

    $gantt_data[] = [
        'id' => 'project_' . $project['id'],
        'text' => $project['name'],
        'start_date' => $project['start_date'],
        'duration' => $duration,
        'type' => 'project'
    ];

    $tasks = $conn->query("SELECT id, name, start_date, due_date FROM tasks WHERE project_id = " . $project['id']);
    while ($task = $tasks->fetch_assoc()) {
        $task_start_date = new DateTime($task['start_date']);
        $task_end_date = new DateTime($task['due_date']);
        $task_duration = $task_start_date->diff($task_end_date)->days;

        $gantt_data[] = [
            'id' => 'task_' . $task['id'],
            'text' => $task['name'],
            'start_date' => $task['start_date'],
            'duration' => $task_duration,
            'parent' => 'project_' . $project['id'],
            'type' => 'task'
        ];
    }
}

echo json_encode($gantt_data);
?>
