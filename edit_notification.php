<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id']; 
$sql = "SELECT * FROM notifications WHERE id = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $scheduled_datetime = $_POST['scheduled_datetime'];
    $status = $_POST['status'];

    $sql = "UPDATE notifications SET title='$title', description='$description', location='$location', scheduled_datetime='$scheduled_datetime', status='$status' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: main.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขการแจ้งเตือน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <div class="container mx-auto p-8">
        <div class="mb-4">
            <a href="main.php" class="bg-blue-500 text-white p-2 rounded-md">กลับ</a>
        </div>

        <h1 class="text-3xl font-semibold mb-6 text-center">แก้ไขการแจ้งเตือน</h1>

        <form action="edit_notification.php?id=<?php echo $id; ?>" method="post" class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-lg">
            <div class="mb-4">
                <label for="title" class="block text-lg font-medium text-gray-700">หัวข้อ</label>
                <input type="text" name="title" value="<?php echo $row['title']; ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-lg font-medium text-gray-700">คำอธิบาย</label>
                <textarea name="description" rows="4" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required><?php echo $row['description']; ?></textarea>
            </div>

            <div class="mb-4">
                <label for="location" class="block text-lg font-medium text-gray-700">สถานที่</label>
                <input type="text" name="location" value="<?php echo $row['location']; ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="scheduled_datetime" class="block text-lg font-medium text-gray-700">วันที่/เวลา</label>
                <input type="datetime-local" name="scheduled_datetime" value="<?php echo date('Y-m-d\TH:i', strtotime($row['scheduled_datetime'])); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <div class="mb-4">
                <label for="status" class="block text-lg font-medium text-gray-700">สถานะ</label>
                <select name="status" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>ยังไม่เสร็จ</option>
                    <option value="in_progress" <?php echo ($row['status'] == 'in_progress') ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                    <option value="completed" <?php echo ($row['status'] == 'completed') ? 'selected' : ''; ?>>เสร็จแล้ว</option>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-blue-500 text-white p-2 rounded-md">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>

</body>
</html>
