<?php
require_once 'header.php';

// Stats
$total_sales = 0;
$total_orders = 0;
$pending_orders = 0;
$total_products = 0;

try {
    $saleStmt = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
    $total_sales = $saleStmt->fetch()['total'] ?? 0;

    $orderStmt = $conn->query("SELECT COUNT(*) as total FROM orders");
    $total_orders = $orderStmt->fetch()['total'] ?? 0;

    $pendingStmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $pending_orders = $pendingStmt->fetch()['total'] ?? 0;

    $prodStmt = $conn->query("SELECT COUNT(*) as total FROM products");
    $total_products = $prodStmt->fetch()['total'] ?? 0;

    // Recent orders
    $recentStmt = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
    $recent_orders = $recentStmt->fetchAll();
} catch (Exception $e) {}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold font-mali">📊 ภาพรวมแดชบอร์ด (Dashboard)</h1>
        <p class="text-muted mb-0">ยินดีต้อนรับคุณ <?= htmlspecialchars($_SESSION['user_name']) ?> สู่ระบบหลังร้านครูต๊ะ</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="products.php?action=create" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้าใหม่
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">ยอดขายรวมทั้งหมด</span>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= format_price($total_sales) ?></h3>
                </div>
                <div class="rounded-circle p-3 bg-success-subtle text-success fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">คำสั่งซื้อทั้งหมด</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1"><?= $total_orders ?> ออเดอร์</h3>
                </div>
                <div class="rounded-circle p-3 bg-primary-subtle text-primary fs-3">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">รอตรวจสอบชำระ</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= $pending_orders ?> รายการ</h3>
                </div>
                <div class="rounded-circle p-3 bg-warning-subtle text-warning fs-3">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card admin-card p-3 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold">สินค้าในสต็อก</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1"><?= $total_products ?> ชิ้น</h3>
                </div>
                <div class="rounded-circle p-3 bg-danger-subtle text-danger fs-3">
                    <i class="bi bi-box"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card admin-card p-4 bg-white mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 font-mali"><i class="bi bi-clock-fill text-primary me-2"></i> รายการสั่งซื้อล่าสุด</h5>
        <a href="orders.php" class="btn btn-sm btn-outline-secondary rounded-pill">ดูทั้งหมด</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>รหัสสั่งซื้อ</th>
                    <th>ผู้สั่งซื้อ</th>
                    <th>เบอร์โทร</th>
                    <th>ยอดรวม</th>
                    <th>สลิปโอน</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">ยังไม่มีรายการสั่งซื้อ</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_orders as $ord): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($ord['order_code']) ?></td>
                            <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                            <td><?= htmlspecialchars($ord['customer_phone']) ?></td>
                            <td class="fw-bold text-danger"><?= format_price($ord['total_amount']) ?></td>
                            <td>
                                <?php if ($ord['payment_slip']): ?>
                                    <a href="../uploads/<?= htmlspecialchars($ord['payment_slip']) ?>" target="_blank" class="badge bg-info text-dark text-decoration-none">
                                        <i class="bi bi-eye"></i> ดูสลิป
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">- ไม่มี -</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $ord['status'] === 'paid' ? 'success' : ($ord['status'] === 'pending' ? 'warning text-dark' : 'secondary') ?> rounded-pill">
                                    <?= htmlspecialchars($ord['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="orders.php?view=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                    ตรวจเช็ค
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
