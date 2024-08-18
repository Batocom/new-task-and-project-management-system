-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2024 at 09:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `new task and project management system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `activity_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `activity_description`, `created_at`) VALUES
(1, 'Created a new project', '2024-07-17 20:34:45'),
(2, 'Added a new user', '2024-07-17 20:34:45'),
(3, 'Updated task status to in progress', '2024-07-17 20:34:45'),
(4, 'Completed a task', '2024-07-17 20:34:45'),
(5, 'Assigned a task to a team member', '2024-07-17 20:34:45');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `manager_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `manager_id`) VALUES
(1, 'IT', 6),
(2, 'cloud', 1),
(3, 'devops', 3),
(4, 'cloud', 1),
(5, 'network and security', 9),
(6, 'IT Support', 11),
(10, 'Service Desk', 10),
(11, 'IT Entreprice', 13),
(12, 'IT Entreprice', 15),
(13, 'civil works', 10);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `project_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `status`, `created_at`, `project_id`, `task_id`) VALUES
(1, 7, 'Task status updated.', 'unread', '2024-07-15 13:24:17', NULL, NULL),
(2, 7, 'Task status updated.', 'unread', '2024-07-15 13:25:00', NULL, NULL),
(3, 7, 'Task status updated.', 'unread', '2024-07-15 13:25:15', NULL, NULL),
(4, 9, 'Task status updated.', 'unread', '2024-07-15 13:30:20', NULL, NULL),
(5, 8, 'Task status updated.', 'read', '2024-07-17 06:59:32', NULL, NULL),
(6, 8, 'Task status updated.', 'read', '2024-07-17 07:00:34', NULL, NULL),
(7, 8, 'Task status updated.', 'unread', '2024-07-17 07:00:49', NULL, NULL),
(8, 8, 'Task status updated.', 'unread', '2024-07-17 07:59:18', NULL, NULL),
(9, 7, 'Task status updated.', 'unread', '2024-07-17 12:33:07', NULL, NULL),
(10, 7, 'Task status updated.', 'unread', '2024-07-17 12:33:19', NULL, NULL),
(11, 5, 'You have been assigned as the manager for the new project: ERP.', 'unread', '2024-07-17 13:21:27', 8, NULL),
(12, 8, 'You have been assigned as the manager for the new project: ERP.', 'unread', '2024-07-17 13:21:27', NULL, NULL),
(13, 8, 'You have been assigned a new task: Manage all workers.', 'unread', '2024-07-17 13:26:03', 19, 28),
(14, 8, 'Task status updated.', 'unread', '2024-07-17 13:27:34', NULL, NULL),
(15, 4, 'You have been assigned as the manager for the new project: install antiviruses to all computers.', 'unread', '2024-07-17 19:55:56', 9, NULL),
(16, 7, 'You have been assigned as the manager for the new project: install antiviruses to all computers.', 'read', '2024-07-17 19:55:56', 6, NULL),
(17, 7, 'You have been assigned a new task: install to people in icm.', 'read', '2024-07-17 19:58:01', 9, 22),
(18, 7, 'Task status updated.', 'read', '2024-07-17 20:00:07', NULL, NULL),
(19, 3, 'You have been assigned as the manager for the new project: digitize the ceo office.', 'unread', '2024-07-18 08:02:11', 10, NULL),
(20, 9, 'You have been assigned as the manager for the new project: digitize the ceo office.', 'unread', '2024-07-18 08:02:11', 2, NULL),
(21, 10, 'You have been assigned as the manager for the new project: digitize the ceo office.', 'unread', '2024-07-18 08:02:11', NULL, NULL),
(22, 11, 'You have been assigned as the manager for the new project: digitize the ceo office.', 'unread', '2024-07-18 08:02:11', NULL, NULL),
(23, 10, 'You have been assigned a new task: create a digital signature for all documents.', 'unread', '2024-07-18 08:06:05', 7, 23),
(24, 6, 'You have been assigned as the manager for the new project: create hr system.', 'unread', '2024-07-18 08:08:25', 11, NULL),
(25, 9, 'You have been assigned as the manager for the new project: create hr system.', 'read', '2024-07-18 08:08:25', 2, NULL),
(26, 10, 'You have been assigned as the manager for the new project: create hr system.', 'unread', '2024-07-18 08:08:25', NULL, NULL),
(27, 11, 'You have been assigned as the manager for the new project: create hr system.', 'unread', '2024-07-18 08:08:25', NULL, NULL),
(28, 11, 'You have been assigned a new task: design the hr user interface.', 'unread', '2024-07-18 08:09:46', 11, 24),
(29, 11, 'Task status updated.', 'unread', '2024-07-18 08:11:17', NULL, NULL),
(30, 11, 'You have been assigned a new task: Manage all workers.', 'unread', '2024-07-18 08:19:02', 11, 24),
(31, 11, 'Task status updated.', 'unread', '2024-07-18 08:19:23', NULL, NULL),
(32, 11, 'Task status updated.', 'unread', '2024-07-18 08:28:42', NULL, NULL),
(33, 15, 'You have been assigned a new project: improve our services.', 'unread', '2024-07-20 20:14:34', NULL, NULL),
(34, 15, 'You have been assigned a new project: improve our services.', 'unread', '2024-07-20 20:14:42', NULL, NULL),
(35, 15, 'You have been assigned a new project: improve our services.', 'unread', '2024-07-20 20:15:46', NULL, NULL),
(36, 15, 'You have been assigned a new project: improve our services.', 'unread', '2024-07-20 20:16:10', NULL, NULL),
(37, 7, 'Task status updated.', 'read', '2024-07-22 06:45:13', NULL, NULL),
(38, 6, 'You have been assigned a new project: advice ceo.', 'unread', '2024-07-22 08:47:45', 11, NULL),
(39, 7, 'A new project has been assigned to your department: advice ceo.', 'read', '2024-07-22 08:47:45', 6, NULL),
(40, 7, 'A project you were part of has been disabled.', 'read', '2024-07-22 10:12:43', 6, NULL),
(41, 7, 'A project you were part of has been disabled.', 'read', '2024-07-22 10:12:43', 6, NULL),
(42, 7, 'A project you were part of has been disabled.', 'read', '2024-07-22 10:13:20', 6, NULL),
(43, 7, 'A project you were part of has been disabled.', 'read', '2024-07-22 10:13:20', 6, NULL),
(44, 5, 'You have been assigned a new project: onboard the new client.', 'unread', '2024-07-22 13:05:41', 8, NULL),
(45, 8, 'A new project has been assigned to your department: onboard the new client.', 'read', '2024-07-22 13:05:41', NULL, NULL),
(46, 8, 'You have been assigned a new task: partiton the vm.', 'read', '2024-07-22 16:27:40', 19, 28),
(47, 9, 'A project you were part of has been disabled.', 'read', '2024-07-22 20:47:34', 2, NULL),
(48, 8, 'A project you were part of has been disabled. Click <a href=\'view_details.php?project_id=2\'>here</a> to view details.', 'read', '2024-07-22 20:47:34', NULL, NULL),
(49, 8, 'You have been assigned a new task: install wifi 6.', 'read', '2024-07-22 20:55:53', 19, 28),
(50, 8, 'You have been assigned a new task: design the hr user interface.', 'read', '2024-07-22 20:56:41', 19, 28),
(51, 8, 'Task status updated.', 'read', '2024-07-23 10:35:25', NULL, NULL),
(52, 8, 'Task status updated.', 'read', '2024-07-23 11:14:12', NULL, NULL),
(53, 10, 'You have been assigned a new project: improve sales.', 'read', '2024-07-23 17:49:38', 21, NULL),
(54, 7, 'Task status updated.', 'read', '2024-07-24 06:45:24', NULL, NULL),
(55, 7, 'Task status updated.', 'unread', '2024-07-24 06:48:38', NULL, NULL),
(56, 7, 'Task status updated.', 'read', '2024-07-24 06:48:59', NULL, NULL),
(57, 9, 'A project you were part of has been enabled.', 'read', '2024-07-25 08:21:06', 2, NULL),
(58, 8, 'A project you were part of has been enabled.', 'unread', '2024-07-25 08:21:06', 2, NULL),
(59, 3, 'A project you were part of has been disabled.', 'unread', '2024-07-25 08:21:45', 4, NULL),
(60, 9, 'A project you were part of has been disabled. Click <a href=\'view_notification.php?project_id=4&task_id=\'>here</a> to view details.', 'read', '2024-07-25 08:21:45', 4, NULL),
(61, 9, 'You have been assigned a new task: create analytics.', 'unread', '2024-08-10 14:35:56', NULL, NULL),
(62, 11, 'You have been assigned a new project: road construction.', 'unread', '2024-08-15 16:36:41', 22, NULL),
(63, 10, 'A new project has been assigned to your department: road construction.', 'unread', '2024-08-15 16:36:41', 22, NULL),
(64, 11, 'You have been assigned a new project: simplify filling process.', 'unread', '2024-08-16 04:50:50', 23, NULL),
(65, 10, 'You have been assigned a new project: shamata highway construction.', 'unread', '2024-08-16 07:25:28', 24, NULL),
(66, 12, 'A new project has been assigned to your department: shamata highway construction.', 'unread', '2024-08-16 07:25:28', 24, NULL),
(67, 13, 'A new project has been assigned to your department: shamata highway construction.', 'unread', '2024-08-16 07:25:28', 24, NULL),
(68, 13, 'You have been assigned a new task: prepare a budget for all the required materials.', 'read', '2024-08-16 07:31:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','pending') DEFAULT 'not_started',
  `description` varchar(255) DEFAULT NULL,
  `state` enum('active','disabled') DEFAULT 'active',
  `disable_description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expected_due_date` date NOT NULL DEFAULT '2024-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `department_id`, `manager_id`, `status`, `description`, `state`, `disable_description`, `created_at`, `expected_due_date`) VALUES
(1, 'automate ceo office', 2, 2, 'not_started', 'make all processes seamless', 'active', NULL, '2024-07-23 16:49:18', '2024-04-02'),
(2, 'automate booking process', 3, 9, 'not_started', 'make booking seamless', 'active', NULL, '2024-07-23 16:49:18', '2024-11-06'),
(3, 'improve network speed', 2, 2, 'not_started', 'make it fast', 'active', NULL, '2024-07-23 16:49:18', '2024-06-23'),
(4, 'digitize booking process', 3, 3, 'not_started', 'make it easy', 'disabled', 'lack of resources', '2024-07-23 16:49:18', '2024-07-25'),
(5, 'add a dashboard ', 3, 3, 'not_started', 'make the ticketing system dashboard', 'active', NULL, '2024-07-23 16:49:18', '2024-01-16'),
(6, 'create a website', 1, 7, 'in_progress', 'a website for our organisation', 'disabled', NULL, '2024-07-23 16:49:18', '2024-03-21'),
(7, 'digidashboard', 3, 6, 'not_started', 'create a tool to level of digitization', 'active', NULL, '2024-07-23 16:49:18', '2024-07-25'),
(8, 'ERP', 2, 5, 'not_started', 'CREATE AN ENTRIPRICE RESOURCE PLANNING SYSTEM', 'active', NULL, '2024-07-23 16:49:18', '2024-01-03'),
(9, 'install antiviruses to all computers', 1, 4, 'not_started', 'protect the organisations computers from being affected by viruses', 'active', NULL, '2024-07-23 16:49:18', '2024-10-24'),
(10, 'digitize the ceo office', 3, 3, 'not_started', 'make all operations in the ceo office seamless', 'active', NULL, '2024-07-23 16:49:18', '2024-12-06'),
(11, 'create hr system', 3, 6, 'not_started', 'automate all hr processes', 'active', NULL, '2024-07-23 16:49:18', '2024-09-12'),
(12, 'improve our services', 12, NULL, 'not_started', 'make access to our services seamless', 'active', NULL, '2024-07-23 16:49:18', '2024-08-16'),
(13, 'improve our services', 12, NULL, 'not_started', 'make access to our services seamless', 'active', NULL, '2024-07-23 16:49:18', '2024-08-13'),
(14, 'improve our services', 12, NULL, 'not_started', 'make access to our services seamless', 'active', NULL, '2024-07-23 16:49:18', '2024-12-22'),
(15, 'improve our services', 12, NULL, 'not_started', 'make access to our services seamless', 'active', NULL, '2024-07-23 16:49:18', '2024-12-12'),
(16, 'advice ceo', 1, NULL, 'not_started', 'make him uderstand', 'active', NULL, '2024-07-23 16:49:18', '2024-10-07'),
(17, 'advice ceo', 3, NULL, 'not_started', 'make him aware', 'active', NULL, '2024-07-23 16:49:18', '2024-10-04'),
(18, 'advice ceo', 3, NULL, 'not_started', 'make him aware', 'disabled', 'the budget was cancelled', '2024-07-23 16:49:18', '2024-03-14'),
(19, 'onboard the new client', 2, NULL, 'not_started', 'allocate cloud resources to the new client', 'disabled', 'there is currently limitted resources', '2024-07-23 16:49:18', '2024-09-06'),
(20, 'onboard the new client', 2, NULL, 'not_started', 'allocate cloud resources to the new client', 'active', NULL, '2024-07-23 16:49:18', '2024-03-09'),
(21, 'improve sales', 10, NULL, 'not_started', 'make more reachout', 'active', NULL, '2024-07-23 19:49:38', '2024-08-06'),
(22, 'road construction', 6, NULL, 'not_started', 'leveling\r\nconstruction', 'active', NULL, '2024-08-15 18:36:41', '2024-08-27'),
(23, 'simplify filling process', 6, NULL, 'not_started', 'make it for the staff to compile their work for submission for audit', 'active', NULL, '2024-08-16 06:50:50', '2024-08-23'),
(24, 'shamata highway construction', 13, NULL, 'not_started', 'construct a termarck road to join shamata town and nairobi road', 'active', NULL, '2024-08-16 09:25:28', '2024-09-07');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `priority` enum('high','medium','low') DEFAULT 'medium',
  `status` enum('assigned_but_not_started','in_progress','completed','pending') DEFAULT 'assigned_but_not_started',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `progress` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `name`, `description`, `project_id`, `assigned_to`, `priority`, `status`, `due_date`, `created_at`, `updated_at`, `progress`) VALUES
(1, 'digitize booking process', 'make it seamless', NULL, 8, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(2, 'digitize booking process', 'make it seamless', NULL, 7, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(4, 'FRANCIS', 'smile', 1, 8, '', 'completed', NULL, '2024-07-15 06:09:54', '2024-07-23 11:14:12', 100),
(5, 'add e-signature', 'add an e-signature to all documents going through the office of the ceo', 1, 9, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(6, 'modify request', 'make it simple', 7, 9, '', 'in_progress', NULL, '2024-07-15 06:09:54', '2024-07-15 13:30:20', 2),
(7, 'digitize filing process', 'make all filing online for ease of access and reachability', 1, 7, '', 'in_progress', NULL, '2024-07-15 06:09:54', '2024-07-24 06:48:59', 86),
(8, 'digitize filing process', 'make all filing online for ease of access and reachability', 1, 7, '', 'in_progress', NULL, '2024-07-15 06:09:54', '2024-07-15 13:25:15', 19),
(9, 'digitize filing process', 'make all filing online for ease of access and reachability', NULL, 7, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(10, 'digitize booking process', 'should be better', NULL, 7, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(11, 'digitize booking process', 'should be better', NULL, 7, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(13, 'add an access point in office', 'ensure every office has an access point', 3, 15, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(14, 'add an access point in office', 'ensure every office has one', 3, 15, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(15, 'allow self selection', 'make it possible', 7, 8, '', 'in_progress', NULL, '2024-07-15 06:09:54', '2024-07-25 21:30:13', 9),
(16, 'allow self selection', 'let people assign themselves', 4, 9, '', 'assigned_but_not_started', NULL, '2024-07-15 06:09:54', '2024-07-15 06:09:54', 0),
(18, 'add an access point in office', 'make it easy', 6, 7, '', 'pending', NULL, '2024-07-15 06:09:54', '2024-07-25 21:25:12', 3),
(19, 'digitize filing process', 'make it simple', 2, 8, '', 'in_progress', NULL, '2024-07-15 06:09:54', '2024-08-10 17:56:51', 9),
(20, 'install wifi 6', 'make it fast', 1, 8, 'medium', 'pending', '2024-07-24', '2024-07-17 07:57:59', '2024-07-17 07:59:18', 0),
(21, 'Manage all workers', 'allow hr to manage all workers from a central point', 8, 8, 'medium', 'pending', '2024-07-31', '2024-07-17 13:26:03', '2024-07-17 13:27:34', 0),
(22, 'install to people in icm', 'report to icm tomorrow to install kaspersky antivirus to our stuff there.', 9, 7, 'medium', 'in_progress', '2024-07-18', '2024-07-17 19:58:01', '2024-07-17 20:00:07', 2),
(23, 'create a digital signature for all documents', 'make it possible to sign electronicaly', 7, 10, 'medium', 'assigned_but_not_started', '2024-07-25', '2024-07-18 08:06:05', '2024-07-18 08:06:05', 0),
(24, 'design the hr user interface', 'create an easy to use user interface', 11, 11, 'high', 'completed', '2024-08-01', '2024-07-18 08:09:46', '2024-07-18 08:28:42', 4),
(26, 'partiton the vm', 'partition the vm memory into two disks 400gb and 600gb', 19, 8, 'medium', 'assigned_but_not_started', '2024-07-23', '2024-07-22 16:27:40', '2024-07-22 16:27:40', 0),
(27, 'install wifi 6', 'yeah make it good', 19, 8, 'medium', 'assigned_but_not_started', '2024-07-23', '2024-07-22 20:55:53', '2024-07-22 20:55:53', 0),
(28, 'design the hr user interface', 'yeah make it rock', 19, 8, 'low', 'assigned_but_not_started', '2024-07-23', '2024-07-22 20:56:41', '2024-07-22 20:56:41', 0),
(29, 'create analytics', 'vjrhkfjljfkjrtb', 4, 9, 'medium', 'assigned_but_not_started', '2024-08-13', '2024-08-10 14:35:56', '2024-08-10 14:35:56', 0),
(30, 'prepare a budget for all the required materials', 'create a very detailed report on all the requirements for all the materials that will be used in the project', 24, 13, 'medium', 'assigned_but_not_started', '2024-08-24', '2024-08-16 07:31:38', '2024-08-16 07:31:38', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','project_manager','team_member') NOT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `department_id`) VALUES
(1, 'admin1', 'admin1@example.com', '$2y$10$PTwV99kxZpm1r/E5ONg8KeK465gf1WR95C61V8pW72F.iPbb1rLgC', 'admin', NULL),
(2, 'admin2', 'admin2@example.com', '$2y$10$EtJZkNKhvpf.wKDIDBp92ewIXR5uYkNrX/VJr.VqBaytbtR9auWcy', 'admin', NULL),
(3, 'admin3', 'admin3@example.com', '$2y$10$ENaCVulEMOwm8WHy20nbBuciwRuFSdsPbgMMjioodZM.R8YdFblEm', 'admin', NULL),
(4, 'pm1', 'pm1@example.com', '$2y$10$STxYrfXEQY7d08bk/2skXeQ.EKTHbz5.03YrjU2vi6UJ8hwBfS/3K', 'project_manager', 1),
(5, 'pm2', 'pm2@example.com', '$2y$10$7bCuOAoG1peNpTNbT7zAV.3lpkNhlGX82PA0pu8n3XZFM9GxjJQyK', 'project_manager', 2),
(6, 'pm3', 'pm3@example.com', '$2y$10$couI04./hVbd2ICIjlW4s.6sRQlMqdeczr2YxMSY4hySdwyUTGFY2', 'project_manager', 3),
(7, 'tm1', 'tm1@example.com', '$2y$10$0wjNQ/xXj0UGxon0HtRST.Lx2g6WMcDDd3qMglde54krNpECAI6jW', 'team_member', 1),
(8, 'tm2', 'tm2@example.com', '$2y$10$pXfkn6Q1R1CwfiyK.qAhLOxhpMn24tY91aNYR0PmxDRScantEcjc2', 'team_member', 2),
(9, 'tm3', 'tm3@example.com', '$2y$10$EG0jw1ZMXeJ5.4haXvvE/uxi9/NQk3GyiViQygEU9qSULSCrP9IRu', 'project_manager', 3),
(10, 'francis muriithi', 'francismuriithi38@gmail.com', '$2y$10$nRTOUqCF7uTaeY6GIVEDzOdt1jVI9qSax4y.PFX7XBn2nElv24Q4a', 'project_manager', 6),
(11, 'Collins Ogonda', 'collinsogonda@gmail.com', 'pmpass', 'project_manager', 11),
(12, 'Dedan Kiroto', 'dedankariuki1259@gmail.com', '$2y$10$ZnyMOa/YjxXo.eKr7mZs.uEZooSvyxzFfEg0BPKkkfHULW.oRDivC', 'project_manager', 13),
(13, 'joshua kim', 'jkim@gmail.com', '$2y$10$qB1mU3.TH/2ZbcewXfD9zepyhBETaZIPL2RPIfePZCzXSfVLYP2Pu', 'team_member', 13);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
