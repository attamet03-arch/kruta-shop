<?php
require_once 'header.php';

// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold font-mali"><i class="bi bi-people text-success me-2"></i> จัดการสมาชิกและผู้ดูแลระบบ</h1>
</div>

<div class="card admin-card p-4 bg-white">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>โปรไฟล์</th>
                    <th>ชื่อ - สกุล</th>
                    <th>อีเมล</th>
                    <th>เบอร์โทร</th>
                    <th>สิทธิ์ (Role)</th>
                    <th>วันที่สมัคร</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($u['avatar'] ?? 'https://api.dicebear.com/7.x/bottts/svg?seed=Kruta') ?>" class="rounded-circle shadow-sm" width="40" height="40">
                        </td>
                        <td><b class="font-mali"><?= htmlspecialchars($u['fullname']) ?></b></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'primary' ?> rounded-pill px-3 py-1">
                                <?= strtoupper($u['role']) ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
