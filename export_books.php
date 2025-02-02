<?php
session_start();
require 'database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Erişim reddedildi. Lütfen giriş yapın.");
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="kitaplar.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Turkish character encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Get books from database
$query = "SELECT book_name, difficulty FROM kitaplar ORDER BY created_at DESC";
$result = $conn->query($query);

// Output each book as a CSV line
while ($row = $result->fetch_assoc()) {
    fputcsv($output, array($row['book_name'], $row['difficulty']));
}

fclose($output);
$conn->close();
