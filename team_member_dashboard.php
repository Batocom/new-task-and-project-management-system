<?php
ob_start();
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'team_member') {
    header("Location: login.php");
    exit();
}


include 'db.php';
include 'task_functions.php';

// Ensure the user is logged in
$team_member_id = $_SESSION['userid'] ?? null;
if (!$team_member_id) {
    header("Location: login.php");
    exit();
}

// Ensure the user is logged in
$projects = [];

// Fetch projects assigned to the current user
if ($team_member_id) {
    $projects = $conn->query("SELECT projects.id, projects.name 
                              FROM projects
                              JOIN tasks ON tasks.project_id = projects.id
                              WHERE tasks.assigned_to = $team_member_id
                              GROUP BY projects.id, projects.name");
}

// Fetch team member's department
$result = $conn->query("SELECT d.name AS department_name FROM users u JOIN departments d ON u.department_id = d.id WHERE u.id=$team_member_id");
$department = $result->fetch_assoc()['department_name'] ?? 'Unknown';

// Pagination setup
$tasks_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $tasks_per_page;

// Fetch total number of tasks assigned to the current user
$total_tasks_result = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to=$team_member_id");
$total_tasks = $total_tasks_result->fetch_assoc()['count'];
$total_pages = ceil($total_tasks / $tasks_per_page);

// Fetch tasks assigned to the current user with pagination
$tasks_result = $conn->query("SELECT tasks.id, tasks.name AS task_name, projects.name AS project_name, users.username AS assigned_to, tasks.priority, tasks.status, tasks.progress
                              FROM tasks
                              JOIN projects ON tasks.project_id = projects.id
                              JOIN users ON tasks.assigned_to = users.id
                              WHERE tasks.assigned_to = $team_member_id
                              LIMIT $tasks_per_page OFFSET $offset");

// Fetch task counts for different statuses
$task_counts = $conn->query("SELECT 
                                SUM(status='not_started') as not_started, 
                                SUM(status='in_progress') as in_progress, 
                                SUM(status='pending') as pending, 
                                SUM(status='completed') as completed 
                             FROM tasks WHERE assigned_to=$team_member_id")->fetch_assoc();

// Fetch notifications for the team member
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id=$team_member_id ORDER BY created_at DESC");

// Fetch notifications count for the user
$user_id = $_SESSION['userid'];
$unread_notifications_result = $conn->query("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id=$team_member_id AND status='unread'");
$unread_notifications_count = $unread_notifications_result->fetch_assoc()['unread_count'];

// Fetch analytics data
$tasks_completed = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to=$user_id AND status='completed'")->fetch_assoc()['count'];
$tasks_in_progress = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to=$user_id AND status='in_progress'")->fetch_assoc()['count'];

// Update task status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task_status'])) {
    $taskId = $_POST['task_id'];
    $status = $_POST['status'];
    $progress = isset($_POST['progress']) ? $_POST['progress'] : null;

    // Synchronize progress and status
    if ($status == 'completed' && $progress < 100) {
        $progress = 100;
    } elseif ($status != 'completed' && $progress == 100) {
        $status = 'completed';
    } elseif ($progress > 0 && $progress < 100 && $status == 'pending') {
        $status = 'in_progress';
    } elseif ($progress == 0 && $status == 'in_progress') {
        $status = 'pending';
    }

    $stmt = $conn->prepare("UPDATE tasks SET status = ?, progress = ? WHERE id = ?");
    $stmt->bind_param("sii", $status, $progress, $taskId);
    if ($stmt->execute()) {
        sendNotification($team_member_id, "Task status updated.");
        header("Location: team_member_dashboard.php");
        exit();
    } else {
        echo "Failed to update task status.";
    }
    $stmt->close();
}

// Fetch projects and tasks assigned to the team member
$projects = $conn->query("SELECT * FROM projects WHERE id IN (SELECT project_id FROM tasks WHERE assigned_to=$team_member_id)");
$tasks = $conn->query("SELECT * FROM tasks WHERE assigned_to=$team_member_id");

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

$gantt_data_json = json_encode($gantt_data);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Member Dashboard</title>
    <link rel="stylesheet" href="dashboard1.css">
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <link rel="stylesheet" href="/dashboard/new task and project management system/assets/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="/dashboard/new task and project management system/assets/js/all.min.js"></script>
    <<link rel="stylesheet" href="assets/css/dhtmlxgantt_skyblue.css">
    <script src="assets/js/dhtmlxgantt.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the chart with Gantt data on page load
        const ganttData = <?php echo json_encode($gantt_data); ?>;
        renderGanttChart(ganttData);
    });
    
    function showSection(sectionId) {
        // Hide all sections
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => {
            section.style.display = 'none';
        });
    
        // Show the selected section
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = 'block';
    
            // Load and render Gantt chart only when the Gantt section is shown
            if (sectionId === 'gantt_here_section') {
                fetchGanttData();
            }
        }
    }
    
    function fetchGanttData() {
        fetch('fetch_team_member_gantt_data.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(ganttData => {
                renderGanttChart(ganttData);
            })
            .catch(error => {
                console.error('Error loading Gantt data:', error.message);
            });
    }
    
    function renderGanttChart(ganttData) {
        gantt.config.columns = [
            {name: "text", label: "Task name", width: "*", tree: true},
            {name: "start_date", label: "Start time", align: "center"},
            {name: "duration", label: "Duration", align: "center"}
        ];
    
        gantt.init("gantt_here");
        gantt.parse({ data: ganttData });
    }
    function initChart() {
        var ctx = document.getElementById('taskStatusChart').getContext('2d');
        var taskStatusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Not Started', 'In Progress', 'Pending', 'Completed'],
                datasets: [{
                    label: 'Tasks',
                    data: [<?php echo $task_counts['not_started']; ?>, <?php echo $task_counts['in_progress']; ?>, <?php echo $task_counts['pending']; ?>, <?php echo $task_counts['completed']; ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    </script>
</head>
<body>
    <div class="header">
        <div>
            <img src="./logo and images/logo.png.png" alt="Logo" class="logo">
        </div>
        <h2>Team Member Dashboard</h2>
        <div class="nav-buttons">
        <a href="notifications.php" class="nav-link">
            <span class="icon">🔔</span>
            <span class="notification-count"><?php echo $unread_notifications_count; ?></span>
        </a>
    </div>
            <a href="profile_edit.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <form method="POST" action="logout.php" style="display:inline;">
                <button type="submit" class="nav-link" style="background:none;border:none;color:white;cursor:pointer;"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="sidebar">
    <a href="#" onclick="showSection('dashboard')">
        <i class="fas fa-tachometer-alt"></i> 
        <span>Dashboard</span>
    </a>
    
    <a href="#" onclick="showSection('create_task')">
        <i class="fas fa-tasks"></i> 
        <span>Create Task</span>
    </a>
    <a href="#" onclick="showSection('my_tasks')">
        <i class="fas fa-clipboard-list"></i> 
        <span>My tasks</span>
    </a>
    </a>
    <a href="#" onclick="showSection('statistics')">
        <i class="fas fa-chart-bar"></i> 
        <span>Statistics</span>
    </a>
    <a href="#" onclick="showSection('gantt_here_section')">
    <i class="fa-solid fa-chart-gantt"></i> 
    <span>Gantt Chart</span>
</a>
</div>

    <div class="container">
        <h3 class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! You are logged in as a team member in the <?php echo htmlspecialchars($department); ?> department.</h3>

        <!-- Status Cards -->
        <div class="status-card">
            <div class="card">
                <h3>Total Not Started</h3>
                <p><?php echo $task_counts['not_started']; ?></p>
            </div>
            <div class="card">
                <h3>Total In Progress</h3>
                <p><?php echo $task_counts['in_progress']; ?></p>
            </div>
            <div class="card">
                <h3>Total Pending</h3>
                <p><?php echo $task_counts['pending']; ?></p>
            </div>
            <div class="card">
                <h3>Total Completed</h3>
                <p><?php echo $task_counts['completed']; ?></p>
            </div>
        </div>
        
   <!-- Create Task Section -->
   <div id="create_task" class="section" style="display:none;">
    <div class="card">
        <h4>Create Task</h4>
        <form method="POST" action="create_task.php">
            <div class="form-group">
                <label for="task_name">Task Name:</label>
                <input type="text" id="task_name" name="task_name" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="project_id">Project:</label>
                <select id="project_id" name="project_id" required>
                    <?php
                    include 'db.php'; // Ensure you include your database connection file

                    // Fetch projects assigned to the team member
                    $team_member_id = $_SESSION['userid']; // Ensure the session has the userid
                    $projects = $conn->query("SELECT projects.id, projects.name 
                                              FROM projects
                                              JOIN tasks ON tasks.project_id = projects.id
                                              WHERE tasks.assigned_to = $team_member_id
                                              GROUP BY projects.id, projects.name");

                    // Check if projects query returns results
                    if ($projects === false) {
                        echo "<option value=''>Error fetching projects: " . $conn->error . "</option>";
                    } else if ($projects->num_rows > 0) {
                        while ($row = $projects->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                    } else {
                        echo "<option value=''>No projects available</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="priority">Priority:</label>
                <select id="priority" name="priority" required>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="form-group">
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>
            <button type="submit" class="btn">Create Task</button>
        </form>
    </div>
</div>

<<?php
$task_name = $_POST['task_name'] ?? null;
$description = $_POST['description'] ?? null;
$project_id = $_POST['project_id'] ?? null;
$priority = $_POST['priority'] ?? null;
$due_date = $_POST['due_date'] ?? null;

// Validate that all fields are filled
if ($task_name && $description && $project_id && $priority && $due_date) {
    // Sanitize inputs
    $task_name = $conn->real_escape_string($task_name);
    $description = $conn->real_escape_string($description);
    $project_id = (int)$project_id;
    $priority = $conn->real_escape_string($priority);
    $due_date = $conn->real_escape_string($due_date);

    // Insert the new task into the database
    $insert_query = "INSERT INTO tasks (name, description, project_id, assigned_to, priority, due_date) 
                     VALUES ('$task_name', '$description', $project_id, $team_member_id, '$priority', '$due_date')";

    if ($conn->query($insert_query) === TRUE) {
        echo "New task created successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Please fill in all required fields.";
}
?>


    <div id="statistics" class="card section" style="display: none;">
        <h4>Statistics</h4>
        <div>
            <p>Total Tasks: <?php echo $total_tasks; ?></p>
            <canvas id="taskStatusChart"></canvas>
        </div>
    </div>

    <div id="gantt_here_section" class="card section gantt-chart-card" style="display:none;">
    <h4>Gantt Chart</h4>
    <div id="gantt_here"></div>
</div>

   <div id="my_tasks" class="card section" style="display: none;">
    <h2>My Tasks</h2>
    <table>
        <tr>
            <th>Task Name</th>
            <th>Project Name</th>
            <th>Assigned To</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Progress</th>
            <th>Actions</th>
        </tr>
        <?php
        // Ensure the user is logged in
        $team_member_id = $_SESSION['userid'] ?? null;
        if ($team_member_id) {
            // Pagination setup
            $tasks_per_page = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $tasks_per_page;

            // Fetch total number of tasks assigned to the current user
            $total_tasks = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE assigned_to=$team_member_id")->fetch_assoc()['count'];
            $total_pages = ceil($total_tasks / $tasks_per_page);

            // Fetch tasks assigned to the current user with pagination
            $tasks = $conn->query("SELECT tasks.id, tasks.name AS task_name, projects.name AS project_name, users.username AS assigned_to, tasks.priority, tasks.status, tasks.progress
                                   FROM tasks
                                   JOIN projects ON tasks.project_id = projects.id
                                   JOIN users ON tasks.assigned_to = users.id
                                   WHERE tasks.assigned_to = $team_member_id
                                   LIMIT $tasks_per_page OFFSET $offset");

            while ($task = $tasks->fetch_assoc()) {
                echo "<tr>
                        <td>{$task['task_name']}</td>
                        <td>{$task['project_name']}</td>
                        <td>{$task['assigned_to']}</td>
                        <td>{$task['priority']}</td>
                        <td>
                            <form method='POST' action=''>
                                <input type='hidden' name='task_id' value='{$task['id']}'>
                                <select name='status'>
                                    <option value='not_started' ".($task['status'] == 'not_started' ? 'selected' : '').">Not Started</option>
                                    <option value='in_progress' ".($task['status'] == 'in_progress' ? 'selected' : '').">In Progress</option>
                                    <option value='pending' ".($task['status'] == 'pending' ? 'selected' : '').">Pending</option>
                                    <option value='completed' ".($task['status'] == 'completed' ? 'selected' : '').">Completed</option>
                                </select>
                        </td>
                        <td>
                            <div class='progress-bar'>
                                <div class='progress' style='width: {$task['progress']}%;'>{$task['progress']}%</div>
                            </div>
                            <input type='number' name='progress' value='{$task['progress']}' min='0' max='100'>
                        </td>
                        <td>
                            <button type='submit'><i class='fas fa-save'></i> Update</button>
                            </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Please log in to view your tasks.</td></tr>";
        }
        ?>
    </table>

    <!-- Pagination links -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

    <div class="task-count">
        <form method="GET" action="">
            <label for="tasks_per_page">Tasks per page:</label>
            <select name="tasks_per_page" id="tasks_per_page" onchange="this.form.submit()">
                <option value="10" <?php echo $tasks_per_page == 10 ? 'selected' : ''; ?>>10</option>
                <option value="20" <?php echo $tasks_per_page == 20 ? 'selected' : ''; ?>>20</option>
                <option value="50" <?php echo $tasks_per_page == 50 ? 'selected' : ''; ?>>50</option>
            </select>
        </form>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    $progress = $_POST['progress'];

    // Update task status and progress
    $update_query = "UPDATE tasks SET status='$status', progress=$progress WHERE id=$task_id";
    $conn->query($update_query);

    // Reload the page to reflect changes
    header("Location: team_member_dashboard.php?page=$page&tasks_per_page=$tasks_per_page");
    exit();
}
?>

</body>
</html>
