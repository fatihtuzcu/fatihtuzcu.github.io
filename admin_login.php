<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === "fth" && $password === "mfd432648.") {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
    } else {
        echo "Geçersiz kullanıcı adı veya şifre.";
    }
} else {
    echo "Geçersiz istek.";
}
?>
