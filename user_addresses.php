<?php
$page_title = "สมุดที่อยู่ของฉัน (บันทึกสูงสุด 5 ที่อยู่)";
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';
$msg_type = 'success';

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$del_id, $user_id]);
    header("Location: user_addresses.php?msg=deleted");
    exit;
}

// Handle Set Default
if (isset($_GET['set_default'])) {
    $def_id = (int)$_GET['set_default'];
    $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$def_id, $user_id]);
    header("Location: user_addresses.php?msg=default_set");
    exit;
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $addr_detail = trim($_POST['addr_detail'] ?? '');
    $subdistrict = trim($_POST['subdistrict'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');

    if (!empty($subdistrict) || !empty($province)) {
        $address = "{$addr_detail} ต./แขวง {$subdistrict} อ./เขต {$district} จ.{$province} {$zipcode}";
    } else {
        $address = trim($_POST['address'] ?? '');
    }

    if (empty($recipient_name) || empty($phone) || empty($address)) {
        $msg = "กรุณากรอกข้อมูลให้ครบถ้วน";
        $msg_type = "danger";
    } else {
        // Check current count
        $cnt = $conn->prepare("SELECT COUNT(*) as total FROM user_addresses WHERE user_id = ?");
        $cnt->execute([$user_id]);
        $total = $cnt->fetch()['total'];

        if ($total >= 5) {
            $msg = "บันทึกที่อยู่ได้สูงสุด 5 ที่อยู่แล้ว กรุณาลบที่อยู่เก่าออกก่อนครับ";
            $msg_type = "warning";
        } else {
            $ins = $conn->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone, address, is_default) VALUES (?, ?, ?, ?, ?)");
            $isDef = ($total == 0) ? 1 : 0;
            $ins->execute([$user_id, $recipient_name, $phone, $address, $isDef]);
            header("Location: user_addresses.php?msg=added");
            exit;
        }
    }
}

// Fetch user addresses
$addresses = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 5");
$addresses->execute([$user_id]);
$address_list = $addresses->fetchAll();

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
        <h2 class="fw-bold font-mali"><span class="jump-infinite">📍</span> สมุดที่อยู่จัดส่งของฉัน</h2>
        <p class="text-muted">ระบบจะบันทึกที่อยู่อัตโนมัติทุกครั้งเมื่อสั่งซื้อ (สูงสุด 5 ที่อยู่) พร้อมระบบดึงรหัสไปรษณีย์และตำบลอำเภออัตโนมัติ</p>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success rounded-4 text-center fw-bold shadow-sm">
            <?= $_GET['msg'] === 'deleted' ? 'ลบที่อยู่เรียบร้อยแล้ว' : ($_GET['msg'] === 'default_set' ? 'ตั้งเป็นที่อยู่หลักเรียบร้อยแล้ว' : 'เพิ่มที่อยู่ใหม่เรียบร้อยแล้ว') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?= $msg_type ?> rounded-4 text-center fw-bold shadow-sm">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">
        <!-- Add Form with AutoComplete -->
        <div class="col-lg-6" data-aos="fade-right">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold font-mali mb-0 text-dark"><i class="bi bi-plus-circle text-danger me-2"></i> เพิ่มที่อยู่ใหม่ (<?= count($address_list) ?>/5)</h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                        <i class="bi bi-magic"></i> Auto-fill ที่อยู่ไทย
                    </span>
                </div>
                
                <?php if (count($address_list) >= 5): ?>
                    <div class="alert alert-warning small rounded-3 mb-0">
                        คุณมีที่อยู่ครบโควต้า 5 ที่อยู่แล้ว ระบบจะหมุนเวียนบันทึกที่อยู่ใหม่อัตโนมัติเมื่อสั่งซื้อ หรือลบที่อยู่เก่าออกเพื่อเพิ่มเองได้ครับ
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">ชื่อ - นามสกุล ผู้รับ <span class="text-danger">*</span></label>
                                <input type="text" name="recipient_name" class="form-control rounded-pill px-3" required placeholder="เช่น ด.ญ. ฟ้าใส เรียนดี">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control rounded-pill px-3" required placeholder="เช่น 0812345678" pattern="[0-9]{9,10}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">บ้านเลขที่, หมู่, ซอย, ถนน <span class="text-danger">*</span></label>
                                <input type="text" name="addr_detail" class="form-control rounded-pill px-3" required placeholder="เช่น 45/6 หมู่ 3 ถนนพหลโยธิน">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">รหัสไปรษณีย์ <span class="badge bg-warning text-dark small">พิมพ์แล้วดึงอัตโนมัติ</span></label>
                                <input type="text" name="zipcode" id="addr_zipcode" class="form-control rounded-pill px-3 fw-bold text-primary" placeholder="เช่น 10110">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">ตำบล / แขวง</label>
                                <input type="text" name="subdistrict" id="addr_subdistrict" class="form-control rounded-pill px-3" placeholder="ตำบล / แขวง">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">อำเภอ / เขต</label>
                                <input type="text" name="district" id="addr_district" class="form-control rounded-pill px-3" placeholder="อำเภอ / เขต">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">จังหวัด</label>
                                <input type="text" name="province" id="addr_province" class="form-control rounded-pill px-3" placeholder="จังหวัด">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-kruta-primary w-100 rounded-pill py-2 mt-4 fw-bold shadow-sm">
                            บันทึกที่อยู่นี้ 📍
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Address List -->
        <div class="col-lg-6" data-aos="fade-left">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold font-mali mb-0 text-dark">📋 รายการที่อยู่ที่บันทึกไว้</h5>
                    <span class="badge bg-danger rounded-pill"><?= count($address_list) ?> / 5 ที่อยู่</span>
                </div>

                <?php if (empty($address_list)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-geo-alt fs-1 text-secondary"></i>
                        <p class="mt-2 mb-0">ยังไม่มีที่อยู่ที่บันทึกไว้</p>
                        <small>เมื่อสั่งซื้อสินค้า ระบบจะบันทึกที่อยู่ให้อัตโนมัติครับ</small>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($address_list as $addr): ?>
                            <div class="p-3 rounded-4 border position-relative <?= $addr['is_default'] ? 'bg-light border-danger' : 'bg-white' ?> shadow-sm">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <b class="font-mali text-dark fs-6"><?= htmlspecialchars($addr['recipient_name']) ?></b>
                                        <span class="text-muted small ms-2"><i class="bi bi-telephone"></i> <?= htmlspecialchars($addr['phone']) ?></span>
                                    </div>
                                    <?php if ($addr['is_default']): ?>
                                        <span class="badge bg-danger rounded-pill px-2 py-1 small">⭐ ค่าเริ่มต้น</span>
                                    <?php else: ?>
                                        <a href="user_addresses.php?set_default=<?= $addr['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2 small">
                                            ตั้งเป็นค่าเริ่มต้น
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars($addr['address'])) ?></p>
                                <div class="text-end">
                                    <a href="user_addresses.php?delete=<?= $addr['id'] ?>" onclick="return confirm('ต้องการลบที่อยู่นี้ใช่หรือไม่?')" class="text-danger small text-decoration-none">
                                        <i class="bi bi-trash"></i> ลบที่อยู่
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-complete for address book
$(document).ready(function() {
    $.Thailand({
        database: 'https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/database/db.json',
        $district: $('#addr_subdistrict'),
        $amphoe: $('#addr_district'),
        $province: $('#addr_province'),
        $zipcode: $('#addr_zipcode'),
        onDataFill: function(data) {
            Toast.fire({
                icon: 'success',
                title: 'ดึงข้อมูลที่อยู่ ' + data.district + ' > ' + data.amphoe + ' > ' + data.province + ' อัตโนมัติแล้ว! ✨'
            });
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>
