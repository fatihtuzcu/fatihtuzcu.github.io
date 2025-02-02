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

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Get the student name first
    $name_stmt = $conn->prepare("SELECT studentName FROM users WHERE id = ?");
    $name_stmt->bind_param("i", $delete_id);
    $name_stmt->execute();
    $result = $name_stmt->get_result();
    $user = $result->fetch_assoc();
    $studentName = $user['studentName'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete from homework table
        $delete_homework = $conn->prepare("DELETE FROM homework WHERE student_name = ?");
        $delete_homework->bind_param("s", $studentName);
        $delete_homework->execute();

        // Delete from student_books table
        $delete_books = $conn->prepare("DELETE FROM student_books WHERE student_name = ?");
        $delete_books->bind_param("s", $studentName);
        $delete_books->execute();

        // Finally delete the user
        $delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_user->bind_param("i", $delete_id);
        $delete_user->execute();

        // Commit transaction
        $conn->commit();
        header("Location: admin.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error deleting user: " . $e->getMessage());
    }
}

// Handle delete book request
if (isset($_POST['delete_book'])) {
    $student_name = $_POST['student_name'];
    $book_name = $_POST['book_name'];

    $delete_book = $conn->prepare("DELETE FROM student_books WHERE student_name = ? AND book_name = ?");
    $delete_book->bind_param("ss", $student_name, $book_name);
    $delete_book->execute();
    header("Location: admin.php#student-books");
    exit();
}

// Handle update book request
if (isset($_POST['update_book'])) {
    $old_student_name = $_POST['old_student_name'];
    $old_book_name = $_POST['old_book_name'];
    $new_book_name = $_POST['new_book_name'];

    $update_book = $conn->prepare("UPDATE student_books SET book_name = ? WHERE student_name = ? AND book_name = ?");
    $update_book->bind_param("sss", $new_book_name, $old_student_name, $old_book_name);
    $update_book->execute();
    header("Location: admin.php#student-books");
    exit();
}

// Handle edit/create form submission
if (isset($_POST['submitUser'])) {
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        // Update existing user
        $edit_id = $_POST['edit_id'];
        $studentName = $_POST['studentName'];
        $password = $_POST['password'];
        $class = $_POST['class'];
        $parentPhone = $_POST['parentPhone'];
        $city = $_POST['city'];

        $update_stmt = $conn->prepare("UPDATE users SET studentName = ?, password = ?, class = ?, parentPhone = ?, city = ? WHERE id = ?");
        $update_stmt->bind_param("sssssi", $studentName, $password, $class, $parentPhone, $city, $edit_id);
        $update_stmt->execute();
        header("Location: admin.php");
        exit();
    } else {
        // Insert new user
        $studentName = $_POST['studentName'];
        $password = !empty($_POST['password']) ? $_POST['password'] : '12345678';
        $class = $_POST['class'];
        $parentPhone = $_POST['parentPhone'];
        $city = $_POST['city'];

        $insert_stmt = $conn->prepare("INSERT INTO users (studentName, password, class, parentPhone, city) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $studentName, $password, $class, $parentPhone, $city);
        $insert_stmt->execute();
        header("Location: admin.php");
        exit();
    }
}

$sql = "SELECT studentName, class, parentPhone, city FROM users";
$result = $conn->query($sql);

// Fetch student names for the dropdown
$studentSql = "SELECT studentName FROM users";
$studentResult = $conn->query($studentSql);

// Fetch student books
$booksSql = "SELECT student_name, book_name, assigned_date FROM student_books";
$booksResult = $conn->query($booksSql);

// Additional query for homework
$homeworkSql = "SELECT h.id, h.student_name, h.book_name, h.homework_desc, h.due_date, h.assignment_date, h.status 
                FROM homework h 
                ORDER BY h.assignment_date DESC";
$homeworkResult = $conn->query($homeworkSql);

// Add this after other SQL queries
$announcementsSql = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcementsResult = $conn->query($announcementsSql);

// Add announcement handling
if (isset($_POST['submitAnnouncement'])) {
    $title = $_POST['announcement_title'];
    $content = $_POST['announcement_content'];

    $insert_stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
    $insert_stmt->bind_param("ss", $title, $content);
    $insert_stmt->execute();
    header("Location: admin.php#announcements");
    exit();
}

// Add these handlers after other POST handlers
if (isset($_POST['delete_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    $delete_stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $delete_stmt->bind_param("i", $announcement_id);
    $delete_stmt->execute();
    header("Location: admin.php#announcements");
    exit();
}

if (isset($_POST['edit_announcement'])) {
    $announcement_id = $_POST['announcement_id'];
    $title = $_POST['announcement_title'];
    $content = $_POST['announcement_content'];

    $update_stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $title, $content, $announcement_id);
    $update_stmt->execute();
    header("Location: admin.php#announcements");
    exit();
}

// Add after other POST handlers
if (isset($_POST['send_message'])) {
    $student_name = $_POST['message_student'];
    $message = $_POST['message_content'];

    $insert_stmt = $conn->prepare("INSERT INTO messages (student_name, message) VALUES (?, ?)");
    $insert_stmt->bind_param("ss", $student_name, $message);
    $insert_stmt->execute();
    header("Location: admin.php#messages");
    exit();
}

// Add after other POST handlers
if (isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];
    $delete_stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $delete_stmt->bind_param("i", $message_id);
    $delete_stmt->execute();
    header("Location: admin.php#messages");
    exit();
}

if (isset($_POST['edit_message'])) {
    $message_id = $_POST['message_id'];
    $message_content = $_POST['message_content'];

    $update_stmt = $conn->prepare("UPDATE messages SET message = ? WHERE id = ?");
    $update_stmt->bind_param("si", $message_content, $message_id);
    $update_stmt->execute();
    header("Location: admin.php#messages");
    exit();
}

// Add after other SQL queries
$messagesSql = "SELECT * FROM messages ORDER BY created_at DESC";
$messagesResult = $conn->query($messagesSql);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="description" content="Admin Panel">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>

    <!-- start: Css -->
    <link rel="stylesheet" type="text/css" href="asset/css/bootstrap.min.css">
    <!-- plugins -->
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/simple-line-icons.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/animate.min.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/fullcalendar.min.css" />
    <link href="asset/css/style.css" rel="stylesheet">
    <!-- end: Css -->

    <link rel="shortcut icon" href="asset/img/logomi.png">
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
                                <li class="active ripple"><a href="kullanici.php"><span class="fa fa-plus-circle"></span> Öğrenci Ekle</a></li>
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
                <div class="panel-body">
                    <div class="col-md-12">
                        <h3 class="animated fadeInLeft">Kullanıcı Bilgileri</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel">
                            <div class="panel-body">
                                <h4>Öğrenci Giriş Bilgileri</h4>
                                <!-- Existing form-container content -->
                                <div class="form-container">
                                    <form method="POST" id="userForm">
                                        <input type="hidden" name="edit_id" id="edit_id">
                                        <div class="form-group">
                                            <label for="studentName">Öğrenci Adı</label>
                                            <input type="text" class="form-control" name="studentName" id="studentName" placeholder="Öğrenci Adı" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Şifre</label>
                                            <input type="password" class="form-control" name="password" id="password" placeholder="Şifre" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="class">Sınıf</label>
                                            <select class="form-control" name="class" id="class" required>
                                                <option value="">Sınıf Seçiniz</option>
                                                <option value="5.SINIF">5.SINIF</option>
                                                <option value="6.SINIF">6.SINIF</option>
                                                <option value="7.SINIF">7.SINIF</option>
                                                <option value="8.SINIF">8.SINIF</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="parentPhone">Veli Telefon</label>
                                            <input type="tel" class="form-control"
                                                name="parentPhone"
                                                id="parentPhone"
                                                placeholder="Veli Telefon (05XX XXX XXXX)"
                                                pattern="[0-9]{11}"
                                                maxlength="11"
                                                title="Lütfen 11 haneli telefon numarası giriniz">
                                        </div>
                                        <div class="form-group">
                                            <label for="city">Şehir</label>
                                            <select class="form-control" name="city" id="city" required>
                                                <option value="">Şehir Seçiniz</option>
                                                <option value="Adana">Adana</option>
                                                <option value="Adıyaman">Adıyaman</option>
                                                <option value="Afyonkarahisar">Afyonkarahisar</option>
                                                <option value="Ağrı">Ağrı</option>
                                                <option value="Amasya">Amasya</option>
                                                <option value="Ankara">Ankara</option>
                                                <option value="Antalya">Antalya</option>
                                                <option value="Artvin">Artvin</option>
                                                <option value="Aydın">Aydın</option>
                                                <option value="Balıkesir">Balıkesir</option>
                                                <option value="Bilecik">Bilecik</option>
                                                <option value="Bingöl">Bingöl</option>
                                                <option value="Bitlis">Bitlis</option>
                                                <option value="Bolu">Bolu</option>
                                                <option value="Burdur">Burdur</option>
                                                <option value="Bursa">Bursa</option>
                                                <option value="Çanakkale">Çanakkale</option>
                                                <option value="Çankırı">Çankırı</option>
                                                <option value="Çorum">Çorum</option>
                                                <option value="Denizli">Denizli</option>
                                                <option value="Diyarbakır">Diyarbakır</option>
                                                <option value="Edirne">Edirne</option>
                                                <option value="Elazığ">Elazığ</option>
                                                <option value="Erzincan">Erzincan</option>
                                                <option value="Erzurum">Erzurum</option>
                                                <option value="Eskişehir">Eskişehir</option>
                                                <option value="Gaziantep">Gaziantep</option>
                                                <option value="Giresun">Giresun</option>
                                                <option value="Gümüşhane">Gümüşhane</option>
                                                <option value="Hakkari">Hakkari</option>
                                                <option value="Hatay">Hatay</option>
                                                <option value="Isparta">Isparta</option>
                                                <option value="Mersin">Mersin</option>
                                                <option value="İstanbul">İstanbul</option>
                                                <option value="İzmir">İzmir</option>
                                                <option value="Kars">Kars</option>
                                                <option value="Kastamonu">Kastamonu</option>
                                                <option value="Kayseri">Kayseri</option>
                                                <option value="Kırklareli">Kırklareli</option>
                                                <option value="Kırşehir">Kırşehir</option>
                                                <option value="Kocaeli">Kocaeli</option>
                                                <option value="Konya">Konya</option>
                                                <option value="Kütahya">Kütahya</option>
                                                <option value="Malatya">Malatya</option>
                                                <option value="Manisa">Manisa</option>
                                                <option value="Kahramanmaraş">Kahramanmaraş</option>
                                                <option value="Mardin">Mardin</option>
                                                <option value="Muğla">Muğla</option>
                                                <option value="Muş">Muş</option>
                                                <option value="Nevşehir">Nevşehir</option>
                                                <option value="Niğde">Niğde</option>
                                                <option value="Ordu">Ordu</option>
                                                <option value="Rize">Rize</option>
                                                <option value="Sakarya">Sakarya</option>
                                                <option value="Samsun">Samsun</option>
                                                <option value="Siirt">Siirt</option>
                                                <option value="Sinop">Sinop</option>
                                                <option value="Sivas">Sivas</option>
                                                <option value="Tekirdağ">Tekirdağ</option>
                                                <option value="Tokat">Tokat</option>
                                                <option value="Trabzon">Trabzon</option>
                                                <option value="Tunceli">Tunceli</option>
                                                <option value="Şanlıurfa">Şanlıurfa</option>
                                                <option value="Uşak">Uşak</option>
                                                <option value="Van">Van</option>
                                                <option value="Yozgat">Yozgat</option>
                                                <option value="Zonguldak">Zonguldak</option>
                                                <option value="Aksaray">Aksaray</option>
                                                <option value="Bayburt">Bayburt</option>
                                                <option value="Karaman">Karaman</option>
                                                <option value="Kırıkkale">Kırıkkale</option>
                                                <option value="Batman">Batman</option>
                                                <option value="Şırnak">Şırnak</option>
                                                <option value="Bartın">Bartın</option>
                                                <option value="Ardahan">Ardahan</option>
                                                <option value="Iğdır">Iğdır</option>
                                                <option value="Yalova">Yalova</option>
                                                <option value="Karabük">Karabük</option>
                                                <option value="Kilis">Kilis</option>
                                                <option value="Osmaniye">Osmaniye</option>
                                                <option value="Düzce">Düzce</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary" name="submitUser" id="submitButton">Kaydet</button>
                                        <button type="button" class="btn btn-secondary" onclick="resetForm()">Yeni Kayıt</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="panel">
                            <div class="panel-body">
                                <h4>Kullanıcı Listesi</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>#</th>
                                        <th>Öğrenci Adı</th>
                                        <th>Şifre</th>
                                        <th>Sınıf</th>
                                        <th>Veli Telefon</th>
                                        <th>Şehir</th>
                                        <th>İşlemler</th>
                                    </tr>
                                    <?php
                                    $result = $conn->query("SELECT * FROM users");
                                    if ($result->num_rows > 0) {
                                        $counter = 1;
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>{$counter}</td>
                                                    <td>{$row['studentName']}</td>
                                                    <td>{$row['password']}</td>
                                                    <td>{$row['class']}</td>
                                                    <td>{$row['parentPhone']}</td>
                                                    <td>{$row['city']}</td>
                                                    <td class='action-buttons'>
                                                        <button class='btn btn-warning' onclick='editUser({$row['id']}, \"{$row['studentName']}\", \"{$row['password']}\", \"{$row['class']}\", \"{$row['parentPhone']}\", \"{$row['city']}\")'>Düzenle</button>
                                                        <button class='btn btn-danger' onclick='deleteUser({$row['id']})'>Sil</button>
                                                    </td>
                                                  </tr>";
                                            $counter++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>Kayıt bulunamadı</td></tr>";
                                    }
                                    ?>
                                </table>
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

    <!-- start: Javascript -->
    <script src="asset/js/jquery.min.js"></script>
    <script src="asset/js/jquery.ui.min.js"></script>
    <script src="asset/js/bootstrap.min.js"></script>

    <!-- plugins -->
    <script src="asset/js/plugins/moment.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>

    <!-- custom -->
    <script src="asset/js/main.js"></script>

    <script>
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

        // Add active class to sidebar menu items
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-menu li').forEach(li => {
                    li.classList.remove('active');
                });
                this.parentElement.classList.add('active');
            });
        });

        // Existing loadStudentBooks function
        function loadStudentBooks() {
            const studentName = document.getElementById('studentSelect').value;
            const bookSelect = document.getElementById('bookSelect');

            if (!studentName) {
                bookSelect.innerHTML = '<option value="">Önce Öğrenci Seçin</option>';
                return;
            }

            fetch('get_student_books.php?student=' + encodeURIComponent(studentName))
                .then(response => response.json())
                .then(books => {
                    bookSelect.innerHTML = '<option value="">Kitap Seçin</option>';
                    books.forEach(book => {
                        bookSelect.innerHTML += `<option value="${book.book_name}">${book.book_name}</option>`;
                    });
                });
        }

        function updateHomeworkStatus(status, homeworkId) {
            fetch('update_homework_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `status=${status}&id=${homeworkId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Ödev durumu güncellendi');
                    } else {
                        alert('Bir hata oluştu');
                    }
                });
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="delete_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editUser(id, name, password, classValue, phone, city) {
            document.getElementById('edit_id').value = id;
            document.getElementById('studentName').value = name;
            document.getElementById('password').value = password;
            document.getElementById('class').value = classValue;
            document.getElementById('parentPhone').value = phone;
            document.getElementById('city').value = city; // This will now select the correct option
            document.getElementById('submitButton').textContent = "Güncelle";
            document.getElementById('userForm').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function resetForm() {
            document.getElementById("userForm").reset();
            document.getElementById("edit_id").value = "";
            document.getElementById("submitButton").textContent = "Kaydet";
        }

        function deleteBook(studentName, bookName) {
            if (confirm('Bu kitabı silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_book" value="1">
                    <input type="hidden" name="student_name" value="${studentName}">
                    <input type="hidden" name="book_name" value="${bookName}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editBook(studentName, bookName) {
            const newBookName = prompt('Yeni kitap adını girin:', bookName);
            if (newBookName && newBookName !== bookName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="update_book" value="1">
                    <input type="hidden" name="old_student_name" value="${studentName}">
                    <input type="hidden" name="old_book_name" value="${bookName}">                    <input type="hidden" name="new_book_name" value="${newBookName}">                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function filterHomework() {
            const selectedStudent = document.querySelector('#studentSelect').value;
            const rows = document.querySelectorAll('#homework-list table tbody tr');
            rows.forEach(row => {
                const studentName = row.querySelector('td:first-child').textContent;
                if (selectedStudent === "" || studentName === selectedStudent) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Initialize showCompleted as false (hide completed homework by default)
        let showCompleted = false;

        // Add window.onload event handler
        window.onload = function() {
            filterHomework(); // This will hide completed homework when page loads
        }

        function toggleCompletedHomework() {
            showCompleted = !showCompleted;
            filterHomework();
            const button = document.querySelector('.toggle-completed');
            button.textContent = showCompleted ? 'Tamamlanan Ödevleri Gizle' : 'Tamamlanan Ödevleri Göster';
        }

        function filterHomework() {
            const selectedStudent = document.querySelector('#studentSelect').value;
            const rows = document.querySelectorAll('#homework-list table tbody tr');
            rows.forEach(row => {
                const studentName = row.querySelector('td:first-child').textContent;
                const status = row.querySelector('td:nth-child(6)').textContent;
                const shouldShow = (selectedStudent === "" || studentName === selectedStudent) &&
                    (showCompleted || status !== "Yapıldı");
                row.style.display = shouldShow ? "" : "none";
            });
        }

        function editAnnouncement(id, title, content) {
            document.getElementById('announcement_id').value = id;
            document.getElementById('announcement_title').value = title;
            document.getElementById('announcement_content').value = content;
            document.getElementById('announcementButton').textContent = "Güncelle";
            document.getElementById('announcementForm').setAttribute('name', 'edit_announcement');
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
            document.getElementById('announcement_id').value = "";
            document.getElementById('announcementButton').textContent = "Duyuru Ekle";
            document.getElementById('announcementForm').setAttribute('name', 'submitAnnouncement');
        }

        function editMessage(id, student, content) {
            document.getElementById('message_id').value = id;
            document.getElementById('message_student').value = student;
            document.getElementById('message_content').value = content;
            document.getElementById('messageButton').textContent = "Güncelle";
            document.getElementById('messageButton').name = "edit_message";
            document.getElementById('messageForm').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function deleteMessage(id) {
            if (confirm('Bu mesajı silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="delete_message" value="1">
                                <input type="hidden" name="message_id" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetMessageForm() {
            document.getElementById('messageForm').reset();
            document.getElementById('message_id').value = "";
            document.getElementById('messageButton').textContent = "Mesaj Gönder";
            document.getElementById('messageButton').name = "send_message";
        }
    </script>

    <?php $conn->close(); ?>
</body>

</html>