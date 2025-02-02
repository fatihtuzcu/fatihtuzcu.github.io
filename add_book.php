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

// Handle add or update book request
if (isset($_POST['add_book'])) {
    $book_name = $_POST['book_name'];
    $difficulty = $_POST['difficulty'];
    
    if (isset($_GET['id'])) {
        // Update existing book
        $book_id = $_GET['id'];
        $update_stmt = $conn->prepare("UPDATE kitaplar SET book_name = ?, difficulty = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $book_name, $difficulty, $book_id);
        $update_stmt->execute();
    } else {
        // Add new book
        $insert_stmt = $conn->prepare("INSERT INTO kitaplar (book_name, difficulty) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $book_name, $difficulty);
        $insert_stmt->execute();
    }
    header("Location: add_book.php");
    exit();
}

// Handle delete book request
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    
    $delete_stmt = $conn->prepare("DELETE FROM kitaplar WHERE id = ?");
    $delete_stmt->bind_param("i", $book_id);
    $delete_stmt->execute();
    header("Location: add_book.php");
    exit();
}

// Handle CSV import request
if (isset($_POST['import_csv'])) {
    if ($_FILES['csv_file']['error'] == 0) {
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            $book_name = $line[0];
            $difficulty = $line[1];
            
            $insert_stmt = $conn->prepare("INSERT INTO kitaplar (book_name, difficulty) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $book_name, $difficulty);
            $insert_stmt->execute();
        }
        fclose($file);
        header("Location: add_book.php");
        exit();
    }
}

// Fetch books
$booksSql = "SELECT * FROM kitaplar ORDER BY created_at DESC";
$booksResult = $conn->query($booksSql);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Admin Panel">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - Kitap Ekleme</title>
 
    <!-- start: Css -->
    <link rel="stylesheet" type="text/css" href="asset/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/simple-line-icons.css"/>
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/animate.min.css"/>
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/fullcalendar.min.css"/>
    <link href="asset/css/style.css" rel="stylesheet">
    <link rel="shortcut icon" href="asset/img/logomi.png">
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
                                <li><a href="messages.php"><span class="fa-comments fa"></span> Özel Mesaj</a></li>
                            </ul>
                        </li>
                        <li class="active ripple"><a href="add_book.php"><span class="fa fa-book"></span>Kitap İşlemleri</a></li>
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
                        <h3 class="animated fadeInLeft">Kitap Ekleme</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <!-- Book entry panel -->
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Yeni Kitap</div>
                                    <div class="panel-body">
                                        <form method="POST" class="book-form" id="addBookForm">
                                            <div class="form-group">
                                                <label for="book_name">Kitap Adı</label>
                                                <input type="text" name="book_name" id="book_name" class="form-control" placeholder="Kitap Adı" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="difficulty">Açıklama</label>
                                                <textarea name="difficulty" id="difficulty" class="form-control" placeholder="Kitap Açıklaması"></textarea>
                                            </div>
                                            <button type="submit" name="add_book" id="addBookButton" class="btn btn-primary">Kitap Ekle</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Book table panel -->
                            <div class="col-md-8">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Kitap Listesi</div>
                                    <div class="panel-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Kitap Adı</th>
                                                    <th>Açıklama</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($booksResult->num_rows > 0) {
                                                    while($book = $booksResult->fetch_assoc()) {
                                                        echo "<tr>
                                                                <td>{$book['book_name']}</td>
                                                                <td>{$book['difficulty']}</td>
                                                                <td class='action-buttons'>
                                                                    <button class='btn btn-warning' onclick='editBook({$book['id']}, \"{$book['book_name']}\", \"{$book['difficulty']}\")'>Düzenle</button>
                                                                    <button class='btn btn-danger' onclick='deleteBook({$book['id']})'>Sil</button>
                                                                </td>
                                                              </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='3'>Eklenen kitap bulunamadı</td></tr>";
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
    </div>
    <!-- end:wrapper -->

    <!-- Scripts -->
    <script src="asset/js/jquery.min.js"></script>
    <script src="asset/js/jquery.ui.min.js"></script>
    <script src="asset/js/bootstrap.min.js"></script>
    <script src="asset/js/plugins/moment.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>
    <script src="asset/js/main.js"></script>
    
    <script>
        function editBook(id, name, difficulty) {
            document.getElementById("book_name").value = name;
            document.getElementById("difficulty").value = difficulty;
            document.getElementById("addBookButton").textContent = "Güncelle";
            document.getElementById("addBookForm").action = "add_book.php?id=" + id;
        }

        function deleteBook(id) {
            if (confirm("Bu kitabı silmek istediğinizden emin misiniz?")) {
                const form = document.createElement("form");
                form.method = "POST";
                form.innerHTML = `<input type="hidden" name="delete_book" value="1">
                                  <input type="hidden" name="book_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
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
