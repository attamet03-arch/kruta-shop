<?php
require_once 'header.php';

$msg = '';
$msg_type = 'success';

// Handle Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promptpay_number = preg_replace('/[^0-9]/', '', $_POST['promptpay_number'] ?? '');
    $promptpay_name = trim($_POST['promptpay_name'] ?? '');
    $promptpay_type = $_POST['promptpay_type'] ?? 'mobile';

    if (empty($promptpay_number)) {
        $msg = "กรุณากรอกเบอร์พร้อมเพย์ หรือเลขประจำตัวประชาชน/ผู้เสียภาษี";
        $msg_type = "danger";
    } else {
        try {
            $save = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            
            $save->execute(['promptpay_number', $promptpay_number]);
            $save->execute(['promptpay_name', $promptpay_name]);
            $save->execute(['promptpay_type', $promptpay_type]);

            $msg = "บันทึกการตั้งค่าพร้อมเพย์เรียบร้อยแล้ว!";
            $msg_type = "success";
        } catch (Exception $e) {
            $msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
            $msg_type = "danger";
        }
    }
}

// Current values
$current_number = get_setting('promptpay_number', '0899999999');
$current_name = get_setting('promptpay_name', 'ครูต๊ะ เครื่องเขียนเพื่อการเรียนรู้');
$current_type = get_setting('promptpay_type', 'mobile');

// Test QR Code generation
$test_amount = 99.00;
$test_qr_url = PromptPayQR::getQrImageUrl($current_number, $test_amount, 280);
$test_payload = PromptPayQR::generatePayload($current_number, $test_amount);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold font-mali"><i class="bi bi-qr-code-scan text-danger me-2"></i> จัดการระบบชำระเงิน พร้อมเพย์ (PromptPay)</h1>
        <p class="text-muted mb-0">กำหนดเบอร์โทรศัพท์ หรือ เลขประจำตัวผู้เสียภาษี สำหรับรับเงินหน้าร้าน</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show rounded-4 fw-bold shadow-sm" role="alert">
        <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Form Settings -->
    <div class="col-lg-6">
        <div class="card admin-card p-4 bg-white">
            <h5 class="fw-bold font-mali mb-3 text-dark">⚙️ ตั้งค่าบัญชีรับเงิน PromptPay</h5>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">ประเภทบัญชีพร้อมเพย์</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="promptpay_type" id="type_mobile" value="mobile" <?= $current_type === 'mobile' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="type_mobile">
                                📱 เบอร์โทรศัพท์มือถือ (10 หลัก)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="promptpay_type" id="type_natid" value="id_card" <?= $current_type === 'id_card' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="type_natid">
                                🪪 บัตรประชาชน / นิติบุคคล (13 หลัก)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">หมายเลขพร้อมเพย์ (PromptPay No.) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-telephone-fill text-muted"></i></span>
                        <input type="text" name="promptpay_number" class="form-control rounded-end-pill px-3 fs-5 fw-bold text-primary" required
                               placeholder="เช่น 0891234567 หรือ 1234567890123" value="<?= htmlspecialchars($current_number) ?>">
                    </div>
                    <small class="text-muted">รองรับเบอร์มือถือ หรือ เลขประจำตัว 13 หลัก ไม่ต้องใส่ขีดคั่น</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">ชื่อบัญชี / ชื่อร้านค้าที่แสดง <span class="text-danger">*</span></label>
                    <input type="text" name="promptpay_name" class="form-control rounded-pill px-3" required
                           placeholder="เช่น นายต๊ะ เครื่องเขียน หรือ ร้านครูต๊ะช็อป" value="<?= htmlspecialchars($current_name) ?>">
                    <small class="text-muted">ชื่อนี้จะแสดงให้ลูกค้าตรวจสอบความถูกต้องขณะสแกนจ่าย</small>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold shadow-sm">
                    <i class="bi bi-save2-fill me-1"></i> บันทึกการตั้งค่าพร้อมเพย์
                </button>
            </form>
        </div>
    </div>

    <!-- Live QR Preview -->
    <div class="col-lg-6">
        <div class="card admin-card p-4 bg-white text-center">
            <h5 class="fw-bold font-mali mb-2 text-dark">🔍 พรีวิว QR Code ที่ลูกค้าจะเห็น</h5>
            <p class="text-muted small">ระบบสร้าง QR Code ตามมาตรฐาน EMVCo สแกนได้ทุกแอปธนาคารของไทย</p>

            <div class="p-3 bg-light rounded-4 d-inline-block mx-auto mb-3 border shadow-sm">
                <img src="<?= $test_qr_url ?>" alt="PromptPay Preview" class="img-fluid rounded-3" style="max-width: 230px;">
            </div>

            <div class="text-center">
                <h6 class="fw-bold text-dark font-mali mb-1"><?= htmlspecialchars($current_name) ?></h6>
                <span class="badge bg-primary rounded-pill px-3 py-1 fs-6 mb-2">PromptPay: <?= htmlspecialchars($current_number) ?></span>
                <p class="text-muted small mb-0">ตัวอย่าง QR Code สำหรับยอด: <b><?= format_price($test_amount) ?></b></p>
            </div>

            <div class="mt-3 p-2 bg-light rounded-3 text-start small text-break">
                <span class="text-muted fw-bold">EMVCo Payload:</span><br>
                <code><?= htmlspecialchars($test_payload) ?></code>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
