<?php
session_start();
echo "User ID: " . $_SESSION['user_id'];
echo "Role: " . $_SESSION['role'];
?>
