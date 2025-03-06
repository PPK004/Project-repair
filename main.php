<?php
include 'config.php';
session_start();

// ตรวจสอบการเข้าสู่ระบบและบทบาท
$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? "Guest";

// ดึงข้อมูลการแจ้งเตือนทั้งหมด
$sql = "SELECT id, repair_id, title, scheduled_datetime, status FROM notifications ORDER BY scheduled_datetime DESC";
$result = $conn->query($sql);
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// คำนวณจำนวนสถานะต่างๆ
$status_count = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
foreach ($notifications as $notification) {
    $status_count[$notification['status']]++;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการแจ้งเตือน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
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
        <div class="mb-6 flex justify-between items-center">
        <?php if ($user_role === 'admin'): ?>
            <div>
                <a href="manage.php" class="bg-blue-500 text-white p-2 rounded mr-2">สร้างการแจ้งเตือน</a>
                <a href="calendar.php" class="bg-yellow-500 text-white p-2 rounded">ปฏิทิน</a>
            </div>
            <?php endif; ?>

            <div class="flex space-x-4">
                <div class="bg-blue-500 text-white p-2 rounded">ยังไม่เสร็จ: <?php echo $status_count['pending']; ?></div>
                <div class="bg-yellow-500 text-white p-2 rounded">กำลังดำเนินการ: <?php echo $status_count['in_progress']; ?></div>
                <div class="bg-green-500 text-white p-2 rounded">เสร็จแล้ว: <?php echo $status_count['completed']; ?></div>
            </div>
        </div>

        <h1 class="text-3xl font-semibold mb-6 text-center">รายการแจ้งเตือน</h1>

        <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-3 text-left">ลำดับ</th>
                    <th class="p-3 text-left">ID อ้างอิง</th>
                    <th class="p-3 text-left">หัวข้อ</th>
                    <th class="p-3 text-left">วันที่/เวลา</th>
                    <th class="p-3 text-left">สถานะ</th>
                    <th class="p-3 text-left">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notification) : ?>
                    <tr class="border-t">
                        <td class="p-3"><?php echo $notification['id']; ?></td>
                        <td class="p-3"><?php echo $notification['repair_id']; ?></td>
                        <td class="p-3"><?php echo $notification['title']; ?></td>
                        <td class="p-3"><?php echo date("d/m/Y H:i", strtotime($notification['scheduled_datetime'])); ?></td>
                        <td class="p-3">
                            <?php
                            switch ($notification['status']) {
                                case 'pending':
                                    echo "<span class='text-yellow-500'>ยังไม่เสร็จ</span>";
                                    break;
                                case 'in_progress':
                                    echo "<span class='text-blue-500'>กำลังดำเนินการ</span>";
                                    break;
                                case 'completed':
                                    echo "<span class='text-green-500'>เสร็จแล้ว</span>";
                                    break;
                                default:
                                    echo "ไม่ทราบสถานะ";
                            }
                            ?>
                        </td>
                        <td class="p-3">
                            <a href="edit_notification.php?id=<?php echo $notification['id']; ?>" class="bg-yellow-500 text-white p-2 rounded">แก้ไข</a>
                            <a href="delete_notification.php?id=<?php echo $notification['id']; ?>" class="bg-red-500 text-white p-2 rounded">ลบ</a>
                            <a href="view_notification.php?id=<?php echo $notification['id']; ?>" class="bg-blue-500 text-white p-2 rounded">ดูรายละเอียด</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
