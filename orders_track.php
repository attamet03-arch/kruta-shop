<?php
$page_title = "ติดตามสถานะคำสั่งซื้อ & พัสดุ";
require_once 'config.php';

$search = trim($_GET['search'] ?? '');
$orders = [];

if (is_logged_in() && empty($search)) {
    // Current user orders
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} elseif (!empty($search)) {
    // Search by code or phone
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ? OR customer_phone = ? ORDER BY id DESC");
    $stmt->execute([$search, $search]);
    $orders = $stmt->fetchAll();
}

require_once 'header.php';
?>

<div class="container py-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold font-mali"><span class="jump-infinite">🚚</span> ติดตามสถานะคำสั่งซื้อ</h2>
        <p class="text-muted">กรอกรหัสคำสั่งซื้อ (เช่น KT-20261127-xxxx) หรือเบอร์โทรศัพท์เพื่อค้นหา</p>

        <!-- Search Form -->
        <form method="GET" class="d-flex justify-content-center mt-3">
            <div class="input-group glass-box p-1" style="max-width: 500px; border-radius: 50px;">
                <input type="text" name="search" class="form-control border-0 bg-transparent ps-4 shadow-none" 
                       placeholder="รหัสคำสั่งซื้อ หรือ เบอร์โทร..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-kruta-primary px-4 rounded-pill" type="submit">ค้นหาพัสดุ 🔍</button>
            </div>
        </form>
    </div>

    <?php if (empty($orders)): ?>
        <div class="glass-box p-5 text-center my-4" data-aos="zoom-in">
            <span class="display-3">📦🔍</span>
            <h5 class="fw-bold font-mali mt-3">ไม่พบรายการสั่งซื้อที่ค้นหา</h5>
            <p class="text-muted">กรุณาตรวจสอบรหัสคำสั่งซื้อ หรือ เบอร์โทรศัพท์อีกครั้งนะครับ</p>
        </div>
    <?php else: ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($orders as $order): 
                // Fetch items
                $itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemStmt->execute([$order['id']]);
                $items = $itemStmt->fetchAll();
            ?>
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <div>
                                <span class="badge bg-dark rounded-pill px-3 py-1 mb-1">เลขที่คำสั่งซื้อ: <?= htmlspecialchars($order['order_code']) ?></span>
                                <div class="small text-muted">วันที่: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?> น.</div>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <?php
                                $statusBadge = [
                                    'pending' => ['bg' => 'warning text-dark', 'label' => '⏳ รอตรวจสอบการชำระเงิน'],
                                    'paid' => ['bg' => 'info text-dark', 'label' => '💰 ชำระเงินแล้ว กำลังแพ็ก'],
                                    'shipped' => ['bg' => 'success text-white', 'label' => '🚚 จัดส่งพัสดุแล้ว'],
                                    'cancelled' => ['bg' => 'secondary text-white', 'label' => '❌ ยกเลิกคำสั่งซื้อ']
                                ];
                                $s = $statusBadge[$order['status']] ?? ['bg' => 'secondary', 'label' => $order['status']];
                                ?>
                                <span class="badge bg-<?= $s['bg'] ?> rounded-pill px-3 py-2 fs-6">
                                    <?= $s['label'] ?>
                                </span>
                            </div>
                        </div>

                        <!-- Customer details -->
                        <div class="row mb-3 small">
                            <div class="col-md-6">
                                <b>ผู้รับ:</b> <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_phone']) ?>)<br>
                                <b>ที่อยู่จัดส่ง:</b> <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                            </div>
                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                <b>ยอดรวมสุทธิ:</b> <span class="text-danger fw-bold fs-5"><?= format_price($order['total_amount']) ?></span><br>
                                <?php if ($order['payment_slip']): ?>
                                    <a href="uploads/<?= htmlspecialchars($order['payment_slip']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill mt-1">
                                        <i class="bi bi-image me-1"></i> ดูสลิปที่แนบ
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Item table -->
                        <div class="table-responsive bg-light rounded-3 p-2">
                            <table class="table table-sm table-borderless mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>รายการเครื่องเขียน</th>
                                        <th class="text-center">ราคา</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-end">รวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $it): ?>
                                        <tr>
                                            <td class="fw-bold font-mali"><?= htmlspecialchars($it['product_name']) ?></td>
                                            <td class="text-center"><?= format_price($it['price']) ?></td>
                                            <td class="text-center"><?= $it['quantity'] ?></td>
                                            <td class="text-end fw-bold"><?= format_price($it['subtotal']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
