<?php
$page_title = "เข้าสู่ระบบ - Kruta Shop";
require_once 'config.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "กรุณากรอกอีเมลและรหัสผ่านให้ครบถ้วน";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_avatar'] = $user['avatar'];

                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
            }
        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}

// Fetch Google Client ID from settings
$google_client_id = get_setting('google_client_id', '');

require_once 'header.php';
?>

<!-- Official Google Identity Services SDK -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5" data-aos="zoom-in">
            <div class="card border-0 rounded-5 shadow-lg p-4 p-md-5 bg-white">
                <div class="text-center mb-4">
                    <span class="jump-infinite display-4">✨🔑</span>
                    <h3 class="fw-bold font-mali mt-2" style="color: #FF6584;">เข้าสู่ระบบสมาชิก</h3>
                    <p class="text-muted small">ยินดีต้อนรับสู่โลกเครื่องเขียนสุดคิ้วท์ Kruta Shop</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-4 text-center fw-bold small py-2">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Real Google Sign-In Container -->
                <div class="text-center mb-3">
                    <?php if (!empty($google_client_id)): ?>
                        <!-- Real Google Official One-Tap & Button -->
                        <div id="g_id_onload"
                             data-client_id="<?= htmlspecialchars($google_client_id) ?>"
                             data-callback="handleGoogleCredentialResponse"
                             data-auto_prompt="false">
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="g_id_signin" 
                                 data-type="standard" 
                                 data-shape="pill" 
                                 data-theme="outline" 
                                 data-text="signin_with" 
                                 data-size="large" 
                                 data-logo_alignment="left"
                                 data-width="320">
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Prompt Admin to enter Client ID or Quick-click to configure -->
                        <button type="button" onclick="promptGoogleConfig()" class="btn btn-outline-dark w-100 rounded-pill py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm bounce-hover fw-bold">
                            <svg width="20" height="20" viewBox="0 0 48 48">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            </svg>
                            เข้าสู่ระบบด้วย Google (จริง)
                        </button>
                        <div class="mt-2 text-center">
                            <a href="admin/google_settings.php" class="small text-danger text-decoration-none">
                                <i class="bi bi-gear-fill"></i> ใส่ Google Client ID ของคุณที่นี่
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="position-relative text-center my-3">
                    <hr class="text-muted">
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">หรือใช้อีเมล</span>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">อีเมล (Email)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-envelope-fill text-muted"></i></span>
                            <input type="email" name="email" class="form-control rounded-end-pill border-light bg-light" placeholder="admin@kruta.com หรือ student@kruta.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">รหัสผ่าน (Password)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-key-fill text-muted"></i></span>
                            <input type="password" name="password" class="form-control rounded-end-pill border-light bg-light" placeholder="รหัสผ่าน (ค่าเริ่มต้น: 123456)" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-kruta-primary w-100 py-2 rounded-pill mt-3 fw-bold">
                        เข้าสู่ระบบเลย 🚀
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <p class="small text-muted mb-1">ยังไม่มีบัญชีสมาชิก?</p>
                    <a href="register.php" class="fw-bold text-decoration-none" style="color: #6C5CE7;">
                        สมัครสมาชิกใหม่คลิกที่นี่ 🎉
                    </a>
                </div>

                <div class="alert alert-light border small text-muted mt-3 mb-0">
                    💡 <b>บัญชีทดสอบระบบ:</b><br>
                    • ผู้ดูแลระบบ: <code>admin@kruta.com</code> / <code>123456</code><br>
                    • ลูกค้าทั่วไป: <code>student@kruta.com</code> / <code>123456</code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Callback function when user completes Google Sign-In popup
function handleGoogleCredentialResponse(response) {
    Swal.fire({
        title: 'กำลังตรวจสอบบัญชี Google...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('google_auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ credential: response.credential })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'เข้าสู่ระบบสำเร็จ!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: data.message
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'เชื่อมต่อล้มเหลว',
            text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้'
        });
    });
}

function promptGoogleConfig() {
    Swal.fire({
        title: 'ต้องการเชื่อมต่อ Google จริงใช่ไหมครับ?',
        html: `การเชื่อมต่อกับ Google จริง จำเป็นต้องใส่ <b>Google Client ID</b> ของคุณ<br><br>
               คุณมี Google Client ID จาก Google Cloud Console หรือยังครับ?`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'ไปหน้าใส่ Client ID (Admin)',
        cancelButtonText: 'ปิดหน้าต่างนี้',
        confirmButtonColor: '#FF6584'
    }).then((res) => {
        if (res.isConfirmed) {
            window.location.href = 'admin/google_settings.php';
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>
