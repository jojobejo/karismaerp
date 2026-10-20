<?php
$loginError = $this->session->flashdata('gagal');
$loginNotice = $this->session->flashdata('logout') ?: $this->session->flashdata('edited');
?>
<main class="login-shell">
    <section class="login-visual" aria-label="KARISMA ERP">
        <div class="visual-grid"></div>
        <div class="visual-top"><img src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo Karisma"><span>ENTERPRISE PLATFORM</span></div>
        <div class="visual-copy"><div class="eyebrow"><i class="fas fa-circle"></i> EKOSISTEM DIGITAL TERINTEGRASI</div><h1>Satu gerbang.<br><span>Seluruh operasional.</span></h1><p>Akses perangkat kerja KARISMA ERP dalam satu platform yang aman, cepat, dan terhubung.</p></div>
        <div class="visual-orbit orbit-one"></div><div class="visual-orbit orbit-two"></div>
        <div class="visual-footer"><span>SECURE ACCESS</span><span>•</span><span>BUILT FOR KARISMA</span></div>
    </section>
    <section class="login-panel">
        <div class="mobile-brand"><img src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo Karisma"></div>
        <div class="form-wrap">
            <div class="form-kicker">SELAMAT DATANG KEMBALI</div><h2>Masuk ke akun Anda</h2><p class="form-intro">Gunakan akun perusahaan untuk melanjutkan ke portal.</p>
            <?php if ($loginError): ?><div class="login-alert error" role="alert"><i class="fas fa-exclamation-circle"></i><?= html_escape($loginError) ?></div><?php endif ?>
            <?php if ($loginNotice): ?><div class="login-alert success" role="status"><i class="fas fa-check-circle"></i><?= html_escape($loginNotice) ?></div><?php endif ?>
            <form id="loginForm" action="<?= base_url('process') ?>" method="post" autocomplete="on">
                <?php if (config_item('csrf_protection')): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><?php endif ?>
                <label for="user_isi">Username</label><div class="field"><i class="far fa-user"></i><input type="text" id="user_isi" name="user_isi" placeholder="Masukkan username" autocomplete="username" required autofocus></div>
                <label for="pass_isi">Password</label><div class="field"><i class="fas fa-lock"></i><input type="password" id="pass_isi" name="pass_isi" placeholder="Masukkan password" autocomplete="current-password" required><button type="button" id="togglePassword" aria-label="Tampilkan password"><i class="far fa-eye"></i></button></div>
                <button class="submit-button" type="submit"><span>Masuk ke Portal</span><i class="fas fa-arrow-right"></i><span class="spinner" aria-hidden="true"></span></button>
                <div class="ajax-message" id="ajaxMessage" role="alert"></div>
            </form>
            <div class="secure-note"><i class="fas fa-shield-alt"></i><span>Koneksi aman untuk akun perusahaan Anda.</span></div>
        </div>
        <footer>© <?= date('Y') ?> PT Karisma Indoagro Universal</footer>
    </section>
</main>
