<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit;
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? 'bi-bookmark-star');
    $color = trim($_POST['color'] ?? '#FF6B6B');

    if (!empty($name)) {
        $ins = $conn->prepare("INSERT INTO categories (name, icon, color) VALUES (?, ?, ?)");
        $ins->execute([$name, $icon, $color]);
        header("Location: categories.php?msg=added");
        exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
    header("Location: categories.php?msg=deleted");
    exit;
}

$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC")->fetchAll();

require_once 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold font-mali"><i class="bi bi-tags text-primary me-2"></i> จัดการหมวดหมู่เครื่องเขียน</h1>
</div>

<div class="row g-4">
    <!-- Form -->
    <div class="col-md-5">
        <div class="card admin-card p-4 bg-white">
            <h5 class="fw-bold font-mali mb-3">✨ เพิ่มหมวดหมู่ใหม่</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อหมวดหมู่</label>
                    <input type="text" name="name" class="form-control rounded-pill" required placeholder="เช่น ยางลบแฟนซี & ไม้บรรทัด">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Bootstrap Icon Class</label>
                    <input type="text" name="icon" class="form-control rounded-pill" value="bi-stars" placeholder="เช่น bi-pen-fill, bi-palette-fill">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">สีประจำหมวดหมู่</label>
                    <input type="color" name="color" class="form-control form-control-color w-100 rounded-pill" value="#FF6584">
                </div>
                <button type="submit" name="add_category" class="btn btn-primary rounded-pill w-100 fw-bold">
                    บันทึกหมวดหมู่
                </button>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="col-md-7">
        <div class="card admin-card p-4 bg-white">
            <h5 class="fw-bold font-mali mb-3">📁 หมวดหมู่ทั้งหมด</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ไอคอน</th>
                            <th>ชื่อหมวดหมู่</th>
                            <th>จำนวนสินค้า</th>
                            <th class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" 
                                         style="width: 40px; height: 40px; background-color: <?= htmlspecialchars($cat['color']) ?>;">
                                        <i class="bi <?= htmlspecialchars($cat['icon']) ?> fs-5"></i>
                                    </div>
                                </td>
                                <td><b class="font-mali"><?= htmlspecialchars($cat['name']) ?></b></td>
                                <td><span class="badge bg-secondary"><?= $cat['product_count'] ?> รายการ</span></td>
                                <td class="text-end">
                                    <a href="categories.php?delete=<?= $cat['id'] ?>" onclick="return confirm('ลบหมวดหมู่นี้ สินค้าในหมวดหมู่นี้จะถูกลบด้วย?')" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
