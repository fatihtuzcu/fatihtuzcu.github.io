<?php
session_start();
require 'database.php';

// Initialize connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Fetch student names for the dropdown
$studentSql = "SELECT studentName FROM users";
$studentResult = $conn->query($studentSql);

// Fetch homework
$homeworkSql = "SELECT h.id, h.student_name, h.book_name, h.homework_desc, h.due_date, h.assignment_date, h.status 
                FROM homework h 
                ORDER BY h.assignment_date DESC";
$homeworkResult = $conn->query($homeworkSql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ödev İşlemleri</title>

    <!-- Include your CSS files here -->
    <link rel="stylesheet" type="text/css" href="asset/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/simple-line-icons.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/animate.min.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/fullcalendar.min.css" />
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
                        <li>
                            <div class="left-bg"></div>
                        </li>
                        <li class="time">
                            <h1 class="animated fadeInLeft">21:00</h1>
                            <p class="animated fadeInRight">Sat,October 2029</p>
                        </li>
                        <li class="ripple"><a href="admin.php"><span class="fa-home fa"></span>Ana Sayfa</a></li>
                        <li class="active ripple"><a href="homework.php"><span class="fa fa-tasks"></span>Ödev İşlemleri</a></li>
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
                <div class="panel-body container-fluid">
                    <div class="col-md-12">
                        <h3 class="animated fadeInLeft">Ödev İşlemleri</h3>
                    </div>
                </div>
            </div>

            <div class="container-fluid" style="margin-top: 15px;">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="row">
                                <!-- Homework entry panel -->
                                <div class="col-md-4">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Ödev Ata</div>
                                        <div class="panel-body">
                                            <form id="homeworkAssignForm" action="assign_homework.php" method="POST" class="homework-form">
                                                <div class="form-group">
                                                    <label for="studentSelect">Öğrenci Seçin</label>
                                                    <select name="studentName" id="studentSelect" class="form-control" required onchange="loadStudentBooks(); filterHomework();">
                                                        <option value="">Öğrenci Seçin</option>
                                                        <?php
                                                        if ($studentResult->num_rows > 0) {
                                                            $studentResult->data_seek(0);
                                                            while ($studentRow = $studentResult->fetch_assoc()) {
                                                                echo "<option value='{$studentRow['studentName']}'>{$studentRow['studentName']}</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="bookSelect">Kitap Seçin</label>
                                                    <select name="bookName" id="bookSelect" class="form-control" required>
                                                        <option value="">Önce Öğrenci Seçin</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="homeworkDesc">Ödev Açıklaması</label>
                                                    <textarea name="homeworkDesc" id="homeworkDesc" class="form-control" placeholder="Ödev açıklaması" required></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dueDate">Teslim Tarihi</label>
                                                    <input type="date" name="dueDate" id="dueDate" class="form-control" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Ödev Ata</button>
                                            </form>
                                            <div id="messageBox" style="margin-top: 10px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Homework table panel -->
                                <div class="col-md-8">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Ödev Listesi</div>
                                        <div class="panel-body">
                                            <button class="btn btn-secondary toggle-completed btn-info" onclick="toggleCompletedHomework()">Tamamlanan Ödevleri Göster</button>
                                            <table class="table table-striped" id="homework-list">
                                                <thead>
                                                    <tr>
                                                        <th>Öğrenci Adı</th>
                                                        <th>Kitap Adı</th>
                                                        <th>Ödev Açıklaması</th>
                                                        <th>Atanma Tarihi</th>
                                                        <th>Teslim Tarihi</th>
                                                        <th>Durum</th>
                                                        <th>İşlem</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($homeworkResult->num_rows > 0) {
                                                        while ($homework = $homeworkResult->fetch_assoc()) {
                                                            $status = $homework['status'] ?? 'Yapılmadı';
                                                            echo "<tr>
                                                                    <td>{$homework['student_name']}</td>
                                                                    <td>{$homework['book_name']}</td>
                                                                    <td>{$homework['homework_desc']}</td>
                                                                    <td>{$homework['assignment_date']}</td>
                                                                    <td>{$homework['due_date']}</td>
                                                                    <td>{$status}</td>
                                                                    <td>
                                                                        <select class='form-control' onchange='updateHomeworkStatus(this.value, {$homework['id']})'>
                                                                            <option value='Yapılmadı' " . ($status == 'Yapılmadı' ? 'selected' : '') . ">Yapılmadı</option>
                                                                            <option value='Yapıldı' " . ($status == 'Yapıldı' ? 'selected' : '') . ">Yapıldı</option>
                                                                        </select>
                                                                    </td>
                                                                  </tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='7'>Atanmış ödev bulunamadı</td></tr>";
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
    </div>
    <!-- end:wrapper -->

    <!-- start:javascript -->
    <script src="asset/js/jquery.min.js"></script>
    <script src="asset/js/jquery.ui.min.js"></script>
    <script src="asset/js/bootstrap.min.js"></script>
    <script src="asset/js/plugins/moment.min.js"></script>
    <script src="asset/js/plugins/fullcalendar.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>
    <script src="asset/js/plugins/jquery.vmap.min.js"></script>
    <script src="asset/js/plugins/maps/jquery.vmap.world.js"></script>
    <script src="asset/js/plugins/jquery.vmap.sampledata.js"></script>
    <script src="asset/js/plugins/chart.min.js"></script>
    <script src="asset/js/main.js"></script>
    <!-- end:javascript -->

    <!-- Keep the original JavaScript from homework.php -->
    <script>
        document.getElementById("homeworkAssignForm").addEventListener("submit", function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageBox = document.getElementById("messageBox");

            fetch("assign_homework.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    messageBox.innerHTML = data;
                    if (data.includes("başarıyla")) {
                        // Form başarılı ise formu sıfırla ve tabloyu yenile
                        this.reset();
                        location.reload();
                    }
                })
                .catch(error => {
                    messageBox.innerHTML = "Bir hata oluştu: " + error;
                });
        });

        function loadStudentBooks() {
            const studentName = document.getElementById("studentSelect").value;
            const bookSelect = document.getElementById("bookSelect");

            if (!studentName) {
                bookSelect.innerHTML = "<option value=\'\'>Önce Öğrenci Seçin</option>";
                return;
            }

            fetch("get_student_books.php?student=" + encodeURIComponent(studentName))
                .then(response => response.json())
                .then(books => {
                    bookSelect.innerHTML = "<option value=\'\'>Kitap Seçin</option>";
                    books.forEach(book => {
                        bookSelect.innerHTML += `<option value="${book.book_name}">${book.book_name}</option>`;
                    });
                });
        }

        function updateHomeworkStatus(status, homeworkId) {
            fetch("update_homework_status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `status=${status}&id=${homeworkId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Ödev durumu güncellendi");
                    } else {
                        alert("Bir hata oluştu");
                    }
                });
        }

        function toggleCompletedHomework() {
            showCompleted = !showCompleted;
            filterHomework();
            const button = document.querySelector(".toggle-completed");
            button.textContent = showCompleted ? "Tamamlanan Ödevleri Gizle" : "Tüm Ödevleri Göster";
        }

        function filterHomework() {
            const selectedStudent = document.querySelector("#studentSelect").value;
            const rows = document.querySelectorAll("#homework-list tbody tr");
            rows.forEach(row => {
                const studentName = row.querySelector("td:first-child").textContent;
                const status = row.querySelector("td:nth-child(6)").textContent;
                const shouldShow = (selectedStudent === "" || studentName === selectedStudent) &&
                    (showCompleted || status !== "Yapıldı");
                row.style.display = shouldShow ? "" : "none";
            });
        }

        // Initialize showCompleted as false (hide completed homework by default)
        let showCompleted = false;

        // Add window.onload event handler
        window.onload = function() {
            filterHomework(); // This will hide completed homework when page loads
        }

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