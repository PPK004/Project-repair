<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

// เริ่ม session
session_start();

$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? "Guest";

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ฟังก์ชันการค้นหาและกรองข้อมูล
$search_query = '';
$filter_query = '';
$repair_type = ''; // กำหนดค่าเริ่มต้นให้กับ repair_type
$search_value = '';
$start_date = '';
$end_date = '';

// ตรวจสอบกรณีกรอกคำค้นหาหรือกรองตามวันที่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['search'])) {
        $search_value = $_POST['search'];
        $search_query = "AND (repair_name LIKE :search_value OR location LIKE :search_value)";
    }

    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $filter_query = "AND report_date BETWEEN :start_date AND :end_date";
    }
    if (!empty($_POST['repair_type'])) {
        $repair_type = $_POST['repair_type'];
        $search_query .= " AND repair_type = :repair_type";
    }
        
}

// ดึงข้อมูลการแจ้งซ่อม
$sql = "SELECT id, repair_code, repair_name, description, location, status, image, report_date, repair_type 
        FROM repair_request 
        WHERE 1=1 $search_query $filter_query 
        ORDER BY report_date DESC";
$stmt = $conn->prepare($sql);

// Binding parameters
if (!empty($search_value)) {
    $stmt->bindValue(':search_value', '%' . $search_value . '%', PDO::PARAM_STR);
}
if (!empty($start_date) && !empty($end_date)) {
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
}
if (!empty($repair_type)) {
    $stmt->bindValue(':repair_type', $repair_type, PDO::PARAM_STR);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการการแจ้งซ่อม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
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

<!-- ตารางแสดงรายการซ่อม -->
<div class="container">
    <div class="table-container">
        <h2 class="text-center mb-4">รายการการแจ้งซ่อม</h2>

        <!-- ฟอร์มค้นหาและกรองตามเวลา -->
        <form method="POST" class="mb-4">
            <div class="row">
            <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อหรือสถานที่" value="<?php echo htmlspecialchars($search_value); ?>">
        </div>
        <div class="col-md-3">
            <select name="repair_type" class="form-control">
                <option value="">เลือกประเภทการซ่อม</option>
                <option value="mouse" <?php echo $repair_type == 'mouse' ? 'selected' : ''; ?>>เมาส์</option>
                <option value="keyboard" <?php echo $repair_type == 'keyboard' ? 'selected' : ''; ?>>คีย์บอร์ด</option>
                <option value="monitor" <?php echo $repair_type == 'monitor' ? 'selected' : ''; ?>>จอคอมพิวเตอร์</option>
                <option value="computer" <?php echo $repair_type == 'computer' ? 'selected' : ''; ?>>เครื่องคอมพิวเตอร์</option>
                <option value="other" <?php echo $repair_type == 'other' ? 'selected' : ''; ?>>อื่นๆ</option>
            </select>
        </div>
                
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-2" style="margin-top: 20px;">
    <button type="submit" class="btn btn-primary">ค้นหา</button>
</div>

            </div>
        </form>

        <!-- ตารางแสดงข้อมูล -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ลำดับที่</th>
                    <th>เลข ID</th>
                    <th>ชื่อการซ่อม</th>
                    <th>ประเภทอุปกรณ์</th>
                    <th>รายละเอียด</th>
                    <th>สถานที่</th>
                    <th>สถานะ</th>
                    <th>รูปภาพ</th>
                    <th>รหัสซ่อม</th>
                    <th>วันที่แจ้ง</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($result)) {
                    $i = 1;
                    foreach ($result as $row) {
                        echo "<tr>";
                        echo "<td>" . $i++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['repair_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['repair_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>";
                        if ($row['image']) {
                            echo "<img src='data:image/jpeg;base64," . base64_encode($row['image']) . "' width='100'>";
                        } else {
                            echo "ไม่มีรูปภาพ";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row['repair_code']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['report_date']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>ไม่มีข้อมูลการแจ้งซ่อม</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
