<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$del_id]);
    header("Location: products.php?msg=deleted");
    exit;
}

// Handle Add / Edit
$error = '';
$product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $product = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $badge = trim($_POST['badge'] ?? 'HOT');
    $image = trim($_POST['image'] ?? '');

    // Handle Image Upload if provided
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
        if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
        move_uploaded_file($_FILES['image_file']['tmp_name'], '../uploads/' . $filename);
        $image = 'uploads/' . $filename;
    }

    if (empty($name) || $price <= 0) {
        $error = "กรุณากรอกชื่อสินค้าและราคาให้ถูกต้อง";
    } else {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update
            $u_id = (int)$_POST['id'];
            $sql = "UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, description = ?, badge = ?" . ($image ? ", image = ?" : "") . " WHERE id = ?";
            $params = [$name, $category_id, $price, $stock, $description, $badge];
            if ($image) $params[] = $image;
            $params[] = $u_id;
            $conn->prepare($sql)->execute($params);
            header("Location: products.php?msg=updated");
            exit;
        } else {
            // Insert
            if (empty($image)) {
                $image = 'https://images.unsplash.com/photo-1585336261026-62024b3cf68c?w=500';
            }
            $ins = $conn->prepare("INSERT INTO products (name, category_id, price, stock, description, badge, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$name, $category_id, $price, $stock, $description, $badge, $image]);
            header("Location: products.php?msg=created");
            exit;
        }
    }
}

// Fetch categories
$cats = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
// Fetch products
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();

require_once 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold font-mali"><i class="bi bi-pencil-square text-danger me-2"></i> จัดการสินค้าเครื่องเขียน</h1>
    <button class="btn btn-primary rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#productForm">
        <i class="bi bi-plus-lg me-1"></i> <?= $product ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' ?>
    </button>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger rounded-4"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Product Form Collapse -->
<div class="collapse <?= ($product || isset($_GET['action'])) ? 'show' : '' ?> mb-4" id="productForm">
    <div class="card admin-card p-4 bg-white border">
        <h5 class="fw-bold font-mali mb-3"><?= $product ? '✏️ แก้ไขข้อมูลสินค้า' : '✨ เพิ่มสินค้าเครื่องเขียนใหม่' ?></h5>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($product): ?>
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">ชื่อสินค้า <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-pill" required value="<?= htmlspecialchars($product['name'] ?? '') ?>" placeholder="เช่น ปากกาเจล Macaron สีพาสเทล">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">หมวดหมู่ <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select rounded-pill" required>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($product['category_id']) && $product['category_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">ป้ายกำกับ (Badge)</label>
                    <input type="text" name="badge" class="form-control rounded-pill" value="<?= htmlspecialchars($product['badge'] ?? 'HOT') ?>" placeholder="เช่น 🔥 ขายดี, ✨ แนะนำ">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">ราคา (บาท) <span class="text-danger">*</span></label>
                    <input type="number" step="0.5" name="price" class="form-control rounded-pill" required value="<?= htmlspecialchars($product['price'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">จำนวนสต็อก</label>
                    <input type="number" name="stock" class="form-control rounded-pill" required value="<?= htmlspecialchars($product['stock'] ?? '50') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">URL รูปภาพ หรือ อัปโหลดจากคอมฯ</label>
                    <input type="url" name="image" class="form-control rounded-pill mb-2" value="<?= htmlspecialchars($product['image'] ?? '') ?>" placeholder="https://example.com/image.jpg">
                    <input type="file" name="image_file" class="form-control rounded-pill" accept="image/*">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">รายละเอียดสินค้า</label>
                    <textarea name="description" class="form-control rounded-4" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">บันทึกข้อมูลสินค้า</button>
                <a href="products.php" class="btn btn-light rounded-pill px-3">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card admin-card p-4 bg-white">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>รูปสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>หมวดหมู่</th>
                    <th>ราคา</th>
                    <th>คงเหลือ</th>
                    <th>ป้าย</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($p['image']) ?>" class="rounded-3 shadow-sm" width="50" height="50" style="object-fit:cover;">
                        </td>
                        <td><b class="font-mali"><?= htmlspecialchars($p['name']) ?></b></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span></td>
                        <td class="fw-bold text-danger"><?= format_price($p['price']) ?></td>
                        <td><?= $p['stock'] ?> ชิ้น</td>
                        <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($p['badge'] ?? '') ?></span></td>
                        <td class="text-end">
                            <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="products.php?delete=<?= $p['id'] ?>" onclick="return confirm('ยืนยันลบสินค้านี้?')" class="btn btn-sm btn-outline-danger rounded-pill">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
