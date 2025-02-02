<?php
session_start();

require 'database.php';

// Initialize connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

// Check if admin
if ($username === "admin" && $password === "admin123") { // Change this to your actual admin credentials
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin.php");
    exit();
}

// Check if student
$stmt = $conn->prepare("SELECT studentName, password FROM users WHERE studentName = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['student_logged_in'] = true;
    $_SESSION['student_name'] = $username;
    header("Location: student.php");
    exit();
} else {
    header("Location: index.php?error=1");
    exit();
}

$stmt->close();
$conn->close();
