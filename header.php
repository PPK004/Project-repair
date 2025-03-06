<header>
    <h1>ระบบแจ้งซ่อม</h1>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="report.php">แจ้งซ่อม</a>
            <a href="logout.php">ออกจากระบบ</a>
        <?php else: ?>
            <a href="login.php">เข้าสู่ระบบ</a>
            <a href="register.php">ลงทะเบียน</a>
        <?php endif; ?>
    </nav>
</header>
