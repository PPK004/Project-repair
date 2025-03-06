<?php
include 'db.php';
session_start();

// Query นับจำนวนแต่ละสถานะ
$sql = "SELECT 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
        FROM notifications";

$stmt = $conn->prepare($sql);
$stmt->execute();
$status_counts = $stmt->fetch(PDO::FETCH_ASSOC);

// กำหนดค่าให้ตัวแปร
$pending = $status_counts['pending'] ?? 0;
$in_progress = $status_counts['in_progress'] ?? 0;
$completed = $status_counts['completed'] ?? 0;

// ตรวจสอบการเข้าสู่ระบบและบทบาท
$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? "Guest";

// ค่าจำนวนรายการที่แสดงต่อหน้า
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ดึงข้อมูลการแจ้งซ่อมพร้อมชื่อผู้ใช้
$sql = "SELECT rr.id, rr.repair_name, rr.location, rr.status, rr.image, rr.image_type, rr.report_date, rr.user_id
        FROM repair_request rr
        ORDER BY rr.report_date DESC
        LIMIT $limit OFFSET $offset";
$stmt = $conn->query($sql);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงชื่อผู้ที่แจ้งซ่อมจาก user_id
foreach ($reports as &$report) {
    $user_id = $report['user_id'];
    $user_sql = "SELECT first_name, last_name FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    // แสดงชื่อผู้แจ้งซ่อม
    $report['username'] = $user['first_name'] . ' ' . $user['last_name'];
}

// ดึงข้อมูลการแจ้งเตือน
$note_sql = "SELECT repair_id, title, scheduled_datetime, description, location, status FROM notifications ORDER BY scheduled_datetime ASC";
$note_stmt = $conn->query($note_sql);
$notes = $note_stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงจำนวนรายการทั้งหมด
$total_sql = "SELECT COUNT(*) FROM repair_request";
$total_stmt = $conn->query($total_sql);
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบแจ้งซ่อม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment-with-locales.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        #calendar {
            margin-top: 20px;
        }
        .note-details {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .list-group-item {
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
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

                    </br>
    <?php if ($user_role === 'admin' || $user_role === 'tech'): ?>


    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>ปฏิทิน</h4>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4>รายการงานที่ต้องดำเนินการซ่อมแซม</h4>
                        <div style="display: flex; flex-direction: column; gap: 15px; font-size: 22px;">
    <span class="badge bg-primary" style="min-width: 250px; text-align: center; padding: 10px;">ยังไม่เสร็จ: <?php echo $pending; ?></span> 
    <span class="badge bg-warning" style="min-width: 250px; text-align: center; padding: 10px;">กำลังดำเนินการ: <?php echo $in_progress; ?></span>
    <span class="badge bg-success" style="min-width: 250px; text-align: center; padding: 10px;">เสร็จสมบูรณ์แล้ว: <?php echo $completed; ?></span>
</div>

</div>
                <?php foreach ($notes as $note): ?>
            <li class="list-group-item">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <!-- ชื่อเรื่อง -->
                    <strong><?php echo htmlspecialchars($note['title']); ?></strong>

                    <div style="display: flex; gap: 15px;">
                        <?php 
                            // กำหนดสถานะและสีของ badge
                            $status = htmlspecialchars($note['status']);
                            $statusText = '';
                            $badgeClass = '';

                            if ($status == 'pending') {
                                $statusText = 'ยังไม่ได้ดำเนินการ';
                                $badgeClass = 'bg-primary';
                            } elseif ($status == 'in_progress') {
                                $statusText = 'อยู่ระหว่างดำเนินการ';
                                $badgeClass = 'bg-warning';
                            } elseif ($status == 'completed') {
                                $statusText = 'เสร็จสมบูรณ์แล้ว';
                                $badgeClass = 'bg-success';
                            } else {
                                $statusText = 'ไม่ทราบสถานะ';
                                $badgeClass = 'bg-secondary';
                            }
                        ?>

                        <!-- สถานะ -->
                        <span class="badge <?php echo $badgeClass; ?>" style="font-size: 14px;">
                            <?php echo $statusText; ?>
                        </span>

                        <!-- ID อ้างอิง -->
                        <span class="badge bg-secondary" style="font-size: 14px;">
                            ID อ้างอิง : <?php echo htmlspecialchars($note['repair_id']); ?>
                        </span>
                    </div>
                </div>

                <br>

                <?php 
                    $datetime = new DateTime($note['scheduled_datetime'], new DateTimeZone('Asia/Bangkok'));
                    echo $datetime->format('d/m/Y H:i');
                ?>

                <button class="btn btn-info btn-sm mt-2" onclick="showDetails('<?php echo htmlspecialchars($note['title']); ?>')">ดูข้อมูลเพิ่มเติม</button>
                
                <div id="details-<?php echo $note['title']; ?>" class="note-details">
                    <p><strong>สถานะ:</strong> <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span></p>
                    <p><strong>ID อ้างอิง:</strong> <?php echo htmlspecialchars($note['repair_id']); ?></p>

                    <?php
                        $repair_id = $note['repair_id'];
                        $user_sql = "SELECT u.first_name, u.last_name 
                                     FROM repair_request rr 
                                     JOIN users u ON rr.user_id = u.id 
                                     WHERE rr.id = ?";
                        $user_stmt = $conn->prepare($user_sql);
                        $user_stmt->execute([$repair_id]);
                        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
                        $username = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'ไม่ทราบชื่อผู้แจ้ง';
                    ?>
                    <p><strong>ชื่อผู้แจ้ง:</strong> <?php echo htmlspecialchars($username); ?></p>
                    <p><strong>เวลาที่กำหนด:</strong> <?php echo $datetime->format('d/m/Y H:i'); ?></p>
                    <p><strong>รายละเอียด:</strong> <?php echo htmlspecialchars($note['description'] ?? 'ไม่มีรายละเอียด'); ?></p>
                    <p><strong>สถานที่:</strong> <?php echo htmlspecialchars($note['location']); ?></p>
                </div>
            </li>
        <?php endforeach; ?>

                </div>
            </div>

            <div class="col-md-8">

            
        
                <h2>รายการการแจ้งซ่อม</h2>
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>ลำดับที่</th>
                            <th>เลข ID</th>
                            <th>ผู้แจ้ง</th>
                            <th>ชื่อการซ่อม</th>
                            <th>สถานที่</th>
                            <th>สถานะ</th>
                            <th>รูปภาพ</th>
                            <?php if ($user_role === 'admin'): ?>
                                <th>การจัดการ</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $index => $report): ?>
                            <tr>
                                <td><?php echo ($offset + $index + 1); ?></td>
                                <td><?php echo htmlspecialchars($report['id']); ?></td>
                                <td><?php echo htmlspecialchars($report['username']); ?></td>
                                <td><?php echo htmlspecialchars($report['repair_name']); ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td><?php echo htmlspecialchars($report['status']); ?></td>
                                <td>
                                    <?php if (!empty($report['image'])): ?>
                                        <img src="data:<?php echo htmlspecialchars($report['image_type']); ?>;base64,<?php echo base64_encode($report['image']); ?>" 
                                             alt="รูปภาพแจ้งซ่อม" style="width: 100px; height: auto;">
                                    <?php else: ?>
                                        <span>ไม่มีรูปภาพ</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($user_role === 'admin'): ?>
                                    <td>
                                        <a href="edit.php?id=<?php echo $report['id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                                        <a href="delete.php?id=<?php echo $report['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้?');">ลบ</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- แสดงปุ่มเปลี่ยนหน้า -->
    <nav>
        <ul class="pagination">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">ก่อนหน้า</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">ถัดไป</a>
            </li>
        </ul>
    </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'th',
            timeZone: 'Asia/Bangkok',
            initialView: 'dayGridMonth',
            events: [
                <?php foreach ($notes as $note): ?>
                    {
                        title: '<?php echo htmlspecialchars($note['title']); ?>',
                        start: '<?php echo $note['scheduled_datetime']; ?>',
                        id: '<?php echo $note['title']; ?>',  // Add unique event identifier
                    },
                <?php endforeach; ?>
            ],
            eventClick: function(info) {
                // Redirect to calendar.php with event details (using event ID or title)
                window.location.href = 'calendar.php?event_id=' + info.event.id;
            }
        });
        calendar.render();
    });

    function showDetails(title) {
        var detailsElement = document.getElementById("details-" + title);
        if (detailsElement.style.display === "none") {
            detailsElement.style.display = "block";
        } else {
            detailsElement.style.display = "none";
        }
    }
</script>

<?php endif; ?>

<?php if ($user_role === 'user' || $user_role === null): ?>

    <div class="container mt-4">
         <h2>รายการการแจ้งซ่อม</h2>
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>ผู้แจ้ง</th>
                            <th>ชื่อการซ่อม</th>
                            <th>สถานที่</th>
                            <th>สถานะ</th>
                            <th>รูปภาพ</th>
                            <?php if ($user_role === 'admin'): ?>
                                <th>การจัดการ</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $index => $report): ?>
                            <tr>
                                <td><?php echo ($offset + $index + 1); ?></td>
                                <td><?php echo htmlspecialchars($report['username']); ?></td>
                                <td><?php echo htmlspecialchars($report['repair_name']); ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td><?php echo htmlspecialchars($report['status']); ?></td>
                                <td>
                                    <?php if (!empty($report['image'])): ?>
                                        <img src="data:<?php echo htmlspecialchars($report['image_type']); ?>;base64,<?php echo base64_encode($report['image']); ?>" 
                                             alt="รูปภาพแจ้งซ่อม" style="width: 100px; height: auto;">
                                    <?php else: ?>
                                        <span>ไม่มีรูปภาพ</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($user_role === 'admin'): ?>
                                    <td>
                                        <a href="edit.php?id=<?php echo $report['id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                                        
                                        <a href="delete.php?id=<?php echo $report['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้?');">ลบ</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- แสดงปุ่มเปลี่ยนหน้า -->
    <nav>
        <ul class="pagination">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>">ก่อนหน้า</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>">ถัดไป</a>
            </li>
        </ul>
    </nav>
    </div>

    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
