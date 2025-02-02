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

$studentName = $conn->real_escape_string($_GET['student']);

$sql = "SELECT book_name FROM student_books WHERE student_name = '$studentName' AND status = 'assigned'";
$result = $conn->query($sql);

$books = array();
while($row = $result->fetch_assoc()) {
    $books[] = $row;
}

header('Content-Type: application/json');
echo json_encode($books);

$conn->close();
?>
