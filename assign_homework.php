<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Erişim reddedildi");
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = $conn->real_escape_string($_POST['studentName']);
    $bookName = $conn->real_escape_string($_POST['bookName']);
    $homeworkDesc = $conn->real_escape_string($_POST['homeworkDesc']);
    $dueDate = $conn->real_escape_string($_POST['dueDate']);
    
    $sql = "INSERT INTO homework (student_name, book_name, homework_desc, due_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("<div style='color: red;'>Prepare failed: " . $conn->error . "</div>");
    }
    
    $stmt->bind_param("ssss", $studentName, $bookName, $homeworkDesc, $dueDate);
    
    if ($stmt->execute()) {
        echo "<div style='color: green;'>Ödev başarıyla atandı!</div>";
    } else {
        echo "<div style='color: red;'>Hata oluştu: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
}

$conn->close();
?>
