<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = htmlspecialchars($_POST['studentId']);

    require 'database.php';
    if ($conn->connect_error) {
        die("Bağlantı hatası: " . $conn->connect_error);
    }

    $sql = "SELECT studentName, class, parentPhone, city FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Öğrenci Adı: " . $row["studentName"] . "<br>";
        echo "Sınıf: " . $row["class"] . "<br>";
        echo "Veli Telefon: " . $row["parentPhone"] . "<br>";
        echo "Şehir: " . $row["city"] . "<br>";
    } else {
        echo "Kayıt bulunamadı.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Geçersiz istek.";
}
?>
