<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Erişim reddedildi. Lütfen giriş yapın.");
}

require 'database.php';

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $lesson_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);

    if ($stmt->execute()) {
        header("Location: index.php?message=Ders başarıyla silindi&alertType=success");
    } else {
        header("Location: index.php?message=Hata: " . $conn->error . "&alertType=danger");
    }
} else {
    header("Location: index.php?message=Geçersiz istek&alertType=danger");
}
?>
