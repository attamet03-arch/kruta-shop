<?php
$page_title = "หน้าแรก - รวมเครื่องเขียนสุดปัง";
require_once 'config.php';

// Fetch Categories
$categories = [];
try {
    $catStmt = $conn->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {}

// Filter by category or search
$cat_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($cat_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $cat_id;
}
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY p.id DESC";

$products = [];
try {
    $prodStmt = $conn->prepare($sql);
    $prodStmt->execute($params);
    $products = $prodStmt->fetchAll();
} catch (Exception $e) {}

require_once 'header.php';
?>

<!-- Hero Banner Section With Bouncing Interactive Badges -->
<section class="py-5 text-center position-relative overflow-hidden">
    <div class="container py-4">
        <!-- Floating Stickers -->
        <div class="d-none d-md-block">
            <span class="position-absolute jump-infinite" style="top: 15%; left: 8%; font-size: 3.5rem; filter: drop-shadow(0 8px 10px rgba(0,0,0,0.15));">💖</span>
            <span class="position-absolute jump-infinite" style="top: 25%; right: 10%; font-size: 4rem; animation-delay: 0.5s; filter: drop-shadow(0 8px 10px rgba(0,0,0,0.15));">🦄</span>
            <span class="position-absolute jump-infinite" style="bottom: 10%; left: 14%; font-size: 3rem; animation-delay: 0.8s; filter: drop-shadow(0 8px 10px rgba(0,0,0,0.15));">⭐️</span>
            <span class="position-absolute jump-infinite" style="bottom: 15%; right: 12%; font-size: 3.2rem; animation-delay: 1.2s; filter: drop-shadow(0 8px 10px rgba(0,0,0,0.15));">🧁</span>
        </div>

        <div class="row justify-content-center" data-aos="zoom-in" data-aos-duration="1000">
            <div class="col-lg-9">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm animate__animated animate__bounceIn">
                    🎉 คัดสรรเครื่องเขียนสุดน่ารัก เกรดดี จดสนุก เรียนเก่ง x100!
                </span>
                <h1 class="display-4 fw-black mb-3 font-mali" style="line-height: 1.25;">
                    เปิดเทอมใหม่ใจฟู กับ <br>
                    <span style="background: linear-gradient(120deg, #FF6584, #6C5CE7, #48DBFB); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        ร้านเครื่องเขียน ครูต๊ะ Kruta Shop
                    </span> ✨
                </h1>
                <p class="lead text-muted mb-4 font-mali fs-5">
                    รวมทุกไอเทมยอดฮิต! ปากกาเจลลื่นปรื๊ด ไฮไลท์พาสเทล สมุดกริด สมุดแพลนเนอร์ <br class="d-none d-md-block">
                    สั่งง่าย สแกนจ่ายไวด้วย <b>QR Code PromptPay</b> ส่งด่วนทันใจ 📦⚡
                </p>

                <!-- Search Box -->
                <form action="index.php#products-section" method="GET" class="d-flex justify-content-center mb-4">
                    <div class="input-group glass-box p-1" style="max-width: 550px; border-radius: 50px;">
                        <span class="input-group-text bg-transparent border-0 ps-3">
                            <i class="bi bi-search text-danger fs-5"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent shadow-none" placeholder="ค้นหาเครื่องเขียน เช่น สมุด, ปากกา, สีไม้..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-kruta-primary px-4 rounded-pill" type="submit">ค้นหาเลย 🚀</button>
                    </div>
                </form>

                <!-- Quick category pills -->
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <a href="index.php" class="btn btn-sm <?= $cat_id == 0 ? 'btn-dark' : 'btn-outline-secondary bg-white' ?> rounded-pill px-3 shadow-sm bounce-hover">
                        🌟 ทั้งหมด
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="index.php?category=<?= $cat['id'] ?>#products-section" 
                           class="btn btn-sm <?= $cat_id == $cat['id'] ? 'btn-danger' : 'btn-outline-secondary bg-white' ?> rounded-pill px-3 shadow-sm bounce-hover">
                            <i class="bi <?= $cat['icon'] ?> me-1"></i> <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Cards Section (Cards that bounce & tilt) -->
<section class="py-4">
    <div class="container">
        <div class="text-center mb-4" data-aos="fade-up">
            <h3 class="fw-bold font-mali"><i class="bi bi-grid-fill text-danger me-2"></i> หมวดหมู่เครื่องเขียนยอดนิยม</h3>
            <p class="text-muted">คลิกเพื่อเลือกช้อปไอเทมโปรดตามสไตล์ที่คุณชอบ</p>
        </div>

        <div class="row g-3 justify-content-center">
            <?php foreach ($categories as $index => $cat): ?>
                <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <a href="index.php?category=<?= $cat['id'] ?>#products-section" class="text-decoration-none">
                        <div class="card p-3 text-center border-0 rounded-4 shadow-sm h-100 bounce-hover" style="background: white;">
                            <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center shadow-sm wiggle-hover" 
                                 style="width: 55px; height: 55px; background-color: <?= $cat['color'] ?>25; color: <?= $cat['color'] ?>;">
                                <i class="bi <?= $cat['icon'] ?> fs-3"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0 font-mali" style="font-size: 0.95rem;"><?= htmlspecialchars($cat['name']) ?></h6>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Products Grid Section -->
<section class="py-5" id="products-section">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold font-mali mb-1">
                    🛒 เครื่องเขียนพร้อมส่ง 
                    <?php if ($cat_id > 0): ?>
                        <span class="badge bg-danger rounded-pill fs-6">หมวดหมู่พิเศษ</span>
                    <?php endif; ?>
                </h2>
                <p class="text-muted mb-0">พบสินค้าทั้งหมด <?= count($products) ?> รายการ พร้อมดีลราคานักเรียน!</p>
            </div>
            <?php if ($cat_id > 0 || !empty($search)): ?>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill btn-sm mt-2 mt-md-0 shadow-sm">
                    <i class="bi bi-x-circle me-1"></i> ล้างตัวกรอง
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-5 glass-box p-5 my-4">
                <span class="display-1">📦🔍</span>
                <h4 class="fw-bold mt-3 font-mali">ยังไม่พบสินค้าที่ต้องการค้นหา</h4>
                <p class="text-muted">ลองค้นหาด้วยคำอื่น หรือกดดูสินค้าทั้งหมดของครูต๊ะได้เลยครับ</p>
                <a href="index.php" class="btn btn-kruta-primary mt-2">ดูสินค้าทั้งหมด</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $idx => $prod): ?>
                    <div class="col-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="<?= ($idx % 4) * 80 ?>">
                        <div class="card product-card h-100">
                            <!-- Product Image -->
                            <div class="product-img-wrapper">
                                <span class="position-absolute top-0 start-0 m-2 badge badge-cute shadow-sm z-2">
                                    <?= htmlspecialchars($prod['badge'] ?? 'NEW') ?>
                                </span>
                                <img src="<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy">
                            </div>

                            <!-- Body -->
                            <div class="card-body p-3 d-flex flex-column">
                                <span class="badge bg-light text-secondary rounded-pill align-self-start mb-2 small border" style="font-size: 0.75rem;">
                                    <?= htmlspecialchars($prod['category_name']) ?>
                                </span>
                                <h6 class="card-title fw-bold text-dark mb-1 text-truncate font-mali" title="<?= htmlspecialchars($prod['name']) ?>">
                                    <?= htmlspecialchars($prod['name']) ?>
                                </h6>
                                <p class="card-text text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.8rem;">
                                    <?= htmlspecialchars($prod['description']) ?>
                                </p>

                                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                                    <div>
                                        <span class="fs-5 fw-black text-danger"><?= format_price($prod['price']) ?></span>
                                    </div>
                                    <button onclick="addToCart(<?= $prod['id'] ?>, '<?= addslashes(htmlspecialchars($prod['name'])) ?>')" 
                                            class="btn btn-kruta-primary btn-sm rounded-pill px-3 shadow-sm bounce-hover">
                                        <i class="bi bi-cart-plus-fill me-1"></i> ซื้อเลย
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Promotional Banner (Bouncing Animation) -->
<section class="container my-5">
    <div class="p-4 p-md-5 rounded-5 shadow-lg position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #FF6584 0%, #6C5CE7 100%);" data-aos="flip-up">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold mb-2">⚡ พิเศษเฉพาะวันนี้</span>
                <h2 class="display-6 fw-bold font-mali mb-2">ส่งฟรีทั่วไทย! เมื่อสั่งครบ 300 บาท 🎁</h2>
                <p class="mb-3 lead fs-6">อย่าลืมชวนเพื่อนมาแชร์เครื่องเขียนน่ารัก ๆ ถ่ายรูปอวดลงสตอรี่ไอจีแท็ก @krutashop รับโค้ดส่วนลด 10% ทันที!</p>
                <a href="#products-section" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow bounce-hover">
                    🛍️ ช้อปให้จุใจ คลิกเลย
                </a>
            </div>
            <div class="col-lg-4 text-center mt-4 mt-lg-0">
                <div class="jump-infinite" style="font-size: 6rem;">
                    🎁📚
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
