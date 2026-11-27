<?php
// install.php - 1-Click Database Setup & Auto-seed
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $pdo->exec($sql);
    
    // Hash passwords properly
    $password_hash = password_hash('123456', PASSWORD_DEFAULT);
    $pdo->exec("USE `kruta_shop`");
    $updateStmt = $pdo->prepare("UPDATE `users` SET `password` = ? WHERE `id` IN (1, 2)");
    $updateStmt->execute([$password_hash]);

    $message = "ติดตั้งฐานข้อมูลและเพิ่มข้อมูลสินค้าตัวอย่างเรียบร้อยแล้ว!";
    $status = "success";
} catch (Exception $e) {
    $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
    $status = "danger";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ติดตั้งฐานข้อมูล - Kruta Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700;800&family=Mali:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-custom { border-radius: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: none; }
    </style>
</head>
<body>
<div class="container" style="max-width: 500px;">
    <div class="card card-custom p-4 text-center bg-white">
        <h1 class="display-3">🎒✏️</h1>
        <h2 class="fw-bold mb-3" style="color: #FF5E7E;">Kruta Shop Database Setup</h2>
        <div class="alert alert-<?= $status ?> fw-bold">
            <?= $message ?>
        </div>
        <p class="text-muted">
            <b>ข้อมูลเข้าสู่ระบบเริ่มต้น:</b><br>
            👑 แอดมิน: <code>admin@kruta.com</code> / รหัสผ่าน: <code>123456</code><br>
            🎒 สมาชิก: <code>student@kruta.com</code> / รหัสผ่าน: <code>123456</code>
        </p>
        <a href="index.php" class="btn btn-primary btn-lg rounded-pill fw-bold shadow mt-2" style="background: linear-gradient(45deg, #FF6B6B, #FF8ED4); border:none;">
            🚀 เข้าสู่หน้าร้าน Kruta Shop ทันที
        </a>
    </div>
</div>
</body>
</html>
