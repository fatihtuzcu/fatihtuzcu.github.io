<?php
require 'database.php';

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if columns exist
$checkColumns = $conn->query("SHOW COLUMNS FROM lessons LIKE 'lesson_count'");
if ($checkColumns->num_rows == 0) {
    // Add the missing columns
    $alterSQL = "ALTER TABLE lessons 
        ADD COLUMN lesson_count INT DEFAULT 1,
        ADD COLUMN amount_per_lesson DECIMAL(10,2) DEFAULT 400.00";
    
    if ($conn->query($alterSQL)) {
        echo "Successfully added missing columns";
    } else {
        echo "Error adding columns: " . $conn->error;
    }
} else {
    echo "Columns already exist";
}

$conn->close();
?>
