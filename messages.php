<?php
session_start();
require 'database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add debug log function
function debug_log($message) {
    error_log(print_r($message, true));
}

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete message request
if (isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];
    $delete_stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $delete_stmt->bind_param("i", $message_id);
    $delete_stmt->execute();
    header("Location: messages.php");
    exit();
}

// Handle edit message request
if (isset($_POST['edit_message'])) {
    $message_id = $_POST['message_id'];
    $message_content = $_POST['message_content'];

    $update_stmt = $conn->prepare("UPDATE messages SET message = ? WHERE id = ?");
    $update_stmt->bind_param("si", $message_content, $message_id);
    $update_stmt->execute();
    header("Location: messages.php");
    exit();
}

// Handle send message request
if (isset($_POST['send_message'])) {
    debug_log("Send message request received");
    debug_log($_POST); // Log POST data
    
    $student_name = trim($_POST['message_student']);
    $message = trim($_POST['message_content']);
    
    if (!empty($student_name) && !empty($message)) {
        try {
            // Create messages table if not exists
            $create_table = "CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_name VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                is_read TINYINT(1) DEFAULT 0
            )";
            $conn->query($create_table);
            
            $insert_stmt = $conn->prepare("INSERT INTO messages (student_name, message) VALUES (?, ?)");
            if (!$insert_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $insert_stmt->bind_param("ss", $student_name, $message);
            
            debug_log("Attempting to insert message");
            if (!$insert_stmt->execute()) {
                throw new Exception("Execute failed: " . $insert_stmt->error);
            }
            
            debug_log("Message inserted successfully. Insert ID: " . $insert_stmt->insert_id);
            
            $_SESSION['success_message'] = "Mesaj başarıyla gönderildi. (ID: " . $insert_stmt->insert_id . ")";
            $insert_stmt->close();
            header("Location: messages.php");
            exit();
        } catch (Exception $e) {
            debug_log("Error occurred: " . $e->getMessage());
            $_SESSION['error_message'] = "Mesaj gönderilemedi: " . $e->getMessage();
            header("Location: messages.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Lütfen tüm alanları doldurun.";
        header("Location: messages.php");
        exit();
    }
}

// Fetch messages
$messagesSql = "SELECT * FROM messages ORDER BY created_at DESC";
$messagesResult = $conn->query($messagesSql);

ob_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mesajlar</title>

    <!-- start: Css -->
    <link rel="stylesheet" type="text/css" href="asset/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/simple-line-icons.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/animate.min.css" />
    <link href="asset/css/style.css" rel="stylesheet">
</head>

<body id="mimin" class="dashboard">
    <!-- start:wrapper -->
    <div id="wrap">
        <!-- start:navbar -->
        <nav class="navbar navbar-default header navbar-fixed-top">
            <div class="col-md-12 nav-wrapper">
                <div class="navbar-header">
                    <div class="opener-left-menu is-open">
                        <span class="top"></span>
                        <span class="middle"></span>
                        <span class="bottom"></span>
                    </div>
                    <a href="index.php" class="navbar-brand">
                        <b>Öğrenci Yönetim Paneli</b>
                    </a>
                </div>
            </div>
        </nav>
        <!-- end:navbar -->

        <!-- start:left menu -->
        <div class="container-fluid mimin-wrapper">
            <div id="left-menu">
                <div class="sub-left-menu scroll">
                    <ul class="nav nav-list">
                        <li><div class="left-bg"></div></li>
                        <li class="time">
                            <h1 class="animated fadeInLeft">21:00</h1>
                            <p class="animated fadeInRight">Sat,October 2029</p>
                        </li>
                        <li class="ripple"><a href="admin.php"><span class="fa-home fa"></span>Ana Sayfa</a></li>
                        <li class="ripple"><a href="homework.php"><span class="fa fa-tasks"></span>Ödev İşlemleri</a></li>
                        <li class="ripple">
                            <a class="tree-toggle nav-header">
                                <span class="fa fa-user"></span> Öğrenci İşlemleri
                                <span class="fa-angle-right fa right-arrow text-right"></span>
                            </a>
                            <ul class="nav nav-list tree">
                                <li><a href="kullanici.php"><span class="fa fa-plus-circle"></span> Öğrenci Ekle</a></li>
                                <li><a href="student_books.php"><span class="fa fa-share-square"></span> Kitap Ata</a></li>
                            </ul>
                        </li>
                        <li class="ripple">
                            <a class="tree-toggle nav-header">
                                <span class="fa fa-info-circle"></span> Bilgilendirme
                                <span class="fa-angle-right fa right-arrow text-right"></span>
                            </a>
                            <ul class="nav nav-list tree">
                                <li><a href="announcements.php"><span class="fa fa-bullhorn"></span> Duyurular</a></li>
                                <li class="active"><a href="messages.php"><span class="fa-comments fa"></span> Özel Mesaj</a></li>
                            </ul>
                        </li>
                        <li class="ripple"><a href="add_book.php"><span class="fa fa-book"></span>Kitap İşlemleri</a></li>
                        <li class="ripple"><a href="ucret.php"><span class="fa-money fa"></span>Ders Ücreti</a></li>
                        <li class="ripple"><a href="logout.php"><span class="fa fa-sign-out"></span>Çıkış</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- end:left menu -->

        <!-- start:content -->
        <div id="content">
            <div class="panel box-shadow-none content-header">
                <div class="panel-body">
                    <div class="col-md-12">
                        <h3 class="animated fadeInLeft">Mesajlar</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <!-- Message entry panel -->
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Yeni Mesaj</div>
                                    <div class="panel-body">
                                        <?php
                                        if (isset($_SESSION['error_message'])) {
                                            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                                            unset($_SESSION['error_message']);
                                        }
                                        if (isset($_SESSION['success_message'])) {
                                            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                                            unset($_SESSION['success_message']);
                                        }
                                        ?>
                                        <form method="POST" id="messageForm">
                                            <input type="hidden" name="message_id" id="message_id">
                                            <div class="form-group">
                                                <label for="message_student">Öğrenci Seçin</label>
                                                <select name="message_student" id="message_student" class="form-control" required>
                                                    <option value="">Öğrenci Seçin</option>
                                                    <?php
                                                    $studentSql = "SELECT studentName FROM users";
                                                    $studentResult = $conn->query($studentSql);
                                                    if ($studentResult->num_rows > 0) {
                                                        while ($studentRow = $studentResult->fetch_assoc()) {
                                                            echo "<option value='{$studentRow['studentName']}'>{$studentRow['studentName']}</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="message_content">Mesajınız</label>
                                                <textarea name="message_content" id="message_content" class="form-control" placeholder="Mesajınız" required></textarea>
                                            </div>
                                            <button type="submit" name="send_message" id="messageButton" class="btn btn-primary">Mesaj Gönder</button>
                                            <button type="button" onclick="resetMessageForm()" class="btn btn-secondary">Yeni Mesaj</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Message table panel -->
                            <div class="col-md-8">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Mesaj Listesi</div>
                                    <div class="panel-body">
                                        <?php
                                        // Display last inserted ID for debugging
                                        if (isset($_SESSION['last_insert_id'])) {
                                            echo '<div class="alert alert-info">Son eklenen mesaj ID: ' . $_SESSION['last_insert_id'] . '</div>';
                                            unset($_SESSION['last_insert_id']);
                                        }
                                        
                                        // Debug information
                                        echo '<div style="display:none" id="debug-info">';
                                        debug_log("Current messages in database:");
                                        $debug_query = "SELECT * FROM messages ORDER BY created_at DESC LIMIT 5";
                                        $debug_result = $conn->query($debug_query);
                                        while ($row = $debug_result->fetch_assoc()) {
                                            debug_log($row);
                                        }
                                        echo '</div>';
                                        ?>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Öğrenci</th>
                                                    <th>Mesaj</th>
                                                    <th>Tarih</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($messagesResult->num_rows > 0) {
                                                    while ($message = $messagesResult->fetch_assoc()) {
                                                        echo "<tr>
                                                                <td>{$message['student_name']}</td>
                                                                <td>{$message['message']}</td>
                                                                <td>{$message['created_at']}</td>
                                                                <td class='action-buttons'>
                                                                    <button class='btn btn-warning' onclick='editMessage({$message['id']}, \"{$message['student_name']}\", \"{$message['message']}\")'>Düzenle</button>
                                                                    <button class='btn btn-danger' onclick='deleteMessage({$message['id']})'>Sil</button>
                                                                </td>
                                                              </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5'>Mesaj bulunamadı</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end:content -->

        <style>
            #content {
                margin-left: 250px;
                padding: 60px 15px 15px 15px;
                width: calc(100% - 250px);
                position: relative;
                float: right;
            }

            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
                width: 100%;
            }

            .nav-list .tree {
                display: none;
            }

            .nav-list .tree.active {
                display: block;
            }

            .nav-list .tree li a span {
                margin-right: 10px;
                width: 20px;
                display: inline-block;
            }
        </style>
    </div>
    <!-- end:wrapper -->

    <!-- start:javascript -->
    <script src="asset/js/jquery.min.js"></script>
    <script src="asset/js/jquery.ui.min.js"></script>
    <script src="asset/js/bootstrap.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>
    <script src="asset/js/main.js"></script>

    <script>
        function editMessage(id, student, content) {
            document.getElementById("message_id").value = id;
            document.getElementById("message_student").value = student;
            document.getElementById("message_content").value = content;
            document.getElementById("messageButton").textContent = "Güncelle";
            document.getElementById("messageButton").name = "edit_message";
            document.getElementById("messageForm").scrollIntoView({
                behavior: "smooth"
            });
        }

        function deleteMessage(id) {
            if (confirm("Bu mesajı silmek istediğinizden emin misiniz?")) {
                const form = document.createElement("form");
                form.method = "POST";
                form.innerHTML = `<input type="hidden" name="delete_message" value="1">
                                <input type="hidden" name="message_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetMessageForm() {
            document.getElementById("messageForm").reset();
            document.getElementById("message_id").value = "";
            document.getElementById("messageButton").textContent = "Mesaj Gönder";
            document.getElementById("messageButton").name = "send_message";
        }

        // Add submenu toggle functionality
        $(document).ready(function() {
            $('.tree-toggle').click(function(e) {
                e.preventDefault();
                var $menuItem = $(this).parent();
                var $submenu = $menuItem.children('ul.tree');

                if ($submenu.hasClass('active')) {
                    $submenu.removeClass('active').slideUp(200);
                    $(this).find('.right-arrow').removeClass('fa-angle-down').addClass('fa-angle-right');
                } else {
                    $submenu.addClass('active').slideDown(200);
                    $(this).find('.right-arrow').removeClass('fa-angle-right').addClass('fa-angle-down');
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
