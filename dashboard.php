<?php
include 'db.php';
session_start();

$user_role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? "Guest";

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ดึงข้อมูลจากฐานข้อมูล
$totalRequests = $conn->query("SELECT COUNT(*) AS total FROM repair_request")->fetch(PDO::FETCH_ASSOC)['total'];
$pendingRequests = $conn->query("SELECT COUNT(*) AS total FROM repair_request WHERE status = 'Pending'")->fetch(PDO::FETCH_ASSOC)['total'];
$inProgressRequests = $conn->query("SELECT COUNT(*) AS total FROM repair_request WHERE status = 'In Progress'")->fetch(PDO::FETCH_ASSOC)['total'];
$completedRequests = $conn->query("SELECT COUNT(*) AS total FROM repair_request WHERE status = 'Completed'")->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-container {
            margin-top: 5rem;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .menu {
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container dashboard-container">
    <h2 class="text-center mb-4">Dashboard การแจ้งซ่อม</h2>
    <div class="row text-center">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <i class="bi bi-clipboard-check display-4"></i>
                    <h5 class="card-title mt-2">การแจ้งซ่อมทั้งหมด</h5>
                    <p class="card-text"><?= $totalRequests; ?> รายการ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <i class="bi bi-clock-history display-4"></i>
                    <h5 class="card-title mt-2">ยังไม่ดำเนินการ</h5>
                    <p class="card-text"><?= $pendingRequests; ?> รายการ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <i class="bi bi-tools display-4"></i>
                    <h5 class="card-title mt-2">กำลังดำเนินการ</h5>
                    <p class="card-text"><?= $inProgressRequests; ?> รายการ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <i class="bi bi-check-circle display-4"></i>
                    <h5 class="card-title mt-2">เสร็จสมบูรณ์</h5>
                    <p class="card-text"><?= $completedRequests; ?> รายการ</p>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
