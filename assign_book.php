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
    $assignedDate = date('Y-m-d H:i:s');

    if ($_POST['edit_mode'] == '1') {
        $oldStudentName = $_POST['old_student_name'];
        $oldBookName = $_POST['old_book_name'];

        // Delete the old record
        $deleteStmt = $conn->prepare("DELETE FROM student_books WHERE student_name = ? AND book_name = ?");
        $deleteStmt->bind_param("ss", $oldStudentName, $oldBookName);
        $deleteStmt->execute();
    }

    // Kontrol et: Bu kitap bu öğrenciye daha önce atanmış mı?
    $checkSql = "SELECT * FROM student_books WHERE student_name = ? AND book_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ss", $studentName, $bookName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div style='color: red;'>Bu kitap zaten bu öğrenciye atanmış!</div>";
    } else {
        $sql = "INSERT INTO student_books (student_name, book_name, assigned_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $studentName, $bookName, $assignedDate);
        
        if ($stmt->execute()) {
            echo "<div style='color: green;'>Kitap başarıyla atandı!</div>";
        } else {
            echo "<div style='color: red;'>Hata oluştu: " . $stmt->error . "</div>";
        }
    }
    $stmt->close();
}

$conn->close();
?>
