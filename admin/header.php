<?php
// admin/header.php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kruta Admin - แผงควบคุมหลังร้านครูต๊ะ</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Mali:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f4f6f9;
        }
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2D3436 0%, #1e272e 100%);
            color: #fff;
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            border-radius: 12px;
            padding: 10px 16px;
            margin-bottom: 6px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
            color: #fff;
            background: #FF6584;
        }
        .admin-card {
            border-radius: 18px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
            transition: transform 0.3s ease;
        }
        .admin-card:hover {
            transform: translateY(-4px);
        }
        .table thead th {
            font-weight: 600;
            color: #4b6584;
            border-top: none;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar p-3 collapse d-md-flex flex-column">
            <div class="d-flex align-items-center gap-2 mb-4 px-2">
                <span class="fs-2">🎒✏️</span>
                <div>
                    <h5 class="fw-bold mb-0 text-white" style="font-family: 'Mali', cursive;">Kruta Admin</h5>
                    <small class="text-secondary">ระบบจัดการหลังร้าน</small>
                </div>
            </div>

            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-speedometer2 me-2"></i> แดชบอร์ดสรุปยอด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                        <i class="bi bi-box-seam me-2"></i> จัดการคำสั่งซื้อ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'promptpay_settings.php' ? 'active' : '' ?>" href="promptpay_settings.php">
                        <i class="bi bi-qr-code-scan me-2 text-warning"></i> จัดการพร้อมเพย์ (QR)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ai_settings.php' ? 'active' : '' ?>" href="ai_settings.php">
                        <i class="bi bi-robot me-2 text-info"></i> AI เช็คสลิปอัตโนมัติ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'google_settings.php' ? 'active' : '' ?>" href="google_settings.php">
                        <i class="bi bi-google me-2 text-danger"></i> ตั้งค่า Google Sign-In
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" href="products.php">
                        <i class="bi bi-palette-fill me-2"></i> จัดการสินค้าเครื่องเขียน
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>" href="categories.php">
                        <i class="bi bi-tags me-2"></i> จัดการหมวดหมู่
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>" href="users.php">
                        <i class="bi bi-people-fill me-2"></i> สมาชิก & แอดมิน
                    </a>
                </li>
            </ul>

            <hr class="text-secondary">
            <div class="px-2">
                <a href="../index.php" class="btn btn-outline-light btn-sm w-100 rounded-pill mb-2">
                    <i class="bi bi-shop me-1"></i> ดูหน้าร้านจริง
                </a>
                <a href="../logout.php" class="btn btn-danger btn-sm w-100 rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
                </a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
