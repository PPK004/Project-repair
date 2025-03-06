<?php
include 'db.php';
session_start();
date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบการเข้าสู่ระบบและบทบาท
$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? "Guest";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

function sendLineNotify($message, $token) {
    $url = "https://notify-api.line.me/api/notify";
    $data = [
        'message' => $message
    ];
    $headers = [
        "Authorization: Bearer " . $token,
        "Content-Type: application/x-www-form-urlencoded"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $repair_name = $_POST['repair_name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $report_date = date("Y-m-d H:i:s");
    $repair_code = strtoupper(uniqid('RC')); // สร้างรหัสแจ้งซ่อม
    $user_id = $_SESSION['user_id'];
    $image_data = null;
    $file_type = null;

    // ฟังก์ชันลดขนาดรูปภาพ
    function resizeImage($file, $maxWidth, $maxHeight) {
        list($width, $height, $type) = getimagesize($file);
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = $width * $ratio;
        $newHeight = $height * $ratio;

        $src = imagecreatefromstring(file_get_contents($file));
        $dst = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 85); // ลดคุณภาพเป็น 85%
        $data = ob_get_clean();

        imagedestroy($dst);
        return $data;
    }

    // จัดการการอัปโหลดไฟล์รูปภาพ
if (isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_size = $_FILES['image']['size'];
    $file_type = mime_content_type($file_tmp);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    // ตรวจสอบว่าเป็นไฟล์รูปภาพและขนาดไฟล์ไม่เกิน 10MB
    if (in_array($file_type, $allowed_types) && $file_size <= 10 * 1024 * 1024) {
        // ลดขนาดรูปภาพ
        $image_data = resizeImage($file_tmp, 1024, 1024); // ลดขนาดภาพให้ไม่เกิน 1024x1024 พิกเซล
    } else {
        $error = "ไฟล์ต้องเป็นรูปภาพประเภท jpg, png, gif และขนาดไม่เกิน 10MB.";
    }
} else {
    // ถ้าไม่มีการอัปโหลดภาพ ให้ใช้ภาพเริ่มต้น hr.png
    $default_image_path = 'img/hx.png';
    $file_type = mime_content_type($default_image_path);
    $image_data = file_get_contents($default_image_path);
}


    if (!isset($error)) {
        try {
            // บันทึกข้อมูลลงในฐานข้อมูล
            $sql = "INSERT INTO repair_request (repair_code, repair_name, description, location, repair_type, report_date, user_id, image, image_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$repair_code, $repair_name, $description, $location, $_POST['repair_type'], $report_date, $user_id, $image_data, $file_type]);

            $line_token = "OksFSrXYd4dCLgF7lPZlf2vmU2bA3Nu5KFr1dJejVGN"; 
            $message = "มีการแจ้งซ่อมใหม่:\n- ชื่อการซ่อม: $repair_name\n- รายละเอียด: $description\n- สถานที่: $location\n- วันที่แจ้ง: $report_date";
            sendLineNotify($message, $line_token);

            $success = "การแจ้งซ่อมของคุณถูกบันทึกเรียบร้อยแล้ว";
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งซ่อม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 5rem;
        }
        .navbar {
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<!-- Navbar -->
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

<!-- Form to submit repair -->
<div class="container d-flex justify-content-center">
    <div class="form-container">
        <h2 class="text-center mb-4">แจ้งซ่อม</h2>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="repair_name" class="form-label">ชื่อการซ่อม</label>
                <input type="text" name="repair_name" id="repair_name" class="form-control" required>
            </div>
            <div class="mb-3">
    <label for="repair_type" class="form-label">ประเภทอุปกรณ์ที่ต้องการซ่อม</label>
    <select name="repair_type" id="repair_type" class="form-control" required>
        <option value="mouse">เมาส์</option>
        <option value="keyboard">คีย์บอร์ด</option>
        <option value="monitor">จอคอมพิวเตอร์</option>
        <option value="computer">เครื่องคอมพิวเตอร์</option>
        <option value="other">อื่นๆ</option>
    </select>
</div>

            <div class="mb-3">
                <label for="description" class="form-label">รายละเอียด</label>
                <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">สถานที่</label>
                <input type="text" name="location" id="location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">แนบรูปภาพ (ถ้ามี)</label>
                <input type="file" name="image" id="image" class="form-control">
            </div>
            <div class="d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">ย้อนกลับ</a>
                <button type="submit" class="btn btn-primary">ส่งการแจ้งซ่อม</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
