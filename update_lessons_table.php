<?php
require 'database.php';

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$alterTableSQL = "ALTER TABLE lessons 
    ADD COLUMN lesson_count INT DEFAULT 1,
    ADD COLUMN amount_per_lesson DECIMAL(10,2) DEFAULT 400.00";

if ($conn->query($alterTableSQL) === TRUE) {
    echo "Table lessons updated successfully";
} else {
    echo "Error updating table: " . $conn->error;
}

$conn->close();
?>
