<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>
(() => {
    const form = document.getElementById('loginForm');
    const submit = form.querySelector('.submit-button');
    const message = document.getElementById('ajaxMessage');
    const password = document.getElementById('pass_isi');
    document.getElementById('togglePassword').addEventListener('click', function () {
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        this.innerHTML = visible ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
        this.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        submit.classList.add('is-loading');
        submit.querySelector('span').textContent = 'Memverifikasi akun…';
        message.style.display = 'none';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
            });
            if (response.redirected && new URL(response.url).pathname !== new URL(form.action).pathname) {
                window.location.assign(response.url);
                return;
            }
            const result = await response.json();
            if (!response.ok || result.success === false) {
                const alertTitle = {username: 'Username tidak ditemukan', password: 'Password salah', inactive: 'Akun tidak aktif'}[result.type] || 'Login belum berhasil';
                await Swal.fire({icon: 'error', title: alertTitle, text: result.message || 'Login belum dapat diproses. Coba kembali.', confirmButtonColor: '#2774f5'});
                submit.classList.remove('is-loading');
                submit.querySelector('span').textContent = 'Masuk ke Portal';
                return;
            }
            window.location.assign('<?= base_url('portal') ?>');
        } catch (error) {
            message.textContent = error.message || 'Koneksi terganggu. Silakan coba kembali.';
            message.style.display = 'block';
            submit.classList.remove('is-loading');
            submit.querySelector('span').textContent = 'Masuk ke Portal';
        }
    });
})();
</script>
<?php if (!empty($loginError)): $alertTitle = ['username' => 'Username tidak ditemukan', 'password' => 'Password salah', 'inactive' => 'Akun tidak aktif'][$loginErrorType ?? ''] ?? 'Login belum berhasil'; ?><script>Swal.fire({icon:'error',title:<?= json_encode($alertTitle) ?>,text:<?= json_encode($loginError, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,confirmButtonColor:'#2774f5'});</script><?php endif ?>
<?php if (!empty($loginNotice)): ?><script>Swal.fire({icon:'success',title:'Berhasil',text:<?= json_encode($loginNotice, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,confirmButtonColor:'#2774f5'});</script><?php endif ?>
</body>
</html>
