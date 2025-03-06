<?php
date_default_timezone_set('Asia/Bangkok');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = '+07:00'"); 

$id = $_GET['id'];

$sql = "SELECT id, title, description, scheduled_datetime, location, status FROM notifications WHERE id = $id";
$result = $conn->query($sql);
$notification = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดการแจ้งเตือน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="container mx-auto p-8">
        <div class="mb-6">
            <a href="main.php" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-green-500 transition-colors">กลับ</a>
        </div>

        <?php if ($notification): ?>
            <div class="bg-white p-8 rounded-lg shadow-xl">
                <h2 class="text-3xl font-semibold text-gray-800 mb-6">รายละเอียดการแจ้งเตือน</h2>

                <div class="mb-4">
                    <h3 class="text-xl font-medium text-gray-700">หัวข้อ:</h3>
                    <p class="text-gray-600"><?php echo $notification['title']; ?></p>
                </div>

                <div class="mb-4">
                    <h3 class="text-xl font-medium text-gray-700">รายละเอียด:</h3>
                    <p class="text-gray-600"><?php echo nl2br($notification['description']); ?></p>
                </div>

                <div class="mb-4">
                    <h3 class="text-xl font-medium text-gray-700">สถานที่:</h3>
                    <p class="text-gray-600"><?php echo $notification['location']; ?></p>
                </div>

                <div class="mb-4">
                    <h3 class="text-xl font-medium text-gray-700">วันที่/เวลา:</h3>
                    <p class="text-gray-600"><?php echo date("d/m/Y H:i", strtotime($notification['scheduled_datetime'])); ?></p>
                </div>

                <div class="mb-4">
                    <h3 class="text-xl font-medium text-gray-700">สถานะ:</h3>
                    <p class="text-gray-600">
                        <?php
                        switch ($notification['status']) {
                            case 'pending':
                                echo "ยังไม่เสร็จ";
                                break;
                            case 'in_progress':
                                echo "กำลังดำเนินการ";
                                break;
                            case 'completed':
                                echo "เสร็จแล้ว";
                                break;
                            default:
                                echo "ไม่ทราบสถานะ";
                        }
                        ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <p class="text-center text-lg text-red-500">ไม่พบข้อมูลการแจ้งเตือนนี้</p>
        <?php endif; ?>
    </div>

</body>
</html>
