<?php
$page_title = "สั่งซื้อสำเร็จ - ครูต๊ะช็อป";
require_once 'config.php';

$code = $_GET['code'] ?? '';
$order = null;

if (!empty($code)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ?");
        $stmt->execute([$code]);
        $order = $stmt->fetch();
    } catch (Exception $e) {}
}

if (!$order) {
    header('Location: index.php');
    exit;
}

require_once 'header.php';
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-6" data-aos="zoom-in">
            <div class="card border-0 rounded-5 shadow-lg p-5 bg-white position-relative overflow-hidden">
                <span class="jump-infinite display-1 mb-2">🎉</span>
                <h2 class="fw-bold font-mali text-danger mb-2">สั่งซื้อสำเร็จแล้วจ้า!</h2>
                <p class="text-muted">ขอบคุณที่เลือกช้อปเครื่องเขียนกับ ครูต๊ะช็อป Kruta Shop นะครับ</p>

                <div class="p-3 bg-light rounded-4 my-3 text-start">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">รหัสคำสั่งซื้อ:</span>
                        <b class="text-primary fs-5"><?= htmlspecialchars($order['order_code']) ?></b>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ชื่อผู้รับ:</span>
                        <b><?= htmlspecialchars($order['customer_name']) ?></b>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ยอดชำระ:</span>
                        <b class="text-danger fs-5"><?= format_price($order['total_amount']) ?></b>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">สถานะ:</span>
                        <span class="badge bg-<?= $order['status'] === 'paid' ? 'success' : 'warning' ?> rounded-pill px-3 py-2">
                            <?= $order['status'] === 'paid' ? 'ชำระเงินเรียบร้อย รอจัดส่ง 🚚' : 'รอตรวจสอบการชำระเงิน ⏳' ?>
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-3">
                    <a href="index.php" class="btn btn-kruta-primary px-4 rounded-pill">
                        <i class="bi bi-cart-plus me-1"></i> ช้อปเครื่องเขียนต่อ
                    </a>
                    <a href="orders_track.php?search=<?= urlencode($order['order_code']) ?>" class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="bi bi-search me-1"></i> ดูสถานะพัสดุ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Confetti celebration
    window.addEventListener('load', () => {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    });
</script>

<?php require_once 'footer.php'; ?>
