<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'project_manager') {
    header("Location: login.php");
    exit();
}

include 'db.php';

$manager_id = $_SESSION['userid'];
$department_id = $conn->query("SELECT department_id FROM users WHERE id=$manager_id")->fetch_assoc()['department_id'];
$department_name = $conn->query("SELECT name FROM departments WHERE id=$department_id")->fetch_assoc()['name'];


// Count unread notifications for the Project Manager
$result = $conn->query("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id=$manager_id AND status='unread'");
$row = $result->fetch_assoc();
$unread_notifications_count = $row['unread_count'];

// Fetch data for statistics
$totalProjects = $conn->query("SELECT COUNT(*) FROM projects WHERE department_id=$department_id")->fetch_row()[0];
$totalTasks = $conn->query("SELECT COUNT(*) FROM tasks JOIN projects ON tasks.project_id = projects.id WHERE projects.department_id=$department_id")->fetch_row()[0];
$totalTeamMembers = $conn->query("SELECT COUNT(*) FROM users WHERE role='team_member' AND department_id=$department_id")->fetch_row()[0];

// Fetch total projects, total tasks, total team members, and task counts
$totalProjects = $conn->query("SELECT COUNT(*) AS total_projects FROM projects WHERE department_id=$department_id")->fetch_assoc()['total_projects'];
$totalTasks = $conn->query("SELECT COUNT(*) AS total_tasks FROM tasks JOIN projects ON tasks.project_id = projects.id WHERE projects.department_id=$department_id")->fetch_assoc()['total_tasks'];
$totalTeamMembers = $conn->query("SELECT COUNT(*) AS members FROM users WHERE role='team_member' AND department_id=$department_id")->fetch_assoc()['members'];

$task_counts = [
    'in_progress' => $conn->query("SELECT COUNT(*) AS count FROM tasks JOIN projects ON tasks.project_id = projects.id WHERE tasks.status='in_progress' AND projects.department_id=$department_id")->fetch_assoc()['count'],
    'completed' => $conn->query("SELECT COUNT(*) AS count FROM tasks JOIN projects ON tasks.project_id = projects.id WHERE tasks.status='completed' AND projects.department_id=$department_id")->fetch_assoc()['count']
];



// Fetch projects for creating tasks
$projects = $conn->query("SELECT id, name FROM projects WHERE department_id=$department_id");

// Fetch users for creating tasks
$team_members = $conn->query("SELECT id, username FROM users WHERE role='team_member' AND department_id=$department_id");

// Fetch notifications
$notifications = $conn->query("SELECT message FROM notifications WHERE user_id=$manager_id ORDER BY created_at DESC LIMIT 5");

// Fetch projects and tasks for the department
$projects = $conn->query("SELECT * FROM projects WHERE department_id=$department_id");
$tasks = $conn->query("SELECT * FROM tasks WHERE project_id IN (SELECT id FROM projects WHERE department_id=$department_id)");

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
    <title>Project Manager Dashboard</title>
    <link rel="stylesheet" href="dashboard1.css">
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <link rel="stylesheet" href="/dashboard/new task and project management system/assets/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="/dashboard/new task and project management system/assets/js/all.min.js"></script>
    <link rel="stylesheet" href="assets/css/dhtmlxgantt_skyblue.css">
    <script src="assets/js/dhtmlxgantt.js"></script>
</head>
<body>
    <div class="header">
        <div>
            <img src="./logo and images/logo.png.png" alt="Logo" class="logo">
        </div>
        <h2>Project Manager Dashboard</h2>
        <div class="nav-buttons">
            <a href="profile_edit.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
    <a href="notifications.php" class="nav-link">
        <span class="icon">🔔</span>
        <span class="notification-count"><?php echo $unread_notifications_count; ?></span>
    </a>
</div>

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
    <a href="#" onclick="showSection('create_project')">
        <i class="fas fa-folder-plus"></i> 
        <span>Create Project</span>
    </a>
    <a href="#" onclick="showSection('create_task')">
        <i class="fas fa-tasks"></i> 
        <span>Create Task</span>
    </a>
    <a href="#" onclick="showSection('current_projects')">
        <i class="fas fa-project-diagram"></i> 
        <span>Current Projects</span>
    </a>
    <a href="#" onclick="showSection('all_tasks')">
        <i class="fas fa-clipboard-list"></i> 
        <span>All Team Tasks</span>
    </a>
    <a href="#" onclick="showSection('statistics')">
        <i class="fas fa-chart-bar"></i> 
        <span>Statistics</span>

    <a href="#" onclick="showSection('gantt_here_section')">
    <i class="fa-solid fa-chart-gantt"></i> 
    <span>Gantt Chart</span>
</a>

</div>




    <div class="container">
        <h3 class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! You are logged in as Project Manager for the <?php echo htmlspecialchars($department_name); ?> department.</h3>


        <div id="dashboard" class="section">
            <h2>Dashboard</h2>
            <!-- Add dashboard content here -->
        </div>
             <!-- Status Cards -->
<div class="status-card">
   <div class="card">
       <h3>Projects</h3>
       <p><?php echo $totalProjects; ?></p>
   </div>
   <div class="card">
       <h3>Total In Progress</h3>
       <p><?php echo $task_counts['in_progress']; ?></p>
   </div>
   <div class="card">
       <h3>Tasks</h3>
       <p><?php echo $totalTasks; ?></p>
   </div>
   <div class="card">
       <h3>Total Completed</h3>
       <p><?php echo $task_counts['completed']; ?></p>
   </div>
   <div class="card">
       <h3>Team Members</h3>
       <p><?php echo $totalTeamMembers; ?></p>
   </div>
</div>

<div id="create_project" class="section" style="display:none;">
    <div class="card">
        <h2>Create Project</h2>
        <form action="create_project.php" method="post">
            <div class="form-group">
                <label for="title">Project Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="expected_due_date">Expected Due Date:</label>
                <input type="date" id="expected_due_date" name="expected_due_date" required>
            </div>
            <input type="hidden" name="department_id" value="<?php echo htmlspecialchars($department_id); ?>">
            <input type="hidden" name="manager_id" value="<?php echo htmlspecialchars($manager_id); ?>">
            <button type="submit" class="btn">Create Project</button>
        </form>
    </div>
</div>



<div id="customModal" class="modal">
    <div class="modal-content" id="modalContent">
        <span class="close">&times;</span>
        <div class="icon" id="modalIcon"></div>
        <p id="modalMessage"></p>
    </div>
</div>

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

                    // Fetch projects based on the department of the project manager
                    $manager_id = $_SESSION['userid']; // Ensure the session has the userid
                    $department_query = $conn->query("SELECT department_id FROM users WHERE id = $manager_id");
                    if ($department_query->num_rows > 0) {
                        $department_row = $department_query->fetch_assoc();
                        $department_id = $department_row['department_id'];
                        
                        $projects = $conn->query("SELECT id, name FROM projects WHERE department_id = $department_id");

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
                    } else {
                        echo "<option value=''>Department not found</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="assigned_to">Assigned To:</label>
                <select id="assigned_to" name="assigned_to" required>
                    <?php
                    // Fetch team members in the same department
                    $team_members = $conn->query("SELECT id, username FROM users WHERE role='team_member' AND department_id = $department_id");

                    // Check if team members query returns results
                    if ($team_members === false) {
                        echo "<option value=''>Error fetching team members: " . $conn->error . "</option>";
                    } else if ($team_members->num_rows > 0) {
                        while ($row = $team_members->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['username']}</option>";
                        }
                    } else {
                        echo "<option value=''>No team members available</option>";
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


<div id="current_projects" class="section" style="display: none;">
    <h2>Current Projects</h2>
    <table>
        <tr>
            <th>Project Name</th>
            <th>Task Name</th>
            <th>Assigned To</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        // Pagination setup
        $limit = 10;  // Number of entries per page
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Fetch total number of projects
        $totalProjectsResult = $conn->query("SELECT COUNT(*) AS total FROM projects WHERE department_id = $department_id");
        $totalProjects = $totalProjectsResult->fetch_assoc()['total'];
        $totalPages = ceil($totalProjects / $limit);

        // Fetch projects for the department with pagination
        $projectsResult = $conn->query("SELECT projects.id AS project_id, projects.name AS project_name, projects.state AS project_state 
                                        FROM projects 
                                        WHERE department_id = $department_id 
                                        LIMIT $limit OFFSET $offset");

        while ($projectRow = $projectsResult->fetch_assoc()) {
            $project_id = $projectRow['project_id'];
            $project_state = $projectRow['project_state'];

            // Display project row
            echo "<tr>
                    <td>{$projectRow['project_name']}</td>
                    <td colspan='5'></td>
                  </tr>";

            // Fetch tasks for the project
            $tasksResult = $conn->query("SELECT tasks.id, tasks.name AS task_name, users.username AS assigned_to, tasks.priority, tasks.status
                                         FROM tasks
                                         JOIN users ON tasks.assigned_to = users.id
                                         WHERE tasks.project_id = $project_id");

            while ($taskRow = $tasksResult->fetch_assoc()) {
                echo "<tr>
                        <td></td>
                        <td>{$taskRow['task_name']}</td>
                        <td>{$taskRow['assigned_to']}</td>
                        <td>{$taskRow['priority']}</td>
                        <td>{$taskRow['status']}</td>
                        <td>
                            <a href='edit_task.php?id={$taskRow['id']}'><i class='fas fa-edit'></i> Edit</a>
                            <a href='task_action.php?id={$taskRow['id']}&action=delete' onclick='return confirm(\"Are you sure you want to delete this task?\")'><i class='fas fa-trash'></i> Delete</a>";
                // Display appropriate action based on project state
                if ($project_state == 'active') {
                    echo "<a href='project_action.php?id={$project_id}&action=disable' onclick='return confirm(\"Are you sure you want to disable this project?\")'><i class='fas fa-ban'></i> Disable</a>";
                } else {
                    echo "<a href='project_action.php?id={$project_id}&action=enable' onclick='return confirm(\"Are you sure you want to enable this project?\")'><i class='fas fa-check'></i> Enable</a>";
                }
                echo "    </td>
                      </tr>";
            }
        }
        ?>
    </table>

    <!-- Pagination links -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
</div>

        <div id="all_tasks" class="section" style="display:none;">
            <h2>All Team Tasks</h2>
            <table>
                <tr>
                    <th>Task Name</th>
                    <th>Project</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php
                $result = $conn->query("SELECT tasks.id, tasks.name AS task_name, projects.name AS project_name, users.username AS assigned_to, tasks.priority, tasks.status
                                        FROM tasks
                                        JOIN projects ON tasks.project_id = projects.id
                                        JOIN users ON tasks.assigned_to = users.id
                                        WHERE projects.department_id = $department_id");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['task_name']}</td>
                            <td>{$row['project_name']}</td>
                            <td>{$row['assigned_to']}</td>
                            <td>{$row['priority']}</td>
                           
                            <td>{$row['status']}</td>
                            <td>
                                <a href='edit_task.php?id={$row['id']}'><i class='fas fa-edit'></i> Edit</a>
                                <a href='delete_task.php?id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this task?\")'><i class='fas fa-trash'></i> Delete</a>
                            </td>
                          </tr>";
                }
                ?>
            </table>
        </div>
    

        <div id="statistics" class="section" style="display:none;">
            <h2>Statistics</h2>
            <div class="crud-card">
                <h4>Projects</h4>
                <span><?php echo $totalProjects; ?></span>
            </div>
            <div class="crud-card">
                <h4>Tasks</h4>
                <span><?php echo $totalTasks; ?></span>
            </div>
            <div class="crud-card">
                <h4>Team Members</h4>
                <span><?php echo $totalTeamMembers; ?></span>
            </div>
        </div>
    </div>

    <div id="gantt_here_section" class="card section gantt-chart-card" style="display:none;">
    <h4>Gantt Chart</h4>
    <div id="gantt_here"></div>
</div>







<script>
document.addEventListener('DOMContentLoaded', function() {
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
        if (sectionId === 'gantt_here_section') {
            const ganttData = <?php echo json_encode($gantt_data); ?>;
            renderGanttChart(ganttData);
        }
    }
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

    function showModal(type, message) {
        var modal = document.getElementById('customModal');
        var modalContent = document.getElementById('modalContent');
        var modalIcon = document.getElementById('modalIcon');
        var modalMessage = document.getElementById('modalMessage');

        // Set icon and message based on type
        if (type === 'success') {
            modalContent.className = 'modal-content success';
            modalIcon.innerHTML = '<i class="fa fa-check"></i>';
        } else if (type === 'error') {
            modalContent.className = 'modal-content error';
            modalIcon.innerHTML = '<i class="fa fa-times"></i>';
        } else if (type === 'warning') {
            modalContent.className = 'modal-content warning';
            modalIcon.innerHTML = '<i class="fa fa-exclamation-triangle"></i>';
        }

        modalMessage.innerText = message;
        modal.style.display = 'flex';

        // Close the modal when the user clicks on <span> (x)
        document.getElementsByClassName('close')[0].onclick = function() {
            modal.style.display = 'none';
        };

        // Close the modal when the user clicks anywhere outside of the modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    }

    function handleAction(action, projectId) {
        if (confirm(`Are you sure you want to ${action} this project?`)) {
            window.location.href = `project_action.php?id=${projectId}&action=${action}`;
        }
    }

    // Show modal based on session message
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['message'])): ?>
            var message = "<?php echo $_SESSION['message']; ?>";
            var type = "success";
            if (message.includes('Error')) {
                type = "error";
            } else if (message.includes('Invalid')) {
                type = "warning";
            }
            showModal(type, message);
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    });

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification-popup ${type}`;

        const icon = document.createElement('span');
        icon.className = 'icon';
        icon.innerHTML = type === 'success' ? '&#10004;' : '&#10006;'; // Tick or cross icon

        const text = document.createElement('span');
        text.className = 'message';
        text.innerText = message;

        notification.appendChild(icon);
        notification.appendChild(text);

        document.body.appendChild(notification);

        notification.style.display = 'block';
        setTimeout(() => {
            notification.style.display = 'none';
            document.body.removeChild(notification);
        }, 3000); // Show notification for 3 seconds
    }
</script>

</body>
</html>
