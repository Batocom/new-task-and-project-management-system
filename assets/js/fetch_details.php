<?php
require 'db.php'; // Ensure you have the correct database connection

$type = $_POST['type'];
$response = '';

switch ($type) {
    case 'projects':
        $result = $conn->query("SELECT p.id, p.name AS project_name, d.name AS department_name, u.username AS project_manager FROM projects p JOIN departments d ON p.department_id = d.id JOIN users u ON p.manager_id = u.id");
        $response .= '<h4>Projects</h4><table>';
        $response .= '<tr><th>ID</th><th>Project Name</th><th>Department</th><th>Project Manager</th><th>Actions</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $response .= '<tr>';
            $response .= '<td>' . $row['id'] . '</td>';
            $response .= '<td>' . htmlspecialchars($row['project_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['project_manager']) . '</td>';
            $response .= '<td><button onclick="viewProjectDetails(' . $row['id'] . ')">View Details</button></td>';
            $response .= '</tr>';
        }
        $response .= '</table>';
        break;
    case 'tasks':
        $result = $conn->query("SELECT t.id, t.name AS task_name, t.status, u.username AS assignee, p.name AS project_name, d.name AS department_name FROM tasks t JOIN users u ON t.assigned_to = u.id JOIN projects p ON t.project_id = p.id JOIN departments d ON p.department_id = d.id");
        $response .= '<h4>Tasks</h4><table>';
        $response .= '<tr><th>ID</th><th>Task Name</th><th>Status</th><th>Assignee</th><th>Project</th><th>Department</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $response .= '<tr>';
            $response .= '<td>' . $row['id'] . '</td>';
            $response .= '<td>' . htmlspecialchars($row['task_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['status']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['assignee']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['project_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            $response .= '</tr>';
        }
        $response .= '</table>';
        break;
    // Add similar cases for other types: tasks_in_progress, completed_tasks, project_managers, team_members, departments
    default:
        $response .= '<p>Invalid request</p>';
        break;
}

echo $response;

function viewProjectDetails($projectId) {
    global $conn;
    $result = $conn->query("SELECT t.id, t.name AS task_name, t.status, t.priority, t.description, u.username AS assignee, AVG(t.progress) as avg_progress
                            FROM tasks t
                            JOIN users u ON t.assigned_to = u.id
                            WHERE t.project_id = $projectId
                            GROUP BY t.id");
    $response = '<h4>Project Details</h4><table>';
    $response .= '<tr><th>Task ID</th><th>Task Name</th><th>Status</th><th>Priority</th><th>Description</th><th>Assignee</th><th>Average Progress</th></tr>';
    while ($row = $result->fetch_assoc()) {
        $response .= '<tr>';
        $response .= '<td>' . $row['id'] . '</td>';
        $response .= '<td>' . htmlspecialchars($row['task_name']) . '</td>';
        $response .= '<td>' . htmlspecialchars($row['status']) . '</td>';
        $response .= '<td>' . htmlspecialchars($row['priority']) . '</td>';
        $response .= '<td>' . htmlspecialchars($row['description']) . '</td>';
        $response .= '<td>' . htmlspecialchars($row['assignee']) . '</td>';
        $response .= '<td>' . round($row['avg_progress'], 2) . '%</td>';
        $response .= '</tr>';
    }
    $response .= '</table>';

    return $response;
}
?>
