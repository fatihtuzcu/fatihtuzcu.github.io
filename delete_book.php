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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
    $studentName = $conn->real_escape_string($_POST['student_name']);
    $bookName = $conn->real_escape_string($_POST['book_name']);

    $stmt = $conn->prepare("DELETE FROM student_books WHERE student_name = ? AND book_name = ?");
    $stmt->bind_param("ss", $studentName, $bookName);

    if ($stmt->execute()) {
        echo "Kitap başarıyla silindi.";
    } else {
        echo "Hata: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
