<?php
require 'db.php'; // database connection

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

    case 'tasks_in_progress':
        $result = $conn->query("SELECT t.id, t.name AS task_name, u.username AS assignee, p.name AS project_name, d.name AS department_name FROM tasks t JOIN users u ON t.assigned_to = u.id JOIN projects p ON t.project_id = p.id JOIN departments d ON p.department_id = d.id WHERE t.status = 'in_progress'");
        $response .= '<h4>Tasks In Progress</h4><table>';
        $response .= '<tr><th>ID</th><th>Task Name</th><th>Assignee</th><th>Project</th><th>Department</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $response .= '<tr>';
            $response .= '<td>' . $row['id'] . '</td>';
            $response .= '<td>' . htmlspecialchars($row['task_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['assignee']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['project_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            $response .= '</tr>';
        }
        $response .= '</table>';
        break;

    case 'completed_tasks':
        $result = $conn->query("SELECT t.id, t.name AS task_name, u.username AS assignee, p.name AS project_name, d.name AS department_name FROM tasks t JOIN users u ON t.assigned_to = u.id JOIN projects p ON t.project_id = p.id JOIN departments d ON p.department_id = d.id WHERE t.status = 'completed'");
        $response .= '<h4>Completed Tasks</h4><table>';
        $response .= '<tr><th>ID</th><th>Task Name</th><th>Assignee</th><th>Project</th><th>Department</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $response .= '<tr>';
            $response .= '<td>' . $row['id'] . '</td>';
            $response .= '<td>' . htmlspecialchars($row['task_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['assignee']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['project_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            $response .= '</tr>';
        }
        $response .= '</table>';
        break;

    case 'project_managers':
        $result = $conn->query("SELECT u.id, u.username, u.email, d.name AS department_name FROM users u JOIN departments d ON u.department_id = d.id WHERE u.role = 'project_manager'");
        $response .= '<h4>Project Managers</h4><table>';
        $response .= '<tr><th>ID</th><th>Username</th><th>Email</th><th>Department</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $response .= '<tr>';
            $response .= '<td>' . $row['id'] . '</td>';
            $response .= '<td>' . htmlspecialchars($row['username']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['email']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            $response .= '</tr>';
        }
        $response .= '</table>';
        break;

    case 'team_members':
        $result = $conn->query("SELECT u.id, u.username, u.email, d.name AS department_name FROM users u JOIN departments d ON u.department_id = d.id WHERE u.role = 'team_member'");
        $response .= '<h4>Team Members</h4><table>';
        $response .= '<tr><th>ID</th><th>Username</th><th>Email</th><th>Department</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $response .= '<tr>';
            $response .= '<td>' . $row['id'] . '</td>';
            $response .= '<td>' . htmlspecialchars($row['username']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['email']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            $response .= '</tr>';
        }
        $response .= '</table>';
        break;

    case 'departments':
        $result = $conn->query("SELECT d.id, d.name AS department_name, u.username AS manager FROM departments d JOIN users u ON d.manager_id = u.id");
        $response .= '<h4>Departments</h4><table>';
        $response .= '<tr><th>ID</th><th>Department Name</th><th>Manager</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $response .= '<tr>';
            $response .= '<td>' . $row['id'] . '</td>';
            $response .= '<td>' . htmlspecialchars($row['department_name']) . '</td>';
            $response .= '<td>' . htmlspecialchars($row['manager']) . '</td>';
            $response .= '</tr>';
        }
        $response .= '</table>';
        break;

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
    }
    $response .= '</table>';

    return $response;
}
?>
