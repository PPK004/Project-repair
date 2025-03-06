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

function checkAndSendNotifications($conn) {
    $current_time = date('Y-m-d H:i:s'); 
    $sql = "SELECT id, title, description, location, scheduled_datetime FROM notifications WHERE status = 'pending'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            processNotification($row, $conn);
        }
        echo 'Notifications processed.';
    } else {
        echo 'No notifications to process.';
    }
}

function processNotification($notification, $conn) {
    $id = $notification['id'];
    $title = $notification['title'];
    $description = $notification['description'];
    $location = $notification['location'];
    $scheduled_datetime = $notification['scheduled_datetime'];

    $targetTimestamp = strtotime($scheduled_datetime);
    $currentTimestamp = time();

    if ($currentTimestamp >= $targetTimestamp) {
        $message = formatMessage($title, $description, $location, $scheduled_datetime);
        $response = sendLineNotify($message);

        if ($response) {
            updateNotificationStatus($id, $conn);
        }
    }
}

function formatMessage($title, $description, $location, $datetime) {
    return "📌 มีการแจ้งเตือน!\n" .
           "🗓 หัวข้อ: $title\n" .
           "💬 รายละเอียด: $description\n" .
           "📍 สถานที่: $location\n" .
           "⏰ เวลา: $datetime";
}

function sendLineNotify($message) {
    $token = 'OksFSrXYd4dCLgF7lPZlf2vmU2bA3Nu5KFr1dJejVGN';
    $url = 'https://notify-api.line.me/api/notify';

    $data = ['message' => $message];
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/x-www-form-urlencoded'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response) {
        return true;
    } else {
        error_log("LINE Notify Error: " . $error);
        return false;
    }
}

// ฟังก์ชันอัปเดตสถานะในฐานข้อมูล
function updateNotificationStatus($id, $conn) {
    $update_sql = "UPDATE notifications SET status = 'in_progress' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        error_log("Failed to update status for notification ID: $id");
    }
    $stmt->close();
}

// เรียกฟังก์ชันตรวจสอบและส่งการแจ้งเตือน
checkAndSendNotifications($conn);

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
