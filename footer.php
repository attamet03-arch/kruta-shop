<!-- Footer -->
<footer class="mt-5 py-5" style="background: linear-gradient(180deg, rgba(255,255,255,0) 0%, #ffeaf1 100%); border-top: 2px dashed #fbd3e0;">
    <div class="container text-center">
        <div class="mb-3">
            <span class="jump-infinite display-4">🎒</span>
            <span class="jump-infinite display-4" style="animation-delay: 0.2s;">📐</span>
            <span class="jump-infinite display-4" style="animation-delay: 0.4s;">✂️</span>
            <span class="jump-infinite display-4" style="animation-delay: 0.6s;">🎨</span>
        </div>
        <h4 class="fw-bold mb-2 font-mali" style="color: #FF6584;">Kruta Shop - ครูต๊ะช็อป เครื่องเขียนเพื่อการเรียนรู้</h4>
        <p class="text-muted mb-3">อุปกรณ์การเรียนน่ารักจัดเต็ม สแกนจ่ายด้วย QR Code ง่ายไว จัดส่งด่วนถึงโต๊ะเรียนทุกวัน 🚚💨</p>
        
        <div class="d-flex justify-content-center gap-3 mb-4">
            <a href="admin/index.php" class="btn btn-outline-dark rounded-pill btn-sm px-3 shadow-sm">
                <i class="bi bi-lock-fill me-1"></i> เข้าสู่ระบบหลังร้าน Admin
            </a>
            <a href="install.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3 shadow-sm">
                <i class="bi bi-database-fill-gear me-1"></i> ติดตั้ง/รีเซ็ตฐานข้อมูล
            </a>
        </div>

        <p class="small text-muted mb-0">
            © <?= date('Y') ?> Kruta Shop. All rights reserved. ออกแบบสุดปังด้วย PHP + MySQL + Bootstrap 5 + JS Animation
        </p>
    </div>
</footer>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 for Gorgeous Popups -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: false,
        mirror: true
    });

    // Helper for Toast alerts
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });

    // Quick Add to Cart with Bounce animation & Confetti
    function addToCart(productId, productName) {
        fetch('cart_action.php?action=add&id=' + productId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update pill counts
                    document.querySelectorAll('.cart-count-pill').forEach(el => {
                        el.innerText = data.total_cart;
                        el.classList.add('animate__animated', 'animate__rubberBand');
                        setTimeout(() => el.classList.remove('animate__animated', 'animate__rubberBand'), 1000);
                    });

                    // Pop confetti
                    confetti({
                        particleCount: 50,
                        spread: 60,
                        origin: { y: 0.85, x: 0.9 }
                    });

                    Toast.fire({
                        icon: 'success',
                        title: 'เพิ่ม "' + productName + '" ลงตะกร้าแล้ว! 🛒🎉'
                    });
                }
            })
            .catch(err => {
                console.error(err);
            });
    }
</script>
</body>
</html>
