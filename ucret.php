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

$alertMessage = '';
$alertType = '';

// Default times
$default_start_time = "20:00";
$default_end_time = date("H:i", strtotime($default_start_time) + 40 * 60);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    if (
        empty($_POST['student_id']) || empty($_POST['lesson_date']) ||
        empty($_POST['start_time']) || empty($_POST['end_time']) || empty($_POST['fee'])
    ) {
        $alertMessage = 'Lütfen tüm alanları doldurun!';
        $alertType = 'danger';
    } else {
        $student_id = $_POST['student_id'];
        $lesson_date = $_POST['lesson_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $fee = (float)$_POST['fee'];
        $status = 'Ödenecek'; // Set status to "Ödenecek" automatically

        // Use the fee directly without calculating based on hours
        $total_fee = $fee;

        $stmt = $conn->prepare("INSERT INTO lessons (student_id, lesson_date, start_time, end_time, fee, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssds", $student_id, $lesson_date, $start_time, $end_time, $total_fee, $status);

        if ($stmt->execute()) {
            // Başarılı kayıt sonrası yönlendirme yap
            $_SESSION['alertMessage'] = 'Ders ücreti başarıyla eklendi.';
            $_SESSION['alertType'] = 'success';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $alertMessage = 'Hata: ' . $conn->error;
            $alertType = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    if (
        empty($_POST['student_name']) || empty($_POST['lesson_count']) ||
        empty($_POST['amount']) || empty($_POST['payment_date'])
    ) {
        $alertMessage = 'Lütfen tüm alanları doldurun!';
        $alertType = 'danger';
    } else {
        $student_id = $_POST['student_name'];
        $lesson_count = (int)$_POST['lesson_count'];
        $amount_per_lesson = (float)$_POST['amount'];
        $payment_date = $_POST['payment_date'];
        $total_fee = (string)($lesson_count * $amount_per_lesson); // Convert to string
        $status = 'Ödenecek';

        // Set default times
        $default_start_time = "00:00";
        $default_end_time = "00:00";

        // Update query to explicitly include status
        $stmt = $conn->prepare("INSERT INTO lessons (student_id, lesson_date, start_time, end_time, fee, status, lesson_count, amount_per_lesson) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param(
                "isssssis", // Changed parameter types - all strings except student_id and lesson_count
                $student_id,
                $payment_date,
                $default_start_time,
                $default_end_time,
                $total_fee,    // Now passed as string
                $status,
                $lesson_count, // Integer
                $amount_per_lesson
            );

            if ($stmt->execute()) {
                $_SESSION['alertMessage'] = 'Ders ücreti başarıyla eklendi.';
                $_SESSION['alertType'] = 'success';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $alertMessage = 'Hata: ' . $stmt->error;
                $alertType = 'danger';
            }
        } else {
            $alertMessage = 'Hata: ' . $conn->error;
            $alertType = 'danger';
        }
    }
}

// Handle multiple deletions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_selected'])) {
    $selected_ids = $_POST['selected_ids'];
    if (!empty($selected_ids)) {
        $ids = implode(',', $selected_ids);
        $stmt = $conn->prepare("DELETE FROM lessons WHERE id IN ($ids)");
        if ($stmt->execute()) {
            $_SESSION['alertMessage'] = 'Seçili dersler başarıyla silindi.';
            $_SESSION['alertType'] = 'success';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $alertMessage = 'Hata: ' . $conn->error;
            $alertType = 'danger';
        }
    }
}

// Update the archive handler
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['archive_lesson'])) {
    $lesson_id = $_POST['lesson_id'];
    $stmt = $conn->prepare("UPDATE lessons SET archived = 1, status = 'Ödendi' WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    
    if ($stmt->execute()) {
        $_SESSION['alertMessage'] = 'Ders arşivlendi ve ödenmiş olarak işaretlendi.';
        $_SESSION['alertType'] = 'success';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $alertMessage = 'Hata: ' . $conn->error;
        $alertType = 'danger';
    }
}

// Add this after other POST handlers
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_lesson'])) {
    $lesson_id = $_POST['lesson_id'];
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    
    if ($stmt->execute()) {
        $_SESSION['alertMessage'] = 'Ders başarıyla silindi.';
        $_SESSION['alertType'] = 'success';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $alertMessage = 'Hata: ' . $conn->error;
        $alertType = 'danger';
    }
}

// Update the mark_paid handler
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_paid'])) {
    $lesson_id = $_POST['lesson_id'];
    $stmt = $conn->prepare("UPDATE lessons SET status = 'Ödendi', archived = 1 WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    
    if ($stmt->execute()) {
        $_SESSION['alertMessage'] = 'Ödeme durumu güncellendi ve otomatik olarak arşivlendi.';
        $_SESSION['alertType'] = 'success';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $alertMessage = 'Hata: ' . $conn->error;
        $alertType = 'danger';
    }
}

// Add this after other POST handlers
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_unpaid'])) {
    $lesson_id = $_POST['lesson_id'];
    $stmt = $conn->prepare("UPDATE lessons SET status = 'Ödenecek', archived = 0 WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    
    if ($stmt->execute()) {
        $_SESSION['alertMessage'] = 'Ödeme durumu "Ödenecek" olarak güncellendi.';
        $_SESSION['alertType'] = 'success';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $alertMessage = 'Hata: ' . $conn->error;
        $alertType = 'danger';
    }
}

// Uyarı mesajlarını session'dan al
if (isset($_SESSION['alertMessage'])) {
    $alertMessage = $_SESSION['alertMessage'];
    $alertType = $_SESSION['alertType'];
    unset($_SESSION['alertMessage']);
    unset($_SESSION['alertType']);
} else {
    $alertMessage = '';
    $alertType = '';
}

// Fetch students from users table
$students = $conn->query("SELECT id, studentName FROM users ORDER BY studentName");

// Fetch lessons from lessons table
// Update the query to include status in the table columns
$show_archive = isset($_GET['archive']) && $_GET['archive'] == '1';
$show_paid = isset($_GET['paid']) && $_GET['paid'] == '1';

// Add near the top where other GET parameters are defined
$selected_student = isset($_GET['student']) ? (int)$_GET['student'] : 0;

// Update the lessons query
$lessons = $conn->query("SELECT l.id, l.student_id, u.studentName, l.lesson_date, l.start_time, 
                                l.end_time, l.fee, l.status, l.lesson_count 
                                FROM lessons l 
                                JOIN users u ON l.student_id = u.id 
                                WHERE l.status " . ($show_paid ? "= 'Ödendi'" : "!= 'Ödendi'") . 
                                ($selected_student ? " AND l.student_id = " . $selected_student : "") . "
                                ORDER BY l.lesson_date DESC");

// Calculate total fee excluding "Ödendi" status
$total_fee_result = $conn->query("SELECT SUM(fee) AS total_fee FROM lessons WHERE status != 'Ödendi'");
$total_fee_row = $total_fee_result->fetch_assoc();
$total_fee = $total_fee_row['total_fee'];

// Update the totals query to consider selected student
if ($selected_student) {
    $totals_result = $conn->query("SELECT 
        SUM(fee) AS total_fee, 
        SUM(lesson_count) AS total_lessons,
        u.studentName 
    FROM lessons l
    JOIN users u ON l.student_id = u.id 
    WHERE l.status != 'Ödendi' 
    AND l.archived = 0 
    AND l.student_id = " . $selected_student);
} else {
    $totals_result = $conn->query("SELECT 
        SUM(fee) AS total_fee, 
        SUM(lesson_count) AS total_lessons 
    FROM lessons 
    WHERE status != 'Ödendi' 
    AND archived = 0");
}

$totals_row = $totals_result->fetch_assoc();
$total_fee = $totals_row['total_fee'];
$total_lessons = $totals_row['total_lessons'];
$student_name = isset($totals_row['studentName']) ? $totals_row['studentName'] : '';

ob_start();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ders Ücreti</title>

    <!-- start: Css -->
    <link rel="stylesheet" type="text/css" href="asset/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/simple-line-icons.css" />
    <link rel="stylesheet" type="text/css" href="asset/css/plugins/animate.min.css" />
    <link href="asset/css/style.css" rel="stylesheet">
    
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
    <div id="loading">
        <div class="loader"></div>
    </div>
    <nav class="navbar navbar-default header navbar-fixed-top">
        <div class="col-md-12 nav-wrapper">
            <div class="navbar-header" style="width:100%;">
                <div class="opener-left-menu is-open">
                    <span class="top"></span>
                    <span class="middle"></span>
                    <span class="bottom"></span>
                </div>
                <a href="index.html" class="navbar-brand">
                    <b>Öğrenci Yönetim Paneli</b>
                </a>
                <ul class="nav navbar-nav navbar-right user-nav">
                    <li class="user-name"><span>Admin</span></li>
                    <li class="dropdown avatar-dropdown">
                        <img src="asset/img/avatar.jpg" class="img-circle avatar" alt="user name" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="true" />
                        <ul class="dropdown-menu user-dropdown">
                            <li><a href="logout.php"><span class="fa fa-power-off"></span> Çıkış Yap</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Left Menu -->
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
                        <li class="ripple"><a href="add_book.php"><span class="fa fa-book"></span>Kitap İşlemleri</a></li>
                        <li class="active ripple"><a href="ucret.php"><span class="fa-money fa"></span>Ders Ücreti</a></li>
                        <li class="ripple"><a href="logout.php"><span class="fa fa-sign-out"></span>Çıkış</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="content">
            <div class="panel box-shadow-none content-header">
                <div class="panel-body">
                    <div class="col-md-12">
                        <h3 class="animated fadeInLeft">Ders Ücreti</h3>
                    </div>
                </div>
            </div>

            <?php if ($alertMessage): ?>
                <div class="alert alert-<?= $alertType ?>"><?= $alertMessage ?></div>
            <?php endif; ?>

            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="row">
                            <!-- Fee entry panel -->
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Yeni Ücret</div>
                                    <div class="panel-body">
                                        <form method="POST" class="fee-form">
                                            <div class="form-group">
                                                <label for="student_name">Öğrenci Seçin</label>
                                                <select name="student_name" id="student_name" class="form-control" 
                                                onchange="window.location.href='?student='+this.value" required>
                                                <option value="">Öğrenci Seçin</option>
                                                <?php while ($student = $students->fetch_assoc()): ?>
                                                    <option value="<?= $student['id'] ?>" 
                                                        <?= $selected_student == $student['id'] ? 'selected' : '' ?>>
                                                        <?= $student['studentName'] ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="lesson_count">Ders Sayısı</label>
                                                <input type="number" name="lesson_count" id="lesson_count" class="form-control" min="1" value="1" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="amount">Ders Ücreti (Ders Başına)</label>
                                                <input type="number" name="amount" id="amount" class="form-control"
                                                    value="400" min="50" step="50" placeholder="Ders başına ücret" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="payment_date">Tarih</label>
                                                <input type="date" name="payment_date" id="payment_date" 
                                                    value="<?= date('Y-m-d') ?>" 
                                                    class="form-control" required>
                                            </div>
                                            <button type="submit" name="submit_payment" class="btn btn-primary">Ödeme Ekle</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee table panel -->
                            <div class="col-md-8">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        Ücret Listesi
                                        <div class="pull-right">
                                            <?php if ($show_paid): ?>
                                                <a href="?paid=0" class="btn btn-warning btn-sm">
                                                    <i class="fa fa-clock-o"></i> Ödenecekleri Göster
                                                </a>
                                            <?php else: ?>
                                                <a href="?paid=1" class="btn btn-success btn-sm">
                                                    <i class="fa fa-check"></i> Ödenenleri Göster
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Öğrenci</th>
                                                    <th>Ders Sayısı</th>
                                                    <th>Ücret</th>
                                                    <th>Tarih</th>
                                                    <th>Durum</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($lesson = $lessons->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $lesson['studentName'] ?></td>
                                                        <td><?= $lesson['lesson_count'] ?? 1 ?></td>
                                                        <td><?= $lesson['fee'] ?> TL</td>
                                                        <td><?= $lesson['lesson_date'] ?></td>
                                                        <td><?= $lesson['status'] ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <?php if ($lesson['status'] !== 'Ödendi'): ?>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                                                        <button type="submit" name="mark_paid" class="btn btn-success btn-sm" 
                                                                            onclick="return confirm('Ödeme durumunu \'Ödendi\' olarak işaretlemek istediğinize emin misiniz?')">
                                                                            <i class="fa fa-check"></i> Ödendi
                                                                        </button>
                                                                    </form>
                                                                <?php else: ?>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                                                        <button type="submit" name="mark_unpaid" class="btn btn-warning btn-sm" 
                                                                            onclick="return confirm('Ödeme durumunu \'Ödenecek\' olarak işaretlemek istediğinize emin misiniz?')">
                                                                            <i class="fa fa-clock-o"></i> Ödenecek
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                                <form method="POST" style="display: inline; margin-left: 5px;">
                                                                    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                                                    <button type="submit" name="delete_lesson" class="btn btn-danger btn-sm" 
                                                                        onclick="return confirm('Bu dersi silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')">
                                                                        <i class="fa fa-trash"></i> Sil
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                <!-- Add totals row -->
                                                <tr class="info">
                                                    <td><strong><?= $selected_student ? $student_name : 'GENEL TOPLAM' ?></strong></td>
                                                    <td><strong><?= $total_lessons ?? 0 ?> Ders</strong></td>
                                                    <td colspan="4"><strong><?= number_format($total_fee ?? 0, 2) ?> TL</strong></td>
                                                </tr>
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

    <!-- start:javascript -->
    <script src="asset/js/jquery.min.js"></script>
    <script src="asset/js/jquery.ui.min.js"></script>
    <script src="asset/js/bootstrap.min.js"></script>
    <script src="asset/js/plugins/jquery.nicescroll.js"></script>
    <script src="asset/js/main.js"></script>

    <script>
        $(document).ready(function() {
            // Only keep menu toggle handler
            $('.tree-toggle').click(function(e) {
                e.preventDefault();
                const $submenu = $(this).parent().children('ul.tree');
                const $arrow = $(this).find('.right-arrow');

                $submenu.toggleClass('active').slideToggle(200);
                $arrow.toggleClass('fa-angle-right fa-angle-down');
            });
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>