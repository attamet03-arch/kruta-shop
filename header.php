<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME . ' | ' . SITE_TAGLINE ?></title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✏️</text></svg>">
    
    <!-- Google Fonts: Kanit & Mali for playful cute look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800;900&family=Mali:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- AOS (Animate on Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Canvas Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --primary-pink: #FF6584;
            --primary-purple: #6C5CE7;
            --primary-yellow: #FFD166;
            --primary-cyan: #48DBFB;
            --primary-mint: #1DD1A1;
            --bg-pastel: #F8F9FE;
            --text-dark: #2D3436;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--bg-pastel);
            color: var(--text-dark);
            overflow-x: hidden;
            position: relative;
        }

        .font-mali {
            font-family: 'Mali', cursive;
        }

        /* Animated Floating Background Bubbles */
        .bubble-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }
        .bubble {
            position: absolute;
            background: radial-gradient(circle, rgba(255,101,132,0.18) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            animation: floatBubble 12s infinite ease-in-out alternate;
        }
        .bubble:nth-child(1) { width: 320px; height: 320px; top: -50px; left: -50px; background: radial-gradient(circle, rgba(108,92,231,0.15) 0%, rgba(255,255,255,0) 70%); }
        .bubble:nth-child(2) { width: 450px; height: 450px; bottom: 5%; right: -100px; animation-duration: 16s; background: radial-gradient(circle, rgba(255,183,77,0.18) 0%, rgba(255,255,255,0) 70%); }
        .bubble:nth-child(3) { width: 250px; height: 250px; top: 40%; left: 80%; animation-duration: 10s; background: radial-gradient(circle, rgba(72,219,251,0.18) 0%, rgba(255,255,255,0) 70%); }

        @keyframes floatBubble {
            0% { transform: translateY(0px) rotate(0deg) scale(1); }
            50% { transform: translateY(-40px) rotate(180deg) scale(1.1); }
            100% { transform: translateY(30px) rotate(360deg) scale(0.95); }
        }

        /* Bounce & Hop Animations */
        .bounce-hover {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .bounce-hover:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 15px 30px rgba(108, 92, 231, 0.22) !important;
        }

        .jump-infinite {
            animation: jumpY 2.2s infinite ease-in-out;
            display: inline-block;
        }
        @keyframes jumpY {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px) rotate(4deg); }
        }

        .wiggle-hover:hover {
            animation: wiggle 0.6s ease-in-out infinite;
        }
        @keyframes wiggle {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-8deg); }
            75% { transform: rotate(8deg); }
        }

        .pulse-btn {
            animation: pulseGlow 2s infinite;
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(255, 101, 132, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(255, 101, 132, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 101, 132, 0); }
        }

        /* Custom Navbar */
        .kruta-navbar {
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.92) !important;
            border-bottom: 2px dashed #f1e0ff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            border-radius: 0 0 30px 30px;
        }
        .brand-logo {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(45deg, #FF6584, #6C5CE7, #00CEC9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        /* Badges & Buttons */
        .badge-cute {
            background: linear-gradient(135deg, #FF6584, #FF8ED4);
            color: white;
            font-size: 0.8rem;
            border-radius: 20px;
            padding: 5px 12px;
            font-weight: 600;
        }

        .btn-kruta-primary {
            background: linear-gradient(45deg, #FF6584, #FF8ED4);
            color: white !important;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            padding: 10px 24px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 20px rgba(255, 101, 132, 0.35);
        }
        .btn-kruta-primary:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 12px 25px rgba(255, 101, 132, 0.5);
        }

        .btn-kruta-purple {
            background: linear-gradient(45deg, #6C5CE7, #a29bfe);
            color: white !important;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            padding: 10px 24px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 20px rgba(108, 92, 231, 0.35);
        }
        .btn-kruta-purple:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 12px 25px rgba(108, 92, 231, 0.5);
        }

        /* Product Card */
        .product-card {
            border: none;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            overflow: hidden;
            position: relative;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .product-card:hover {
            transform: translateY(-10px) rotate(1deg);
            box-shadow: 0 20px 35px rgba(108, 92, 231, 0.2);
        }
        .product-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 220px;
            background: #fff5f7;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-img-wrapper img {
            transform: scale(1.12);
        }

        /* Floating Cart Button */
        .floating-cart-badge {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF6584, #6C5CE7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            box-shadow: 0 10px 25px rgba(255, 101, 132, 0.5);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
        }
        .floating-cart-badge:hover {
            transform: scale(1.15) rotate(15deg);
            color: #ffffff;
        }
        .floating-cart-badge .badge-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #FFD166;
            color: #2D3436;
            font-size: 14px;
            font-weight: 800;
            border-radius: 50%;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        /* Glassmorphism containers */
        .glass-box {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<!-- Floating Background Bubbles -->
<div class="bubble-bg">
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
</div>

<!-- Header / Navigation Bar -->
<nav class="navbar navbar-expand-lg kruta-navbar sticky-top py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <span class="jump-infinite" style="font-size: 2.2rem;">✏️</span>
            <div>
                <span class="brand-logo">Kruta Shop</span>
                <span class="d-block small text-muted font-mali" style="margin-top: -6px; font-size: 0.75rem;">✨ ครูต๊ะช็อป เครื่องเขียนสุดคิ้วท์</span>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#krutaNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="krutaNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 fw-bold">
                <li class="nav-item">
                    <a class="nav-link px-3 text-dark bounce-hover" href="index.php">
                        <i class="bi bi-house-door-fill text-danger me-1"></i> หน้าร้าน
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-dark bounce-hover" href="index.php#products-section">
                        <i class="bi bi-stars text-warning me-1"></i> สินค้าทั้งหมด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-dark bounce-hover" href="orders_track.php">
                        <i class="bi bi-truck text-info me-1"></i> ติดตามพัสดุ
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <!-- Cart Link in Navbar -->
                <a href="cart.php" class="btn btn-light rounded-pill px-3 py-2 fw-bold position-relative shadow-sm border bounce-hover">
                    <i class="bi bi-basket-fill text-danger me-1"></i> ตะกร้า
                    <span class="badge rounded-pill bg-danger ms-1 cart-count-pill"><?= cart_count() ?></span>
                </a>

                <?php if (is_logged_in()): ?>
                    <!-- User Menu -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary rounded-pill dropdown-toggle d-flex align-items-center gap-2 px-3 py-1 shadow-sm bg-white" data-bs-toggle="dropdown">
                            <img src="<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'https://api.dicebear.com/7.x/bottts/svg?seed=Kruta') ?>" class="rounded-circle" width="30" height="30" alt="avatar">
                            <span class="fw-bold text-truncate" style="max-width: 130px;"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-2">
                            <li class="px-3 py-1 text-muted small">
                                Role: <b><?= htmlspecialchars(strtoupper($_SESSION['user_role'])) ?></b>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (is_admin()): ?>
                                <li>
                                    <a class="dropdown-item rounded-3 fw-bold text-primary py-2" href="admin/index.php">
                                        <i class="bi bi-speedometer2 me-2"></i> แผงควบคุมหลังร้าน (Admin)
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item rounded-3 py-2" href="orders_track.php">
                                    <i class="bi bi-bag-check me-2"></i> ประวัติการสั่งซื้อของฉัน
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 py-2" href="user_addresses.php">
                                    <i class="bi bi-geo-alt me-2 text-danger"></i> สมุดที่อยู่จัดส่ง (สูงสุด 5 ที่)
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item rounded-3 text-danger py-2" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Login / Register Buttons -->
                    <a href="login.php" class="btn btn-kruta-primary">
                        <i class="bi bi-person-circle me-1"></i> เข้าสู่ระบบ
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Floating Cart Button (Mobile & Desktop quick access) -->
<a href="cart.php" class="floating-cart-badge bounce-hover pulse-btn shadow-lg" title="เปิดดูตะกร้าสินค้า">
    <i class="bi bi-cart3"></i>
    <span class="badge-count cart-count-pill"><?= cart_count() ?></span>
</a>
