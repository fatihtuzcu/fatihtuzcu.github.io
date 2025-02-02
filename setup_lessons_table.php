<?php
require 'database.php';

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Drop existing table if needed
$dropTableSQL = "DROP TABLE IF EXISTS lessons";
$conn->query($dropTableSQL);

// Create new table with all required columns
$createTableSQL = "CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    lesson_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    lesson_count INT DEFAULT 1,
    amount_per_lesson DECIMAL(10,2) DEFAULT 400.00,
    fee DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Ödenecek',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id)
)";

if ($conn->query($createTableSQL) === TRUE) {
    echo "Table lessons created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
