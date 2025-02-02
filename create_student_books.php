<?php
require 'database.php';

// Initialize connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// First add index to users table
$addIndexSql = "ALTER TABLE users ADD INDEX idx_studentName (studentName)";
$conn->query($addIndexSql);

// Then create student_books table
$sql = "CREATE TABLE IF NOT EXISTS student_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    book_name VARCHAR(255) NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date DATE,
    status ENUM('assigned', 'returned') DEFAULT 'assigned',
    INDEX (student_name),
    FOREIGN KEY (student_name) REFERENCES users(studentName)
)";

if ($conn->query($sql) === TRUE) {
    echo "student_books tablosu başarıyla oluşturuldu";
} else {
    echo "Tablo oluşturma hatası: " . $conn->error;
}

$conn->close();
?>
