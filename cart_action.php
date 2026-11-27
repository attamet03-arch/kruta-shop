<?php
// cart_action.php - Handle AJAX / Direct cart actions
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'add' && $id > 0) {
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += 1;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
    echo json_encode([
        'status' => 'success',
        'message' => 'เพิ่มสินค้าเรียบร้อย',
        'total_cart' => cart_count()
    ]);
    exit;
}

if ($action === 'update' && $id > 0) {
    $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    echo json_encode([
        'status' => 'success',
        'message' => 'อัปเดตจำนวนเรียบร้อย',
        'total_cart' => cart_count()
    ]);
    exit;
}

if ($action === 'remove' && $id > 0) {
    unset($_SESSION['cart'][$id]);
    echo json_encode([
        'status' => 'success',
        'message' => 'ลบสินค้าออกจากตะกร้าแล้ว',
        'total_cart' => cart_count()
    ]);
    exit;
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    echo json_encode([
        'status' => 'success',
        'message' => 'ล้างตะกร้าเรียบร้อย',
        'total_cart' => 0
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง']);
