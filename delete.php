<?php
session_start();
include 'db.php';

// ตรวจสอบว่ามีการส่ง ID เข้ามา
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // ลบข้อมูลการแจ้งซ่อมจากฐานข้อมูล
    $sql = "DELETE FROM repair_request WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    try {
        $stmt->execute();
        $_SESSION['success'] = "ลบข้อมูลการแจ้งซ่อมสำเร็จ!";
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ตรวจสอบและย้อนกลับไปยังหน้าก่อนหน้า
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    // หากไม่มี HTTP_REFERER ให้กลับไปหน้าแรก
    header("Location: index.php");
}
exit;
?>
