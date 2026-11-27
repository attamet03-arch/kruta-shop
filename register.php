<?php
$page_title = "สมัครสมาชิกใหม่ - Kruta Shop";
require_once 'config.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($fullname) || empty($email) || empty($password)) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    } else {
        try {
            // Check existing
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $error = "อีเมลนี้มีผู้ใช้งานแล้ว กรุณาเข้าสู่ระบบ";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $avatar = "https://api.dicebear.com/7.x/bottts/svg?seed=" . urlencode($fullname);
                $ins = $conn->prepare("INSERT INTO users (fullname, email, password, phone, role, avatar) VALUES (?, ?, ?, ?, 'customer', ?)");
                $ins->execute([$fullname, $email, $hash, $phone, $avatar]);

                $success = "สมัครสมาชิกสำเร็จเรียบร้อย! กำลังพาไปหน้าเข้าสู่ระบบ...";
                header("refresh:2;url=login.php");
            }
        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}

require_once 'header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5" data-aos="zoom-in">
            <div class="card border-0 rounded-5 shadow-lg p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="jump-infinite display-4">🎁🎈</span>
                    <h3 class="fw-bold font-mali mt-2" style="color: #6C5CE7;">สมัครสมาชิกใหม่</h3>
                    <p class="text-muted small">รับสิทธิพิเศษและส่วนลดมากมายเฉพาะสมาชิก ครูต๊ะช็อป</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-4 text-center fw-bold small py-2">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success rounded-4 text-center fw-bold small py-2">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ชื่อ - นามสกุล</label>
                        <input type="text" name="fullname" class="form-control rounded-pill bg-light border-light" placeholder="เช่น น้องฟ้าใส มีความสุข" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">อีเมล (Email)</label>
                        <input type="email" name="email" class="form-control rounded-pill bg-light border-light" placeholder="yourname@gmail.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                        <input type="tel" name="phone" class="form-control rounded-pill bg-light border-light" placeholder="08xxxxxxxx">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">ตั้งรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)</label>
                        <input type="password" name="password" class="form-control rounded-pill bg-light border-light" required>
                    </div>

                    <button type="submit" class="btn btn-kruta-purple w-100 py-2 rounded-pill mt-3 fw-bold">
                        ยืนยันการสมัครสมาชิก 🎉
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <p class="small text-muted mb-1">มีบัญชีอยู่แล้ว?</p>
                    <a href="login.php" class="fw-bold text-decoration-none" style="color: #FF6584;">
                        เข้าสู่ระบบได้ที่นี่ 👉
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
