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
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'}
            });
            if (!response.ok) throw new Error('Login belum dapat diproses. Coba kembali.');
            if (response.redirected && new URL(response.url).pathname !== new URL(form.action).pathname) {
                window.location.assign(response.url);
                return;
            }
            window.location.reload();
        } catch (error) {
            message.textContent = error.message || 'Koneksi terganggu. Silakan coba kembali.';
            message.style.display = 'block';
            submit.classList.remove('is-loading');
            submit.querySelector('span').textContent = 'Masuk ke Portal';
        }
    });
})();
</script>
</body>
</html>
