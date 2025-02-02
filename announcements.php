<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Erişim reddedildi. Lütfen giriş yapın.");
}

require 'database.php';

// Initialize connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Handle delete announcement request
if (isset($_POST['delete_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    $delete_stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $delete_stmt->bind_param("i", $announcement_id);
    $delete_stmt->execute();
    header("Location: announcements.php");
    exit();
}

// Handle edit announcement request
if (isset($_POST['edit_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    $title = $_POST['announcement_title'];
    $content = $_POST['announcement_content'];

    $update_stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $title, $content, $announcement_id);
    $update_stmt->execute();
    header("Location: announcements.php");
    exit();
}

// Handle submit announcement request
if (isset($_POST['submitAnnouncement'])) {
    $title = $_POST['announcement_title'];
    $content = $_POST['announcement_content'];

    $insert_stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
    $insert_stmt->bind_param("ss", $title, $content);
    $insert_stmt->execute();
    header("Location: announcements.php");
    exit();
}

// Fetch announcements
$announcementsSql = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcementsResult = $conn->query($announcementsSql);

ob_start();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="description" content="Admin Panel">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duyurular - Admin Panel</title>

    <!-- start: Css -->
    <link rel="stylesheet" type="text/css" href="asset/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/simple-line-icons.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/animate.min.css" />
    <link href="asset/css/style.css" rel="stylesheet">
    <link rel="shortcut icon" href="asset/img/logomi.png">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <style>
        .btn-edit {
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }

        .btn-delete {
            background-color: #f44336; /* Red */
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }

        .btn-reset {
            background-color: #008CBA; /* Blue */
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }

        .full-width {
            width: 100%;
        }

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
                                <li class="active"><a href="announcements.php"><span class="fa fa-bullhorn"></span> Duyurular</a></li>
                                <li><a href="messages.php"><span class="fa-comments fa"></span> Özel Mesaj</a></li>
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
            <div id="announcements" class="section">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="animated fadeInLeft">Duyurular</h3>
                    </div>
                </div>
                <div class="form-container">
                    <form method="POST" id="announcementForm">
                        <input type="hidden" name="announcement_id" id="announcement_id">
                        <input type="text" name="announcement_title" id="announcement_title" placeholder="Duyuru Başlığı" class="full-width" required>
                        <textarea name="announcement_content" id="announcement_content" placeholder="Duyuru İçeriği" required></textarea>
                        <button type="submit" name="submit_button" id="announcementButton" class="btn-edit">Duyuru Ekle</button>
                        <button type="button" onclick="resetAnnouncementForm()" class="btn-reset">Yeni Duyuru</button>
                    </form>
                </div>
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Duyuru Listesi</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>İçerik</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($announcementsResult->num_rows > 0) {
                                    while ($announcement = $announcementsResult->fetch_assoc()) {
                                        // Properly escape content for JavaScript
                                        $escaped_content = str_replace(array("\r", "\n", '"'), array('', '', '\"'), $announcement['content']);
                                        echo "<tr>
                                            <td>{$announcement['title']}</td>
                                            <td>{$announcement['content']}</td>
                                            <td>{$announcement['created_at']}</td>
                                            <td class='action-buttons'>
                                                <button class='btn-edit' onclick='editAnnouncement({$announcement['id']}, \"{$announcement['title']}\", \"{$escaped_content}\")'>Düzenle</button>
                                                <button class='btn-delete' onclick='deleteAnnouncement({$announcement['id']})'>Sil</button>
                                            </td>
                                          </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>Duyuru bulunamadı</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- end:content -->
    </div>
    <!-- end:wrapper -->

    <!-- start: Javascript -->
    <script src="asset/js/jquery.min.js"></script>
    <script src="asset/js/jquery.ui.min.js"></script>
    <script src="asset/js/bootstrap.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>
    <script src="asset/js/main.js"></script>
    <script>
        CKEDITOR.replace('announcement_content');

        function editAnnouncement(id, title, content) {
            document.getElementById('announcement_id').value = id;
            document.getElementById('announcement_title').value = title;
            CKEDITOR.instances.announcement_content.setData(content);
            document.getElementById('announcementButton').textContent = "Güncelle";
            document.getElementById('announcementButton').name = "edit_announcement";
            document.getElementById('announcementForm').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function deleteAnnouncement(id) {
            if (confirm('Bu duyuruyu silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="delete_announcement" value="1">
                                <input type="hidden" name="announcement_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetAnnouncementForm() {
            document.getElementById('announcementForm').reset();
            CKEDITOR.instances.announcement_content.setData('');
            document.getElementById('announcement_id').value = "";
            document.getElementById('announcementButton').textContent = "Duyuru Ekle";
            document.getElementById('announcementButton').name = "submitAnnouncement";
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

    <?php $conn->close(); ?>
</body>

</html>