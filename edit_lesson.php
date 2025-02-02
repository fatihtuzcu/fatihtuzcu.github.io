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

    // Fetch the lesson details
    $stmt = $conn->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lesson = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
        if (empty($_POST['student_id']) || empty($_POST['lesson_date']) || 
            empty($_POST['start_time']) || empty($_POST['end_time']) || empty($_POST['fee'])) {
            $alertMessage = 'Lütfen tüm alanları doldurun!';
            $alertType = 'danger';
        } else {
            $student_id = $_POST['student_id'];
            $lesson_date = $_POST['lesson_date'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $fee = (float)$_POST['fee'];
            $status = $_POST['status'];

            // Use the fee directly without calculating based on hours
            $total_fee = $fee;

            $stmt = $conn->prepare("UPDATE lessons SET student_id = ?, lesson_date = ?, start_time = ?, end_time = ?, fee = ?, status = ? WHERE id = ?");
            $stmt->bind_param("isssdsi", $student_id, $lesson_date, $start_time, $end_time, $total_fee, $status, $lesson_id);
            
            if ($stmt->execute()) {
                header("Location: index.php?message=Ders başarıyla güncellendi&alertType=success");
            } else {
                $alertMessage = 'Hata: ' . $conn->error;
                $alertType = 'danger';
            }
        }
    }
} else {
    header("Location: index.php?message=Geçersiz istek&alertType=danger");
}

// Fetch students from users table
$students = $conn->query("SELECT id, studentName FROM users ORDER BY studentName");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ders Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #333;
            margin: 20px 0 40px 0;
            padding-top: 20px;
        }
        form {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group > div {
            padding: 10px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            border-color: #28a745;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container mt-4">
            <h1>Ders Düzenle</h1>
            
            <?php if (isset($alertMessage)): ?>
            <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                <?= $alertMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST" class="bg-white p-4 rounded shadow">
                <div class="form-group">
                    <div>
                        <label for="student_id">Öğrenci:</label>
                        <select name="student_id" id="student_id">
                            <?php while ($student = $students->fetch_assoc()): ?>
                                <option value="<?= $student['id'] ?>" <?= $student['id'] == $lesson['student_id'] ? 'selected' : '' ?>><?= $student['studentName'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="lesson_date">Ders Tarihi:</label>
                        <input type="date" name="lesson_date" id="lesson_date" value="<?= $lesson['lesson_date'] ?>" required>
                    </div>
                    <div>
                        <label for="fee">Ücret:</label>
                        <input type="number" name="fee" id="fee" value="<?= $lesson['fee'] ?>" step="50" min="400" required>
                    </div>
                    <div>
                        <label for="start_time">Başlangıç Saati:</label>
                        <input type="time" name="start_time" id="start_time" value="<?= $lesson['start_time'] ?>" step="300" required>
                    </div>
                    <div>
                        <label for="end_time">Bitiş Saati:</label>
                        <input type="time" name="end_time" id="end_time" value="<?= $lesson['end_time'] ?>" step="300" required>
                    </div>
                    <div>
                        <label for="status">Durum:</label>
                        <select name="status" id="status">
                            <option value="Ödendi" <?= $lesson['status'] == 'Ödendi' ? 'selected' : '' ?>>Ödendi</option>
                            <option value="Ödenecek" <?= $lesson['status'] == 'Ödenecek' ? 'selected' : '' ?>>Ödenecek</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="submit">Güncelle</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
