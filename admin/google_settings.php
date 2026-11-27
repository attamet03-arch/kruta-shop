<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit;
}

$msg = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $google_client_id = trim($_POST['google_client_id'] ?? '');

    try {
        $save = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('google_client_id', ?) 
                                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $save->execute([$google_client_id]);

        $msg = "บันทึก Google Client ID เรียบร้อยแล้ว!";
        $msg_type = "success";
    } catch (Exception $e) {
        $msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $msg_type = "danger";
    }
}

$current_client_id = get_setting('google_client_id', '');

require_once 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold font-mali"><i class="bi bi-google text-danger me-2"></i> ตั้งค่า Google Sign-In (OAuth 2.0 จริง)</h1>
        <p class="text-muted mb-0">ใส่ Google Client ID เพื่อให้ลูกค้ากดล็อกอินด้วยบัญชี Google จริง ๆ ได้ทันที</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show rounded-4 fw-bold shadow-sm" role="alert">
        <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card admin-card p-4 bg-white">
            <h5 class="fw-bold font-mali mb-3 text-dark">⚙️ Google OAuth Client Credentials</h5>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Google Client ID <span class="text-danger">*</span></label>
                    <textarea name="google_client_id" class="form-control rounded-4 p-3 font-monospace small" rows="3" 
                              placeholder="เช่น 1234567890-abcdefghijklmnopqrstuvwxyz.apps.googleusercontent.com"><?= htmlspecialchars($current_client_id) ?></textarea>
                    <small class="text-muted d-block mt-1">คัดลอก Client ID จาก <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="fw-bold">Google Cloud Console</a></small>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-save2-fill me-1"></i> บันทึก Google Client ID
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card admin-card p-4 bg-light border">
            <h5 class="fw-bold font-mali text-primary mb-3"><i class="bi bi-lightbulb-fill text-warning me-1"></i> วิธีสร้าง Google Client ID (ฟรี 100%):</h5>
            <ol class="small text-muted ps-3 mb-0" style="line-height: 1.8;">
                <li>เข้าสู่ <a href="https://console.cloud.google.com/" target="_blank" class="fw-bold text-dark">Google Cloud Console</a> ด้วย Gmail</li>
                <li>สร้าง Project ใหม่ (เช่น <code>Kruta-Shop</code>)</li>
                <li>ไปที่เมนู <b>APIs & Services</b> > <b>Credentials</b></li>
                <li>กด <b>Create Credentials</b> > เลือก <b>OAuth client ID</b></li>
                <li>Application type เลือกเป็น <b>Web application</b></li>
                <li>ที่หัวข้อ <b>Authorized JavaScript origins</b> ให้เพิ่ม:<br>
                    <code class="bg-white p-1 rounded border">http://localhost</code>
                </li>
                <li>กด Create แล้วคัดลอก <b>Client ID</b> มาวางในช่องด้านซ้ายได้เลยครับ!</li>
            </ol>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
