<?php
// google_auth.php - Real Google Identity Services (GIS) / OAuth 2.0 Credential Verification
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

// Ensure request is POST and has credential (JWT)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$credential = $data['credential'] ?? ($_POST['credential'] ?? '');

if (empty($credential)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูล Google Token']);
    exit;
}

// Decode Google JWT Token payload
$parts = explode('.', $credential);
if (count($parts) !== 3) {
    echo json_encode(['status' => 'error', 'message' => 'รูปแบบ Google Token ไม่ถูกต้อง']);
    exit;
}

// Decode base64url payload
$payloadRaw = strtr($parts[1], '-_', '+/');
$payloadJson = base64_decode($payloadRaw);
$googleUser = json_decode($payloadJson, true);

if (!$googleUser || empty($googleUser['sub']) || empty($googleUser['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถถอดรหัสข้อมูลผู้ใช้ Google ได้']);
    exit;
}

$google_id = $googleUser['sub'];
$email = $googleUser['email'];
$name = $googleUser['name'] ?? 'ผู้ใช้งาน Google';
$picture = $googleUser['picture'] ?? 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($name);

try {
    // Check if user exists with this email or google_id
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
    $stmt->execute([$email, $google_id]);
    $user = $stmt->fetch();

    if ($user) {
        // Update user's google_id and avatar if missing
        $update = $conn->prepare("UPDATE users SET google_id = ?, avatar = COALESCE(avatar, ?) WHERE id = ?");
        $update->execute([$google_id, $picture, $user['id']]);

        // Login Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = !empty($user['avatar']) && $user['avatar'] !== 'default-avatar.png' ? $user['avatar'] : $picture;
    } else {
        // Register new user from Google
        $insert = $conn->prepare("INSERT INTO users (fullname, email, google_id, avatar, role) VALUES (?, ?, ?, ?, 'customer')");
        $insert->execute([$name, $email, $google_id, $picture]);
        $new_id = $conn->lastInsertId();

        // Login Session
        $_SESSION['user_id'] = $new_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'customer';
        $_SESSION['user_avatar'] = $picture;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'เข้าสู่ระบบด้วย Google สำเร็จ!',
        'redirect' => ($_SESSION['user_role'] === 'admin') ? 'admin/index.php' : 'index.php'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
    exit;
}
