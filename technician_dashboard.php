<?php
session_start();
include 'db.php';

if (!isset($_SESSION['technician_id'])) {
    header("Location: login.php");
    exit();
}

$technician_id = $_SESSION['technician_id'];
$sql = "SELECT r.repair_id, r.repair_name, r.location, r.status 
        FROM repair_requests r
        JOIN assignments a ON r.id = a.repair_id
        WHERE a.technician_id = :technician_id
        ORDER BY r.report_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':technician_id', $technician_id);
$stmt->execute();
$repairs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Welcome, Technician</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>งานซ่อม</th>
                    <th>สถานที่</th>
                    <th>สถานะ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($repairs as $index => $repair): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($repair['repair_name']); ?></td>
                        <td><?php echo htmlspecialchars($repair['location']); ?></td>
                        <td><?php echo htmlspecialchars($repair['status']); ?></td>
                        <td>
                            <a href="update_repair.php?repair_id=<?php echo $repair['repair_id']; ?>" class="btn btn-primary btn-sm">ดูรายละเอียด</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
