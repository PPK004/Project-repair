<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repair_id = $_POST['repair_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    $sql = "UPDATE repair_requests SET status = :status, notes = :notes, updated_at = NOW() WHERE id = :repair_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':repair_id', $repair_id);

    if ($stmt->execute()) {
        echo "<script>alert('อัปเดตสถานะสำเร็จ!'); window.location.href='technician_dashboard.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด!');</script>";
    }
}

// ดึงข้อมูลงานซ่อม
$repair_id = $_GET['repair_id'] ?? null;
$sql = "SELECT * FROM repair_requests WHERE id = :repair_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':repair_id', $repair_id);
$stmt->execute();
$repair = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Repair</title>
</head>
<body>
    <div class="container mt-4">
        <h2>อัปเดตงานซ่อม</h2>
        <form method="POST">
            <input type="hidden" name="repair_id" value="<?php echo $repair['id']; ?>">
            <label for="status">สถานะ:</label>
            <select name="status" id="status">
                <option value="pending">รอดำเนินการ</option>
                <option value="in_progress">กำลังซ่อม</option>
                <option value="completed">เสร็จสิ้น</option>
            </select>
            <label for="notes">หมายเหตุ:</label>
            <textarea name="notes" id="notes"></textarea>
            <button type="submit">บันทึก</button>
        </form>
    </div>
</body>
</html>
