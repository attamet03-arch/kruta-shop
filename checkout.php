<?php
$page_title = "ชำระเงินด้วย QR Code PromptPay";
require_once 'config.php';

// Must have items in cart
if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

// Fetch products in cart
$ids = array_keys($_SESSION['cart']);
$in = str_repeat('?,', count($ids) - 1) . '?';
$stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($in)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

$total_price = 0;
$cart_details = [];
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']];
    $subtotal = $p['price'] * $qty;
    $total_price += $subtotal;
    $cart_details[] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'price' => $p['price'],
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

$user = current_user();
$error = '';
$success_order_id = null;

// Fetch saved addresses for logged-in user (Max 5)
$saved_addresses = [];
if (is_logged_in()) {
    try {
        $addrStmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 5");
        $addrStmt->execute([$_SESSION['user_id']]);
        $saved_addresses = $addrStmt->fetchAll();
    } catch (Exception $e) {}
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    
    // Auto-composed Address from components
    $addr_detail = trim($_POST['addr_detail'] ?? '');
    $subdistrict = trim($_POST['subdistrict'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');

    // Combine into full address text
    if (!empty($subdistrict) || !empty($province)) {
        $shipping_address = "{$addr_detail} ต./แขวง {$subdistrict} อ./เขต {$district} จ.{$province} {$zipcode}";
    } else {
        $shipping_address = !empty($addr_detail) ? $addr_detail : trim($_POST['shipping_address'] ?? '');
    }

    if (empty($customer_name) || empty($customer_phone) || empty($shipping_address)) {
        $error = "กรุณากรอกข้อมูลชื่อ เบอร์โทร และที่อยู่จัดส่งให้ครบถ้วน";
    } else {
        // Handle Slip Upload & AI Automated Verification
        $slip_filename = null;
        $order_status = 'pending';
        $ai_note = '';
        $slip_hash_to_record = null;

        $promptpay_number = get_setting('promptpay_number', '0899999999');
        $promptpay_name = get_setting('promptpay_name', 'ครูต๊ะ เครื่องเขียนเพื่อการเรียนรู้');

        if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array(strtolower($ext), $allowed)) {
                if (!is_dir(__DIR__ . '/uploads')) {
                    mkdir(__DIR__ . '/uploads', 0777, true);
                }
                $slip_filename = 'slip_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $targetPath = __DIR__ . '/uploads/' . $slip_filename;
                move_uploaded_file($_FILES['payment_slip']['tmp_name'], $targetPath);

                // --- AI AUTOMATED SLIP VERIFICATION & DUPLICATE DETECTION ---
                $verification = SlipVerifier::verifySlip($targetPath, $total_price, $promptpay_number, $promptpay_name);

                if ($verification['is_duplicate']) {
                    // ❌ สลิปซ้ำ! บล็อกทันที ไม่ให้ผ่าน
                    @unlink($targetPath);
                    $error = $verification['message'];
                } elseif (!$verification['success']) {
                    // ❌ ยอดเงินไม่ตรง หรือสลิปไม่ถูกต้อง บล็อกทันที!
                    @unlink($targetPath);
                    $error = $verification['message'];
                } else {
                    // ✅ สลิปถูกต้องและยอดเงินตรงเป๊ะ 100%
                    $order_status = 'paid';
                    $ai_note = '✅ ยืนยันการโอนเงินสำเร็จ: ' . $verification['message'];
                    $slip_hash_to_record = $verification['slip_hash'];
                }
            }
        }

        if (empty($error)) {
            // Generate Order Code
            $order_code = 'KT-' . date('Ymd') . '-' . rand(1000, 9999);
            $user_id = is_logged_in() ? $_SESSION['user_id'] : null;

            try {
                $conn->beginTransaction();

                $insertOrder = $conn->prepare("INSERT INTO orders (order_code, user_id, customer_name, customer_phone, shipping_address, total_amount, payment_method, payment_slip, status) VALUES (?, ?, ?, ?, ?, ?, 'promptpay', ?, ?)");
                $insertOrder->execute([$order_code, $user_id, $customer_name, $customer_phone, $shipping_address, $total_price, $slip_filename, $order_status]);
                $order_id = $conn->lastInsertId();

                $insertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($cart_details as $item) {
                    $insertItem->execute([$order_id, $item['id'], $item['name'], $item['price'], $item['qty'], $item['subtotal']]);
                }

                // Record slip hash to prevent reuse
                if ($slip_hash_to_record && $order_status === 'paid') {
                    SlipVerifier::recordSlip($slip_hash_to_record, $order_id, $total_price);
                }

                // --- AUTO SAVE ADDRESS (Max 5 addresses per user) ---
            if (is_logged_in()) {
                $chkAddr = $conn->prepare("SELECT id FROM user_addresses WHERE user_id = ? AND recipient_name = ? AND phone = ? AND address = ?");
                $chkAddr->execute([$user_id, $customer_name, $customer_phone, $shipping_address]);
                $existingAddr = $chkAddr->fetch();

                if ($existingAddr) {
                    $upd = $conn->prepare("UPDATE user_addresses SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $upd->execute([$existingAddr['id']]);
                } else {
                    $cntStmt = $conn->prepare("SELECT id FROM user_addresses WHERE user_id = ? ORDER BY id ASC");
                    $cntStmt->execute([$user_id]);
                    $userAddrs = $cntStmt->fetchAll();

                    if (count($userAddrs) >= 5) {
                        $oldestId = $userAddrs[0]['id'];
                        $delStmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ?");
                        $delStmt->execute([$oldestId]);
                    }

                    $insAddr = $conn->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone, address, is_default) VALUES (?, ?, ?, ?, ?)");
                    $isDef = empty($userAddrs) ? 1 : 0;
                    $insAddr->execute([$user_id, $customer_name, $customer_phone, $shipping_address, $isDef]);
                }
            }

            $conn->commit();

            // Clear Cart
            $_SESSION['cart'] = [];
            header("Location: order_success.php?code=" . $order_code);
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            $error = "เกิดข้อผิดพลาดในการบันทึกคำสั่งซื้อ: " . $e->getMessage();
        }
        }
    }
}

// Get Dynamic PromptPay Settings
$promptpay_number = get_setting('promptpay_number', '0899999999');
$promptpay_name = get_setting('promptpay_name', 'ครูต๊ะ เครื่องเขียนเพื่อการเรียนรู้');

// Generate EMVCo standard dynamic PromptPay QR image URL
$qr_url = PromptPayQR::getQrImageUrl($promptpay_number, $total_price, 280);

require_once 'header.php';
?>

<!-- jQuery & JQL.In.TH Thailand Address AutoComplete Plugin -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.css">
<script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/JQL.min.js"></script>
<script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/typeahead.bundle.js"></script>
<script type="text/javascript" src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.js"></script>

<div class="container py-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold font-mali"><span class="jump-infinite">💳</span> ชำระเงินผ่าน QR Code พร้อมเพย์</h2>
        <p class="text-muted">สแกนจ่ายได้ด้วย App ทุกธนาคาร สะดวก รวดเร็ว ไร้ค่าธรรมเนียม</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger rounded-4 text-center fw-bold shadow-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="needs-validation">
        <div class="row g-4 justify-content-center">
            
            <!-- Left: QR Code Scan Box -->
            <div class="col-lg-5" data-aos="zoom-in">
                <div class="card border-0 rounded-4 shadow p-4 text-center bg-white">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c5/PromptPay-logo.png" alt="PromptPay" height="35">
                    </div>
                    <span class="badge bg-danger rounded-pill px-3 py-1 mx-auto mb-3 fs-6">ยอดชำระเงิน: <?= format_price($total_price) ?></span>

                    <!-- QR Code Image -->
                    <div class="p-3 bg-light rounded-4 d-inline-block mx-auto mb-3 border position-relative shadow-sm">
                        <img src="<?= $qr_url ?>" alt="PromptPay QR Code" class="img-fluid rounded-3" style="max-width: 250px;">
                        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-1">
                            <span class="badge bg-dark small">ครูต๊ะช็อป Kruta Shop</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold mb-1 font-mali text-primary"><?= htmlspecialchars($promptpay_name) ?></h6>
                        <p class="text-muted small mb-0">พร้อมเพย์: <b><?= htmlspecialchars($promptpay_number) ?></b></p>
                        <p class="text-muted small">ยอดรวมสุทธิ: <b class="text-danger fs-5"><?= format_price($total_price) ?></b></p>
                    </div>

                    <div class="alert alert-warning rounded-3 small text-start mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> <b>ขั้นตอนการชำระเงิน:</b>
                        <ol class="mb-0 ps-3 mt-1">
                            <li>เปิดแอปธนาคารใดก็ได้ แล้วเลือก "สแกน QR"</li>
                            <li>ตรวจสอบยอดเงินให้ตรงกับ <b><?= format_price($total_price) ?></b></li>
                            <li>กดยืนยันการโอนเงิน แล้วแนบสลิปด้านขวามือ</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Right: Shipping Info with Auto-complete & Slip Upload -->
            <div class="col-lg-7" data-aos="fade-left">
                <div class="card border-0 rounded-4 shadow p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 font-mali"><i class="bi bi-truck text-danger me-2"></i> ข้อมูลที่อยู่จัดส่งพัสดุ</h5>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                            <i class="bi bi-magic"></i> กรอกรหัสไปรษณีย์ ดึงข้อมูลอัตโนมัติ
                        </span>
                    </div>

                    <?php if (!empty($saved_addresses)): ?>
                        <!-- Quick Select from Saved Addresses -->
                        <div class="mb-3 p-3 bg-light rounded-4 border">
                            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-2">
                                <span><i class="bi bi-geo-alt-fill text-danger me-1"></i> เลือกจากที่อยู่ที่เคยบันทึกไว้ (<?= count($saved_addresses) ?>/5)</span>
                                <span class="badge bg-primary rounded-pill small">คลิกเพื่อใส่ข้อมูล</span>
                            </label>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($saved_addresses as $idx => $sAddr): ?>
                                    <div class="p-2 bg-white rounded-3 border d-flex justify-content-between align-items-center cursor-pointer shadow-sm hover-shadow saved-addr-card <?= $idx === 0 ? 'border-danger bg-light' : '' ?>"
                                         style="cursor: pointer;"
                                         onclick="fillFullAddress('<?= addslashes(htmlspecialchars($sAddr['recipient_name'])) ?>', '<?= addslashes(htmlspecialchars($sAddr['phone'])) ?>', '<?= addslashes(htmlspecialchars($sAddr['address'])) ?>')">
                                        <div class="small text-truncate me-2">
                                            <b><?= htmlspecialchars($sAddr['recipient_name']) ?></b> (<?= htmlspecialchars($sAddr['phone']) ?>)<br>
                                            <span class="text-muted text-truncate d-inline-block" style="max-width: 380px;"><?= htmlspecialchars($sAddr['address']) ?></span>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill py-0 px-2 small">ใช้ที่อยู่นี้</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ชื่อ - นามสกุล ผู้รับ <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control rounded-pill px-3" required 
                                   placeholder="เช่น น้องพิมพ์ใจ เรียนเก่ง" 
                                   value="<?= htmlspecialchars(!empty($saved_addresses) ? $saved_addresses[0]['recipient_name'] : ($user ? $user['fullname'] : '')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">เบอร์โทรศัพท์ติดต่อ <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_phone" id="customer_phone" class="form-control rounded-pill px-3" required 
                                   placeholder="เช่น 0812345678" pattern="[0-9]{9,10}"
                                   value="<?= htmlspecialchars(!empty($saved_addresses) ? $saved_addresses[0]['phone'] : '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">บ้านเลขที่, หมู่ที่, ซอย, ถนน หรืออาคาร <span class="text-danger">*</span></label>
                            <input type="text" name="addr_detail" id="addr_detail" class="form-control rounded-pill px-3" required
                                   placeholder="เช่น 123/45 หมู่ 2 ซอยสุขุมวิท 101"
                                   value="<?= htmlspecialchars(!empty($saved_addresses) ? $saved_addresses[0]['address'] : '') ?>">
                        </div>

                        <!-- Thailand Auto-complete fields -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">รหัสไปรษณีย์ <span class="badge bg-warning text-dark small">พิมพ์แล้วดึงอัตโนมัติ</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-pill"><i class="bi bi-mailbox2 text-danger"></i></span>
                                <input type="text" name="zipcode" id="zipcode" class="form-control rounded-end-pill px-3 fw-bold text-primary" placeholder="เช่น 10110">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">ตำบล / แขวง</label>
                            <input type="text" name="subdistrict" id="subdistrict" class="form-control rounded-pill px-3" placeholder="ตำบล / แขวง">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">อำเภอ / เขต</label>
                            <input type="text" name="district" id="district" class="form-control rounded-pill px-3" placeholder="อำเภอ / เขต">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">จังหวัด</label>
                            <input type="text" name="province" id="province" class="form-control rounded-pill px-3" placeholder="จังหวัด">
                        </div>
                    </div>

                    <div class="mt-4 mb-4">
                        <label class="form-label fw-bold">แนบสลิปหลักฐานการโอนเงิน (ถ้ามี)</label>
                        <div class="input-group">
                            <input type="file" name="payment_slip" class="form-control rounded-pill" accept="image/*" id="slipInput" onchange="previewSlip(event)">
                        </div>
                        <div id="slipPreviewContainer" class="mt-2 text-center d-none">
                            <img id="slipPreview" src="" class="rounded-3 shadow-sm border" style="max-height: 180px;">
                        </div>
                        <small class="text-muted d-block mt-1">แนบรูปภาพสลิป เพื่อให้ครูต๊ะตรวจสอบและจัดส่งสินค้าเร็วยิ่งขึ้น</small>
                    </div>

                    <button type="submit" class="btn btn-kruta-primary btn-lg w-100 py-3 shadow bounce-hover fw-bold">
                        🎉 ยืนยันการสั่งซื้อและแจ้งชำระเงิน
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
// Initialize Thailand Address Auto-complete (รหัสไปรษณีย์ ตำบล อำเภอ จังหวัด)
$(document).ready(function() {
    $.Thailand({
        database: 'https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/database/db.json',
        $district: $('#subdistrict'), // ตำบล
        $amphoe: $('#district'),      // อำเภอ
        $province: $('#province'),    // จังหวัด
        $zipcode: $('#zipcode'),      // รหัสไปรษณีย์
        onDataFill: function(data) {
            Toast.fire({
                icon: 'success',
                title: 'ดึงข้อมูล ' + data.district + ' > ' + data.amphoe + ' > ' + data.province + ' อัตโนมัติแล้ว! ✨'
            });
        }
    });
});

function fillFullAddress(name, phone, fullAddress) {
    document.getElementById('customer_name').value = name;
    document.getElementById('customer_phone').value = phone;
    document.getElementById('addr_detail').value = fullAddress;
    document.getElementById('subdistrict').value = '';
    document.getElementById('district').value = '';
    document.getElementById('province').value = '';
    document.getElementById('zipcode').value = '';

    // Highlight selected address card
    document.querySelectorAll('.saved-addr-card').forEach(el => el.classList.remove('border-danger', 'bg-light'));
    event.currentTarget.classList.add('border-danger', 'bg-light');

    Toast.fire({
        icon: 'success',
        title: 'เลือกใช้ที่อยู่เดิมเรียบร้อยแล้ว! 📍'
    });
}

function previewSlip(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('slipPreview');
        preview.src = reader.result;
        document.getElementById('slipPreviewContainer').classList.remove('d-none');
    };
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

<?php require_once 'footer.php'; ?>
