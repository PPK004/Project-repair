<?php
include 'db.php';
session_start();

$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? "Guest";

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // อัปเดตข้อมูลผู้ใช้
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบรหัสผ่านใหม่
    if (!empty($new_password) && $new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET first_name = ?, last_name = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$first_name, $last_name, $hashed_password, $user_id]);
    } else {
        // ถ้าไม่มีการเปลี่ยนรหัสผ่าน, ก็แค่เปลี่ยนแค่ชื่อจริงและนามสกุล
        $sql = "UPDATE users SET first_name = ?, last_name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$first_name, $last_name, $user_id]);
    }

    // แสดงข้อความสำเร็จ
    $message = "ข้อมูลของคุณได้รับการอัปเดตแล้ว";
}

// ดึงข้อมูลการแจ้งซ่อมที่ผู้ใช้เคยแจ้ง
$sql = "SELECT * FROM repair_request WHERE user_id = ? ORDER BY report_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <div class="container mt-4">
        <h2>โปรไฟล์ผู้ใช้</h2>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="first_name" class="form-label">ชื่อจริง</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">นามสกุล</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)</label>
                <input type="password" class="form-control" id="new_password" name="new_password">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit" class="btn btn-primary">อัปเดตข้อมูล</button>
        </form>

        <h3 class="mt-5">การแจ้งซ่อมที่คุณเคยแจ้ง</h3>
        <?php if (count($reports) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ชื่อการซ่อม</th>
                        <th>สถานที่</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $index => $report): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($report['repair_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['location']); ?></td>
                            <td><?php echo htmlspecialchars($report['status']); ?></td>
                            <td><a href="delete.php?id=<?php echo $report['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้?');">ลบ</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>คุณยังไม่ได้แจ้งซ่อมใดๆ</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
