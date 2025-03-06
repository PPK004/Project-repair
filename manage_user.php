<?php
session_start();
include 'db.php';

$user_role = $_SESSION['role'] ?? null;

// ตรวจสอบว่าเป็น Admin เท่านั้น
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "คุณไม่มีสิทธิ์เข้าถึงหน้านี้!";
    header("Location: index.php");
    exit();
}

// ดึงข้อมูลผู้ใช้ทั้งหมดจากฐานข้อมูล
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $conn->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ฟังก์ชันอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'change_password') {
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $updateSql = "UPDATE users SET password = :password WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([':password' => $newPassword, ':id' => $userId]);
        $_SESSION['success'] = "เปลี่ยนรหัสผ่านสำเร็จ!";
    } elseif ($action === 'change_role') {
        $newRole = $_POST['new_role'];
        $updateSql = "UPDATE users SET role = :role WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([':role' => $newRole, ':id' => $userId]);
        $_SESSION['success'] = "เปลี่ยนบทบาทสำเร็จ!";
    }
    header("Location: manage_user.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้</title>
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
        <h2>จัดการผู้ใช้</h2>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>บทบาท</th>
                    <th>วันที่สมัคร</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $user): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <!-- ปุ่มเปลี่ยนรหัสผ่าน -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="change_password">
                                <input type="password" name="new_password" placeholder="รหัสผ่านใหม่" required>
                                <button type="submit" class="btn btn-warning btn-sm">เปลี่ยนรหัสผ่าน</button>
                            </form>

                            <!-- ปุ่มเปลี่ยนบทบาท -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="change_role">
                                <select name="new_role" required>
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="tech" <?php echo $user['role'] === 'tech' ? 'selected' : ''; ?>>Tech</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">เปลี่ยนบทบาท</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
