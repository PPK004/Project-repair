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


$sql = "SELECT title, scheduled_datetime, description, location FROM notifications";
$result = $conn->query($sql);
$events = [];

while ($row = $result->fetch_assoc()) {
    $scheduled_datetime = new DateTime($row['scheduled_datetime'], new DateTimeZone('Asia/Bangkok'));
    
    $events[] = [
        'title' => $row['title'],
        'start' => $scheduled_datetime->format('Y-m-d\TH:i:s'),
        'description' => $row['description'],
        'location' => $row['location'],
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปฏิทินการแจ้งเตือน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
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
            <a href="manage.php" class="bg-blue-500 text-white p-2 rounded mr-2">สร้างการแจ้งเตือน</a>
            <a href="calendar.php" class="bg-yellow-500 text-white p-2 rounded">ปฏิทิน</a>
        </div>

        <h1 class="text-3xl font-semibold mb-6 text-center">ปฏิทินการแจ้งเตือน</h1>

        <div class="flex justify-center mb-4">
            <select id="yearSelect" class="bg-white p-2 rounded">
                <?php
                $currentYear = date('Y');
                for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                    echo "<option value='$i' " . ($i == $currentYear ? 'selected' : '') . ">$i</option>";
                }
                ?>
            </select>
            <select id="monthSelect" class="bg-white p-2 rounded ml-2">
                <?php
                $months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                for ($i = 0; $i < 12; $i++) {
                    echo "<option value='" . ($i+1) . "' " . ($i+1 == date('n') ? 'selected' : '') . ">{$months[$i]}</option>";
                }
                ?>
            </select>
        </div>

        <div id="calendar"></div>
    </div>

    <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden justify-center items-start z-50">
        <div class="bg-white p-6 rounded-lg w-1/2 max-w-lg mt-6 mx-auto">
            <h2 class="text-xl font-semibold mb-4 text-center" id="modalTitle"></h2>
            <p id="modalStart"></p>
            <p id="modalDescription"></p>
            <p id="modalLocation"></p>
            <button class="mt-4 px-4 py-2 bg-blue-500 text-white rounded" onclick="closeModal()">ปิด</button>
        </div>
    </div>

    <script>
        function openModal(title, start, description, location) {
            document.getElementById('modalTitle').innerText = title;
            const date = new Date(start);
            const thaiDate = formatThaiDate(date);
            document.getElementById('modalStart').innerText = 'วันที่: ' + thaiDate;
            document.getElementById('modalDescription').innerText = 'รายละเอียด: ' + description;
            document.getElementById('modalLocation').innerText = 'สถานที่: ' + location;
            document.getElementById('eventModal').classList.remove('hidden');
        }

        function formatThaiDate(date) {
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear() + 543;
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const seconds = date.getSeconds().toString().padStart(2, '0');
            return `${day.toString().padStart(2, '0')}/${month.toString().padStart(2, '0')}/${year} ${hours}:${minutes}:${seconds}`;
        }

        function closeModal() {
            document.getElementById('eventModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?php echo json_encode($events); ?>,
                eventClick: function(info) {
                    var event = info.event;
                    var title = event.title;
                    var start = event.start.toISOString().slice(0, 19).replace("T", " ");
                    var description = event.extendedProps.description;
                    var location = event.extendedProps.location;
                    openModal(title, start, description, location); 
                },
                locale: 'th',
                eventColor: '#378006',
                timeZone: 'Asia/Bangkok',
                eventTimeFormat: { 
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: 'short'
                }
            });
            calendar.render();

            document.getElementById('yearSelect').addEventListener('change', function() {
                var selectedYear = this.value;
                var selectedMonth = document.getElementById('monthSelect').value;
                calendar.gotoDate(selectedYear + '-' + selectedMonth + '-01');
            });

            document.getElementById('monthSelect').addEventListener('change', function() {
                var selectedYear = document.getElementById('yearSelect').value;
                var selectedMonth = this.value;
                calendar.gotoDate(selectedYear + '-' + selectedMonth + '-01');
            });
        });
    </script>
</body>
</html>
