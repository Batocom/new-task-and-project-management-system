<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Fetch departments with their managers
$query = "SELECT departments.id as department_id, departments.name as department_name, users.id as manager_id, users.username as manager_name 
          FROM departments 
          LEFT JOIN users ON departments.manager_id = users.id";

$result = $conn->query($query);

$departments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}


// Fetch data for dashboard
$usersQuery = $conn->prepare("SELECT id, username, email, role, department_id FROM users");
$usersQuery->execute();
$usersResult = $usersQuery->get_result();

$singleRowQueries = [
    'totalProjects' => "SELECT COUNT(*) as total FROM projects",
    'totalTasks' => "SELECT COUNT(*) as total FROM tasks",
    'inProgressTasks' => "SELECT COUNT(*) as total FROM tasks WHERE status = 'in_progress'",
    'completedTasks' => "SELECT COUNT(*) as total FROM tasks WHERE status = 'completed'",
    'totalProjectManagers' => "SELECT COUNT(*) as total FROM users WHERE role = 'project_manager'",
    'totalTeamMembers' => "SELECT COUNT(*) as total FROM users WHERE role = 'team_member'",
    'totalDepartments' => "SELECT COUNT(*) as total FROM departments"
];

$data = [];
foreach ($singleRowQueries as $key => $query) {
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $data[$key] = $row['total'];
    } else {
        $data[$key] = 0;
    }
}


// Assign variables
$totalProjects = $data['totalProjects'];
$totalTasks = $data['totalTasks'];
$task_counts = [
    'in_progress' => $data['inProgressTasks'],
    'completed' => $data['completedTasks']
];
$totalProjectManagers = $data['totalProjectManagers'];
$totalTeamMembers = $data['totalTeamMembers'];
$totalDepartments = $data['totalDepartments'];

// Example data for progress and recent activities
$taskProgressPercentage = ($totalTasks > 0) ? (($data['completedTasks'] / $totalTasks) * 100) : 0;
$recentActivitiesResult = $conn->query("SELECT activity_description FROM activities ORDER BY created_at DESC LIMIT 10");

$departmentsQuery = $conn->prepare("SELECT d.id AS department_id, d.name AS department_name, u.id AS manager_id, u.username AS manager_name FROM departments d JOIN users u ON d.manager_id = u.id WHERE u.role='project_manager'");
$departmentsQuery->execute();
$departmentsResult = $departmentsQuery->get_result();

$totalRelevantTasks = $data['inProgressTasks'] + $data['completedTasks'];
$taskProgressPercentage = $totalRelevantTasks > 0 ? ($data['completedTasks'] / $totalRelevantTasks) * 100 : 0;

// Fetch projects and tasks for all departments
$projects = $conn->query("SELECT * FROM projects");
$tasks = $conn->query("SELECT * FROM tasks WHERE project_id IN (SELECT id FROM projects)");

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
    <title>Admin Dashboard</title>
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
        <h2>Admin Dashboard</h2>
        <div class="nav-buttons">
            <a href="profile_edit.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i> <span id="notification-count">3</span></a>
            <form method="POST" action="logout.php" style="display:inline;">
                <button type="submit" class="nav-link" style="background:none;border:none;color:white;cursor:pointer;"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="sidebar">
        <a href="#" onclick="showSection('dashboard')"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        <a href="#" onclick="showSection('create_project')"><i class="fas fa-folder-plus"></i> <span>Create Project</span></a>
        <a href="#" onclick="showSection('create_user')"><i class="fas fa-user-plus"></i> <span>Create User</span></a>
        <a href="#" onclick="showSection('create_department')"><i class="fas fa-building"></i> <span>Create Department</span></a>
        <a href="#" onclick="showSection('manage_users')"><i class="fas fa-users-cog"></i> <span>Manage Users</span></a>
        <a href="#" onclick="showSection('statistics')"><i class="fas fa-chart-bar"></i> <span>Statistics</span></a>
        <a href="#" onclick="showSection('gantt_here_section')"><i class="fa-solid fa-chart-gantt"></i> <span>Gantt Chart</span></a>
    </div>

    <div class="container">
        <h3 class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>

        <div class="status-card">
    <div class="card">
        <h3>Total Projects</h3>
        <p><?php echo $data['totalProjects']; ?></p>
    </div>
    <div class="card">
        <h3>Tasks In Progress</h3>
        <p><?php echo $data['inProgressTasks']; ?></p>
    </div>
    <div class="card">
        <h3>Total Tasks</h3>
        <p><?php echo $data['totalTasks']; ?></p>
    </div>
    <div class="card">
        <h3>Completed Tasks</h3>
        <p><?php echo $data['completedTasks']; ?></p>
    </div>
    <div class="card">
        <h3>Total Project Managers</h3>
        <p><?php echo $data['totalProjectManagers']; ?></p>
    </div>
    <div class="card">
        <h3>Total Team Members</h3>
        <p><?php echo $data['totalTeamMembers']; ?></p>
    </div>
    <div class="card">
        <h3>Total Departments</h3>
        <p><?php echo $data['totalDepartments']; ?></p>
    </div>
</div>

<div id="create_project" class="section" style="display: none;">
<div class="card">
    <h4>Create Project</h4>
    <form method="POST" action="create_project.php">
        <div class="form-group">
            <label for="project_title">Project Title:</label>
            <input type="text" id="project_title" name="title" required>
        </div>
        <div class="form-group">
            <label for="project_description">Description:</label>
            <textarea id="project_description" name="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="expected_due_date">Expected Due Date:</label>
            <input type="date" id="expected_due_date" name="expected_due_date" required>
        </div>
        <div class="form-group">
            <label for="project_department_id">Department:</label>
            <select id="project_department_id" name="department_id" required onchange="updateProjectManager()">
                <option value="">Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo $department['department_id']; ?>" data-manager-id="<?php echo $department['manager_id']; ?>" data-manager-name="<?php echo htmlspecialchars($department['manager_name']); ?>">
                        <?php echo htmlspecialchars($department['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="project_manager_name">Project Manager:</label>
            <input type="text" id="project_manager_name" name="manager_name" readonly>
            <input type="hidden" id="project_manager_id" name="manager_id" required>
        </div>
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

<div id="create_user" class="section" style="display: none;">
<div class="card">
    <h4>Create User</h4>
    <form method="POST" action="create_user.php">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="role">Role:</label>
            <select id="role" name="role" required onchange="toggleDepartmentField(this)">
                <option value="project_manager">Project Manager</option>
                <option value="team_member">Team Member</option>
            </select>
        </div>
        <div id="departmentField" class="form-group" style="display: none;">
            <label for="department_id">Department:</label>
            <select id="department_id" name="department_id">
                <option value="">Select Department</option>
                <?php
                // Fetch all departments
                $departments = $conn->query("SELECT id, name FROM departments");
                while ($row = $departments->fetch_assoc()) {
                    echo "<option value=\"{$row['id']}\">" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Create User</button>
    </form>
            </div>
</div>



        

<div id="create_department" class="section" style="display: none;">
<div class="card">
    <h4>Create Department</h4>
    <form method="POST" action="create_department.php">
        <div class="form-group">
            <label for="department_name">Department Name:</label>
            <input type="text" id="department_name" name="department_name" required>
        </div>
        <div class="form-group">
            <label for="manager_id">Assign Project Manager:</label>
            <select id="manager_id" name="manager_id" required onchange="checkCurrentDepartment(this.value)">
                <option value="">Select User</option>
                <?php
                // Fetch users with roles "project_manager" or "team_member"
                $users = $conn->query("SELECT id, username, role FROM users WHERE role IN ('project_manager', 'team_member')");
                while ($row = $users->fetch_assoc()) {
                    echo "<option value=\"{$row['id']}\" data-role=\"{$row['role']}\">" . htmlspecialchars($row['username']) . " ({$row['role']})</option>";
                }
                ?>
            </select>
        </div>
        <div id="replacement_section" class="form-group" style="display: none;">
            <label for="replacement_manager_id">Replacement Project Manager for Previous Department:</label>
            <select id="replacement_manager_id" name="replacement_manager_id">
                <option value="">Select User</option>
                <?php
                // Fetch users with roles "project_manager" or "team_member"
                $replacement_users = $conn->query("SELECT id, username, role FROM users WHERE role IN ('project_manager', 'team_member')");
                while ($row = $replacement_users->fetch_assoc()) {
                    echo "<option value=\"{$row['id']}\" data-role=\"{$row['role']}\">" . htmlspecialchars($row['username']) . " ({$row['role']})</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn">Create Department</button>
    </form>
            </div>
</div>



<!-- Manage Users Section -->
<div id="manage_users" class="section" style="display:none;">
<div class="card">
    <h2>Manage Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
        <?php
        $users_per_page = isset($_GET['users_per_page']) ? (int)$_GET['users_per_page'] : 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $users_per_page;

        // Fetch users data with pagination
        $usersResult = $conn->query("SELECT users.id, users.username, users.email, users.role, departments.name AS department_name 
                                     FROM users 
                                     LEFT JOIN departments ON users.department_id = departments.id
                                     LIMIT $offset, $users_per_page");

        $totalUsersResult = $conn->query("SELECT COUNT(*) AS total FROM users");
        $totalUsers = $totalUsersResult->fetch_assoc()['total'];
        $total_pages = ceil($totalUsers / $users_per_page);

        while ($user = $usersResult->fetch_assoc()) {
            echo "<tr>
                    <td>{$user['id']}</td>
                    <td>{$user['username']}</td>
                    <td>{$user['email']}</td>
                    <td>{$user['role']}</td>
                    <td>{$user['department_name']}</td>
                    <td>
                        <a href='edit_user.php?id={$user['id']}'><i class='fas fa-edit'></i> Edit</a> |
                        <a href='delete_user.php?id={$user['id']}' onclick='return confirm(\"Are you sure you want to delete this user?\")'><i class='fas fa-trash'></i> Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&users_per_page=<?php echo $users_per_page; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <div class="user-count">
        <form method="GET" action="">
            <label for="users_per_page">Users per page:</label>
            <select name="users_per_page" id="users_per_page" onchange="this.form.submit()">
                <option value="10" <?php if ($users_per_page == 10) echo 'selected'; ?>>10</option>
                <option value="20" <?php if ($users_per_page == 20) echo 'selected'; ?>>20</option>
                <option value="50" <?php if ($users_per_page == 50) echo 'selected'; ?>>50</option>
            </select>
        </form>
    </div>
        </div>
</div>


<div id="statistics" class="section" style="display:none;">
    <h2>Statistics</h2>
    <div class="statistics-grid">
        <div class="stat-card" onclick="showProjects()">
            <i class="fas fa-project-diagram stat-icon"></i>
            <h3>Total Projects</h3>
            <p><?php echo $totalProjects; ?></p>
        </div>
        <div class="stat-card" onclick="showTasks()">
            <i class="fas fa-tasks stat-icon"></i>
            <h3>Total Tasks</h3>
            <p><?php echo $totalTasks; ?></p>
        </div>
        <div class="stat-card" onclick="showTasksInProgress()">
            <i class="fas fa-tasks stat-icon"></i>
            <h3>Tasks In Progress</h3>
            <p><?php echo $task_counts['in_progress']; ?></p>
        </div>
        <div class="stat-card" onclick="showCompletedTasks()">
            <i class="fas fa-check-circle stat-icon"></i>
            <h3>Completed Tasks</h3>
            <p><?php echo $task_counts['completed']; ?></p>
        </div>
        <div class="stat-card" onclick="showProjectManagers()">
            <i class="fas fa-user-tie stat-icon"></i>
            <h3>Project Managers</h3>
            <p><?php echo $totalProjectManagers; ?></p>
        </div>
        <div class="stat-card" onclick="showTeamMembers()">
            <i class="fas fa-users stat-icon"></i>
            <h3>Team Members</h3>
            <p><?php echo $totalTeamMembers; ?></p>
        </div>
        <div class="stat-card" onclick="showDepartments()">
            <i class="fas fa-building stat-icon"></i>
            <h3>Departments</h3>
            <p><?php echo $totalDepartments; ?></p>
        </div>
    </div>
    <div class="progress-bar-container">
        <h4>Task Progress</h4>
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo $taskProgressPercentage; ?>%;"></div>
        </div>
        <p><?php echo round($taskProgressPercentage, 2); ?>% Complete</p>
    </div>
    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
    <!-- Sections to display detailed information -->
    <div id="detailedInfo" class="detailed-info-section" style="display: none;"></div>
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


function updateProjectManager() {
    var departmentSelect = document.getElementById("project_department_id");
    var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
    var managerId = selectedOption.getAttribute("data-manager-id");
    var managerName = selectedOption.getAttribute("data-manager-name");

    document.getElementById("project_manager_id").value = managerId;
    document.getElementById("project_manager_name").value = managerName;
}


function toggleDepartmentField(selectElement) {
    var departmentField = document.getElementById("departmentField");
    if (selectElement.value === "team_member") {
        departmentField.style.display = "block";
    } else {
        departmentField.style.display = "none";
    }
}


function checkCurrentDepartment(managerId) {
    // Make an AJAX call to check if the selected manager is heading another department
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "check_manager.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.isHeading) {
                // Show the replacement section if the manager is heading another department
                document.getElementById("replacement_section").style.display = "block";
            } else {
                document.getElementById("replacement_section").style.display = "none";
            }
        }
    };
    xhr.send("manager_id=" + managerId);
}

function showProjects() {
    console.log('showProjects called');
    fetchDetails('projects');
}

function showTasks() {
    console.log('showTasks called');
    fetchDetails('tasks');
}

function showTasksInProgress() {
    console.log('showTasksInProgress called');
    fetchDetails('tasks_in_progress');
}

function showCompletedTasks() {
    console.log('showCompletedTasks called');
    fetchDetails('completed_tasks');
}

function showProjectManagers() {
    console.log('showProjectManagers called');
    fetchDetails('project_managers');
}

function showTeamMembers() {
    console.log('showTeamMembers called');
    fetchDetails('team_members');
}

function showDepartments() {
    console.log('showDepartments called');
    fetchDetails('departments');
}

function fetchDetails(type) {
    console.log('fetchDetails called with type:', type);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_details.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (this.readyState === XMLHttpRequest.DONE) {
            console.log('AJAX request completed with status:', this.status);
            if (this.status === 200) {
                console.log('Response:', this.responseText);
                document.getElementById('detailedInfo').innerHTML = this.responseText;
                document.getElementById('detailedInfo').style.display = 'block';
            } else {
                console.error('Error fetching details:', this.statusText);
            }
        }
    };
    xhr.send('type=' + type);
}

                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    title: { text: "Task Progress" },
                    data: [{
                        type: "doughnut",
                        startAngle: 60,
                        indexLabelFontSize: 17,
                        dataPoints: [
                            { y: <?php echo $data['completedTasks']; ?>, label: "Completed Tasks" },
                            { y: <?php echo $data['inProgressTasks']; ?>, label: "In Progress Tasks" }
                        ]
                    }]
                });
                chart.render();
                
            </script>
        </div>
    </div>
</body>
</html>
