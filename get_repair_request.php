<?php
include 'db.php';

// ดึงข้อมูลการแจ้งซ่อม
$sql = "SELECT * FROM repair_request ORDER BY report_date DESC";
$stmt = $conn->query($sql);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูลกลับเป็น JSON
echo json_encode($reports);
?>
