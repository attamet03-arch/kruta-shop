<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    header("Location: orders.php?msg=updated");
    exit;
}

// Fetch all orders
$orders = $conn->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();

require_once 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold font-mali"><i class="bi bi-box-seam text-danger me-2"></i> จัดการคำสั่งซื้อ (Orders)</h1>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
        อัปเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card admin-card p-4 bg-white">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>วันที่</th>
                    <th>รหัสคำสั่งซื้อ</th>
                    <th>ลูกค้า & ที่อยู่จัดส่ง</th>
                    <th>ยอดชำระ</th>
                    <th>สลิป PromptPay</th>
                    <th>สถานะปัจจุบัน</th>
                    <th>เปลี่ยนสถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">ยังไม่มีรายการสั่งซื้อ</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): 
                        $items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                        $items->execute([$ord['id']]);
                        $order_items = $items->fetchAll();
                    ?>
                        <tr>
                            <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                            <td><b class="text-primary"><?= htmlspecialchars($ord['order_code']) ?></b></td>
                            <td>
                                <b><?= htmlspecialchars($ord['customer_name']) ?></b> (<?= htmlspecialchars($ord['customer_phone']) ?>)<br>
                                <small class="text-muted"><?= htmlspecialchars($ord['shipping_address']) ?></small>
                                
                                <div class="mt-1 small bg-light p-2 rounded">
                                    <?php foreach ($order_items as $it): ?>
                                        <div>• <?= htmlspecialchars($it['product_name']) ?> x <?= $it['quantity'] ?> (<?= format_price($it['subtotal']) ?>)</div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="fw-bold text-danger fs-6"><?= format_price($ord['total_amount']) ?></td>
                            <td>
                                <?php if ($ord['payment_slip']): ?>
                                    <a href="../uploads/<?= htmlspecialchars($ord['payment_slip']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                        <i class="bi bi-file-earmark-image"></i> ตรวจสลิป
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ไม่มีสลิป</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badgeClass = [
                                    'pending' => 'warning text-dark',
                                    'paid' => 'info text-dark',
                                    'shipped' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $badgeClass[$ord['status']] ?? 'secondary' ?> rounded-pill px-3 py-2">
                                    <?= strtoupper($ord['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                    <select name="status" class="form-select form-select-sm rounded-pill" style="width: 130px;">
                                        <option value="pending" <?= $ord['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                        <option value="paid" <?= $ord['status'] === 'paid' ? 'selected' : '' ?>>💰 Paid</option>
                                        <option value="shipped" <?= $ord['status'] === 'shipped' ? 'selected' : '' ?>>🚚 Shipped</option>
                                        <option value="cancelled" <?= $ord['status'] === 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-dark rounded-pill">
                                        บันทึก
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
