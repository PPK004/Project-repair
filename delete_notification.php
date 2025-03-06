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

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $sql = "DELETE FROM notifications WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: main.php");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "<script>
            if (confirm('คุณต้องการลบการแจ้งเตือนนี้จริงหรือไม่?')) {
                window.location.href = 'delete_notification.php?id=$id&confirm=yes';
            } else {
                window.location.href = 'main.php';
            }
          </script>";
}

$conn->close();
?>
