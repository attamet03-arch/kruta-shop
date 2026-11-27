<?php
$page_title = "ตะกร้าสินค้าของฉัน";
require_once 'config.php';

$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $in = str_repeat('?,', count($ids) - 1) . '?';
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($in)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['id']];
            $subtotal = $p['price'] * $qty;
            $total_price += $subtotal;
            $cart_items[] = [
                'product' => $p,
                'qty' => $qty,
                'subtotal' => $subtotal
            ];
        }
    } catch (Exception $e) {}
}

require_once 'header.php';
?>

<div class="container py-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold font-mali"><span class="jump-infinite">🛒</span> ตะกร้าสินค้าเครื่องเขียน</h2>
        <p class="text-muted">ตรวจสอบรายการสินค้าและพร้อมสแกนจ่ายเงินง่าย ๆ</p>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="glass-box p-5 text-center my-4" data-aos="zoom-in">
            <span class="display-1">🛍️💨</span>
            <h4 class="fw-bold font-mali mt-3">ตะกร้าของคุณยังว่างเปล่า</h4>
            <p class="text-muted">ไปเลือกปากกา สมุด หรือไอเทมคิ้วท์ ๆ เพิ่มลงตะกร้ากันเถอะ!</p>
            <a href="index.php" class="btn btn-kruta-primary btn-lg mt-2 px-5">
                <i class="bi bi-cart-plus me-1"></i> ช้อปเครื่องเขียนเลย
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Items list -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                        <h5 class="fw-bold mb-0 font-mali">รายการสินค้าในตะกร้า (<?= count($cart_items) ?> รายการ)</h5>
                        <button onclick="clearCart()" class="btn btn-outline-danger btn-sm rounded-pill">
                            <i class="bi bi-trash3 me-1"></i> ล้างตะกร้าทั้งหมด
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>สินค้า</th>
                                    <th>ราคา</th>
                                    <th style="width: 140px;">จำนวน</th>
                                    <th>รวม</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): 
                                    $p = $item['product'];
                                ?>
                                    <tr id="row-<?= $p['id'] ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= htmlspecialchars($p['image']) ?>" class="rounded-3 shadow-sm" width="65" height="65" style="object-fit:cover;">
                                                <div>
                                                    <h6 class="mb-0 fw-bold font-mali text-dark"><?= htmlspecialchars($p['name']) ?></h6>
                                                    <span class="badge bg-light text-muted border small"><?= htmlspecialchars($p['badge'] ?? '') ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-muted"><?= format_price($p['price']) ?></td>
                                        <td>
                                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                                <button class="btn btn-outline-secondary" onclick="updateQty(<?= $p['id'] ?>, <?= $item['qty'] - 1 ?>)">-</button>
                                                <input type="number" class="form-control text-center fw-bold" value="<?= $item['qty'] ?>" min="1" onchange="updateQty(<?= $p['id'] ?>, this.value)">
                                                <button class="btn btn-outline-secondary" onclick="updateQty(<?= $p['id'] ?>, <?= $item['qty'] + 1 ?>)">+</button>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-danger fs-6"><?= format_price($item['subtotal']) ?></td>
                                        <td>
                                            <button onclick="removeItem(<?= $p['id'] ?>)" class="btn btn-link text-danger p-0">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-arrow-left me-1"></i> เลือกซื้อเครื่องเขียนเพิ่ม
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4" data-aos="fade-left">
                <div class="card border-0 rounded-4 shadow-sm p-4 bg-white sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3 font-mali text-dark">สรุปคำสั่งซื้อ 🧾</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ยอดรวมสินค้า</span>
                        <span class="fw-bold"><?= format_price($total_price) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">ค่าจัดส่งด่วน</span>
                        <span class="text-success fw-bold">ฟรี! โปรโมชั่น 🚚</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold fs-5 font-mali">ยอดสุทธิที่ต้องชำระ</span>
                        <span class="fw-black text-danger display-6" style="font-size: 1.8rem;"><?= format_price($total_price) ?></span>
                    </div>

                    <a href="checkout.php" class="btn btn-kruta-primary btn-lg w-100 py-3 shadow bounce-hover text-center d-block">
                        <i class="bi bi-qr-code me-2"></i> สั่งซื้อ & ชำระเงิน (QR Code) 🚀
                    </a>

                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="bi bi-shield-check text-success"></i> ชำระเงินปลอดภัย ผ่าน PromptPay ทุกธนาคาร</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQty(id, qty) {
    if (qty <= 0) {
        removeItem(id);
        return;
    }
    fetch('cart_action.php?action=update&id=' + id + '&qty=' + qty)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            }
        });
}

function removeItem(id) {
    Swal.fire({
        title: 'ลบสินค้านี้?',
        text: "คุณต้องการนำสินค้านี้ออกจากตะกร้าใช่หรือไม่",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF6584',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, นำออกเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('cart_action.php?action=remove&id=' + id)
                .then(res => res.json())
                .then(data => {
                    location.reload();
                });
        }
    });
}

function clearCart() {
    Swal.fire({
        title: 'ล้างตะกร้าสินค้า?',
        text: "คุณต้องการลบสินค้าทั้งหมดออกจากตะกร้าใช่หรือไม่",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF6584',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ล้างตะกร้า',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('cart_action.php?action=clear')
                .then(res => res.json())
                .then(data => {
                    location.reload();
                });
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>
