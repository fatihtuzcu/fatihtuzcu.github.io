<?php
session_start();
require 'database.php';

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("UPDATE lessons SET archived = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['alertMessage'] = 'Ders başarıyla arşivlendi.';
        $_SESSION['alertType'] = 'success';
    } else {
        $_SESSION['alertMessage'] = 'Hata oluştu: ' . $conn->error;
        $_SESSION['alertType'] = 'danger';
    }
}

header("Location: ucret.php");
exit();
