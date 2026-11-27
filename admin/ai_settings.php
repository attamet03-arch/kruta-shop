<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit;
}

$msg = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gemini_api_key = trim($_POST['gemini_api_key'] ?? '');
    $slipok_api_key = trim($_POST['slipok_api_key'] ?? '');
    $ai_auto_approve = $_POST['ai_auto_approve'] ?? '1';

    try {
        $save = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        $save->execute(['gemini_api_key', $gemini_api_key]);
        $save->execute(['slipok_api_key', $slipok_api_key]);
        $save->execute(['ai_auto_approve', $ai_auto_approve]);

        $msg = "บันทึกการตั้งค่า AI ตรวจสอบสลิปเรียบร้อยแล้ว!";
        $msg_type = "success";
    } catch (Exception $e) {
        $msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $msg_type = "danger";
    }
}

$current_gemini = get_setting('gemini_api_key', '');
$current_slipok = get_setting('slipok_api_key', '');
$current_auto = get_setting('ai_auto_approve', '1');

require_once 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold font-mali"><i class="bi bi-robot text-primary me-2"></i> ระบบ AI ตรวจสอบสลิปอัตโนมัติ 100%</h1>
        <p class="text-muted mb-0">ระบบตรวจสลิปทันทีที่ลูกค้าแนบ โดยที่แอดมินหรือเจ้าของร้านไม่ต้องคอยตรวจเอง</p>
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
            <h5 class="fw-bold font-mali mb-3 text-dark">🤖 การทำงานของ AI อัตโนมัติ</h5>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">สถานะระบบตรวจสลิปอัตโนมัติ</label>
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" name="ai_auto_approve" value="1" id="autoSwitch" <?= $current_auto == '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fs-6 fw-bold" for="autoSwitch">
                            เปิดใช้งาน AI ตรวจและอนุมัติออเดอร์ทันทีเมื่อแนบสลิปถูกต้อง (ไม่ต้องรอแอดมิน)
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Google Gemini AI API Key (ฟรี)</label>
                    <input type="text" name="gemini_api_key" class="form-control rounded-pill px-3" 
                           placeholder="AIzaSy..." value="<?= htmlspecialchars($current_gemini) ?>">
                    <small class="text-muted">ใช้โมเดล Gemini 1.5 Flash Vision อ่านตัวเลข ยอดเงิน และชื่อบัญชีในสลิป (ฟรี 1,500 สลิป/วัน จาก <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-bold">Google AI Studio</a>)</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">SlipOK API Key (ตัวเลือกเสริมสำหรับเช็ค QR ธนาคารตรง)</label>
                    <input type="text" name="slipok_api_key" class="form-control rounded-pill px-3" 
                           placeholder="ใส่ SlipOK Branch API Key (ถ้ามี)" value="<?= htmlspecialchars($current_slipok) ?>">
                    <small class="text-muted">บริการยิงตรงเข้าฐานข้อมูลกลางธนาคารแห่งประเทศไทย (สามารถเว้นว่างได้ ระบบมี AI Vision ทำงานแทนให้อยู่แล้ว)</small>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-save2-fill me-1"></i> บันทึกการตั้งค่า AI
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card admin-card p-4 bg-light border">
            <h5 class="fw-bold font-mali text-primary mb-3"><i class="bi bi-shield-check text-success me-1"></i> AI ตรวจสอบอะไรบ้าง?</h5>
            <ul class="list-unstyled mb-0" style="line-height: 2;">
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> <b>ตรวจสอบยอดเงิน:</b> ยอดเงินที่โอนต้องตรงกับยอดรวมสุทธิของออเดอร์เป๊ะ ๆ</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> <b>ตรวจสอบบัญชีผู้รับ:</b> ต้องเป็นเบอร์พร้อมเพย์ หรือเลขบัญชีของร้านครูต๊ะเท่านั้น</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> <b>ป้องกันสลิปปลอม / สลิปซ้ำ:</b> ตรวจสอบความถูกต้องของรูปภาพและวันที่โอนเงิน</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i> <b>ปรับสถานะเป็น Paid ทันที:</b> ลูกค้าสั่งซื้อเสร็จ ระบบขึ้นว่าชำระเงินแล้วและพร้อมจัดส่งทันที</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
