<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT * FROM repair_request WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $updateSql = "UPDATE repair_request SET status = :status WHERE id = :id";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->execute(['status' => $status, 'id' => $id]);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลการแจ้งซ่อม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">แก้ไขสถานะ</h2>
        <form method="post">
            <div class="mb-3">
                <label for="status" class="form-label">สถานะ</label>
                <select class="form-select" id="status" name="status">
                    <option value="กำลังตรวจสอบ" <?php echo $report['status'] === 'กำลังตรวจสอบ' ? 'selected' : ''; ?>>กำลังตรวจสอบ</option>
                    <option value="รอดำเนินการ" <?php echo $report['status'] === 'รอดำเนินการ' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                    <option value="เสร็จสิ้น" <?php echo $report['status'] === 'เสร็จสิ้น' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">บันทึก</button>
            <a href="index.php" class="btn btn-secondary">ย้อนกลับ</a>
        </form>
    </div>
</body>
</html>
