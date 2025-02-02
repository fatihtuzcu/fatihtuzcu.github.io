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

// Fetch student names for the dropdown
$studentSql = "SELECT studentName FROM users";
$studentResult = $conn->query($studentSql);

// Fetch books for the dropdown
$booksSql = "SELECT book_name FROM kitaplar";
$booksResult = $conn->query($booksSql);

// Fetch student books
$studentBooksSql = "SELECT student_name, book_name, assigned_date FROM student_books";
$studentBooksResult = $conn->query($studentBooksSql);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Admin Panel">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - Öğrenci Kitapları</title>
 
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

        /* Add these new styles */
        .nav-list .tree {
            display: none;
        }

        .nav-list .tree.active {
            display: block;
        }

        /* Add these new styles for submenu icons */
        .nav-list .tree li a span {
            margin-right: 10px;
            width: 20px;
            display: inline-block;
        }
    </style>
</head>

<body id="mimin" class="dashboard">
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
                    <li>
                        <div class="left-bg"></div>
                    </li>
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
                            <li class="active"><a href="student_books.php"><span class="fa fa-share-square"></span> Kitap Ata</a></li>
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
                    <h3 class="animated fadeInLeft">Öğrenci Kitapları</h3>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div id="student-books-entry" class="section">
                                <h2>Kitap Ata</h2>
                                <div class="book-form-container">
                                    <form id="assignBookForm" action="assign_book.php" method="POST" class="book-form">
                                        <input type="hidden" name="edit_mode" id="edit_mode" value="0">
                                        <input type="hidden" name="old_student_name" id="old_student_name">
                                        <input type="hidden" name="old_book_name" id="old_book_name">
                                        <div class="form-group">
                                            <label for="studentName">Öğrenci Seçin</label>
                                            <select name="studentName" id="studentName" class="form-control" required onchange="filterBooks()">
                                                <option value="">Öğrenci Seçin</option>
                                                <?php
                                                if ($studentResult->num_rows > 0) {
                                                    while($studentRow = $studentResult->fetch_assoc()) {
                                                        echo "<option value='{$studentRow['studentName']}'>{$studentRow['studentName']}</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>Öğrenci bulunamadı</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="bookName">Kitap Seçin</label>
                                            <select name="bookName" id="bookName" class="form-control" required>
                                                <option value="">Kitap Seçin</option>
                                                <?php
                                                if ($booksResult->num_rows > 0) {
                                                    while($bookRow = $booksResult->fetch_assoc()) {
                                                        echo "<option value='{$bookRow['book_name']}'>{$bookRow['book_name']}</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>Kitap bulunamadı</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="assignBookButton">Gönder</button>
                                    </form>
                                    <div id="messageBox" style="margin-top: 10px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div id="student-books-list" class="section">
                                <h2>Öğrenci Kitapları Listesi</h2>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Öğrenci Adı</th>
                                            <th>Kitap Adı</th>
                                            <th>Oluşturma Tarihi</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($studentBooksResult->num_rows > 0) {
                                            while($book = $studentBooksResult->fetch_assoc()) {
                                                echo "<tr>
                                                        <td>{$book['student_name']}</td>
                                                        <td>{$book['book_name']}</td>
                                                        <td>{$book['assigned_date']}</td>
                                                        <td class='action-buttons'>
                                                            <button class='btn btn-success' onclick='editBook(\"{$book['student_name']}\", \"{$book['book_name']}\")'>Düzenle</button>
                                                            <button class='btn btn-danger' onclick='deleteBook(\"{$book['student_name']}\", \"{$book['book_name']}\")'>Sil</button>
                                                        </td>
                                                      </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4'>Kayıtlı kitap bulunamadı</td></tr>";
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
    <!-- end:content -->

    <!-- start:javascript -->
    <script src="asset/js/jquery.min.js"></script>
    <script src="asset/js/bootstrap.min.js"></script>
    <script src="asset/js/plugins/moment.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>
    <script src="asset/js/plugins/jquery.vmap.min.js"></script>
    <script src="asset/js/plugins/maps/jquery.vmap.world.js"></script>
    <script src="asset/js/plugins/jquery.vmap.sampledata.js"></script>
    <script src="asset/js/plugins/chart.min.js"></script>
    <script src="asset/js/plugins/jquery.datatables.min.js"></script>
    <script src="asset/js/plugins/datatables.bootstrap.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>
    <script src="asset/js/main.js"></script>

    <script>
        document.querySelector("select[name='studentName']").addEventListener("change", filterBooks);

        document.getElementById("assignBookForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageBox = document.getElementById("messageBox");
            
            fetch("assign_book.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                messageBox.innerHTML = data;
                if (data.includes("başarıyla")) {
                    this.reset();
                    document.getElementById("edit_mode").value = "0";
                    document.getElementById("assignBookButton").textContent = "Gönder";
                    location.reload();
                }
            })
            .catch(error => {
                messageBox.innerHTML = "Bir hata oluştu: " + error;
            });
        });

        function filterBooks() {
            const selectedStudent = document.querySelector(".book-form select[name='studentName']").value;
            const rows = document.querySelectorAll("#student-books-list table tbody tr");
            rows.forEach(row => {
                const studentName = row.querySelector("td:first-child").textContent;
                if (selectedStudent === "" || studentName === selectedStudent) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        function deleteBook(studentName, bookName) {
            if (confirm("Bu kitabı silmek istediğinizden emin misiniz?")) {
                const formData = new FormData();
                formData.append("delete_book", "1");
                formData.append("student_name", studentName);
                formData.append("book_name", bookName);

                fetch("delete_book.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                })
                .catch(error => {
                    alert("Bir hata oluştu: " + error);
                });
            }
        }

        function editBook(studentName, bookName) {
            document.getElementById("studentName").value = studentName;
            document.getElementById("bookName").value = bookName;
            document.getElementById("edit_mode").value = "1";
            document.getElementById("old_student_name").value = studentName;
            document.getElementById("old_book_name").value = bookName;
            document.getElementById("assignBookButton").textContent = "Güncelle";
            document.getElementById("student-books-entry").scrollIntoView({
                behavior: "smooth"
            });
        }

        document.querySelectorAll("button").forEach(button => {
            button.classList.add("btn", "btn-secondary");
        });

        // Add this to enable submenu toggle
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
