<?php
include 'config.php';
date_default_timezone_set('Asia/Bangkok');
include 'db.php';
session_start();

// ตรวจสอบการเข้าสู่ระบบและบทบาท
$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? "Guest";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = '+07:00'");

// การจัดการการบันทึกการแจ้งเตือน
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['title'])) {
        // สำหรับการเพิ่มการแจ้งเตือนใหม่
        $title = $conn->real_escape_string($_POST['title']);
        $repair_id = $conn->real_escape_string($_POST['repair_id']);
        $description = $conn->real_escape_string($_POST['description']);
        $location = $conn->real_escape_string($_POST['location']);
        $scheduled_datetime = $conn->real_escape_string($_POST['scheduled_datetime']);
        $scheduled_datetime = date('Y-m-d H:i:s', strtotime($scheduled_datetime));

        $sql = "INSERT INTO notifications (title, description, location, scheduled_datetime, status, repair_id) 
                VALUES ('$title', '$description', '$location', '$scheduled_datetime', 'pending', '$repair_id')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('การแจ้งเตือนถูกบันทึกเรียบร้อยแล้ว!');</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาด: " . $conn->error . "');</script>";
        }
    } else if (isset($_POST['repair_id'])) {
        // สำหรับการเลือกการซ่อม
        $repair_id = $conn->real_escape_string($_POST['repair_id']);
        $scheduled_datetime = $conn->real_escape_string($_POST['scheduled_datetime']);
        $scheduled_datetime = date('Y-m-d H:i:s', strtotime($scheduled_datetime));

        // ดึงข้อมูลการซ่อมที่เลือก
        $repair_data_sql = "SELECT repair_name, description, location FROM repair_request WHERE id = '$repair_id'";
        $repair_data_stmt = $conn->query($repair_data_sql);
        $repair_data = $repair_data_stmt->fetch_assoc();

        $title = $repair_data['repair_name'];
        $description = $repair_data['description'];
        $location = $repair_data['location'];

        // อัพเดตการซ่อมที่ถูกเลือกเป็น 1 (เลือกแล้ว)
        $update_repair_sql = "UPDATE repair_request SET is_selected = 1 WHERE id = '$repair_id'";
        $conn->query($update_repair_sql);

        // เพิ่มการแจ้งเตือนใหม่
        $sql = "INSERT INTO notifications (title, description, location, scheduled_datetime, status, repair_id) 
                VALUES ('$title', '$description', '$location', '$scheduled_datetime', 'pending', '$repair_id')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('การแจ้งเตือนถูกบันทึกเรียบร้อยแล้ว!');</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาด: " . $conn->error . "');</script>";
        }
    }
}

// ดึงข้อมูลรายการซ่อมทั้งหมดที่ยังไม่ได้เลือก
$repair_sql = "SELECT id, repair_name, description, location, report_date FROM repair_request WHERE is_selected = 0 ORDER BY report_date DESC";
$repair_stmt = $conn->query($repair_sql);
$repair_reports = $repair_stmt->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างการแจ้งเตือน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-gray-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="img/Hr.png" alt="Logo" width="40" height="40" class="me-2">
            <span>ระบบแจ้งซ่อม</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="report.php">แจ้งซ่อม</a>
                </li>

                <?php if ($user_role === 'tech' || $user_role === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            การจัดการข้อมูล
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="managementDropdown">
                            <li><a class="dropdown-item" href="main.php">สร้างการแจ้งเตือน</a></li>
                            <li><a class="dropdown-item" href="repair_list.php">รายการการแจ้งซ่อม</a></li>
                            <?php if ($user_role === 'admin'): ?>
                                <li><a class="dropdown-item" href="manage_user.php">ข้อมูลผู้ใช้</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($user_role === 'tech' || $user_role === 'user' || $user_role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">โปรไฟล์ผู้ใช้</a>
                    </li>
                <?php endif; ?>
                <?php if ($user_role === 'tech' || $user_role === 'user' || $user_role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">ออกจากระบบ</a>
                    </li>
                <?php endif; ?>
                <?php if ($user_role === null): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">เข้าสู่ระบบ</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mx-auto p-8">
    <div class="mb-4">
        <a href="main.php" class="bg-red-500 text-white p-2 rounded mr-2">ย้อนกลับ</a>
        <a href="manage.php" class="bg-blue-500 text-white p-2 rounded mr-2">สร้างการแจ้งเตือน</a>
        <a href="calendar.php" class="bg-yellow-500 text-white p-2 rounded">ปฏิทิน</a>
    </div>

    <h1 class="text-3xl font-semibold mb-6 text-center">สร้างการแจ้งเตือน</h1>

    <form method="POST" class="bg-white p-6 rounded-lg shadow-lg">
        <label for="repair_id" class="block text-gray-700">ตัวเลือกรายการซ่อม:</label>
        <select name="repair_id" id="repair_id" class="w-full p-2 border border-gray-300 rounded mb-4" required>
            <option value="" disabled selected>เลือกการซ่อม</option>
            <?php foreach ($repair_reports as $report): ?>
                <option value="<?php echo $report['id']; ?>">
                    <?php echo htmlspecialchars($report['repair_name']) . " - " . htmlspecialchars($report['location']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="scheduled_datetime" class="block text-gray-700">วันเวลาแจ้งเตือน:</label>
        <input type="datetime-local" name="scheduled_datetime" id="scheduled_datetime" class="w-full p-2 border border-gray-300 rounded mb-4" required>

        <button type="submit" class="w-full bg-blue-500 text-white p-3 rounded">บันทึกการแจ้งเตือน</button>
    </form>
</div>

<script>
    function checkNotifications() {
        $.ajax({
            url: 'check_notifications.php',
            method: 'GET',
            success: function(response) {
                if (response === 'notify') {
                    alert("ถึงเวลาการแจ้งเตือน!");
                }
            }
        });
    }
    setInterval(checkNotifications, 1000);
</script>

</body>
</html>
