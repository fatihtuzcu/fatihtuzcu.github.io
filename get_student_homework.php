<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Erişim reddedildi.");
}

require 'database.php';

// Initialize connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$student = $_GET['student'] ?? '';

if (empty($student)) {
    die(json_encode([]));
}

$sql = "SELECT book_name, homework_desc, due_date, status FROM homework WHERE student_name = ? ORDER BY due_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student);
$stmt->execute();
$result = $stmt->get_result();

$homework = [];
while ($row = $result->fetch_assoc()) {
    $homework[] = $row;
}

echo json_encode($homework);

$stmt->close();
$conn->close();
