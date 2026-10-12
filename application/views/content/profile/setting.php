<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark"><i class="fas fa-user-cog mr-2"></i>Profile Setting</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?php echo base_url(($this->session->userdata('username') == 'direktur2' || $this->session->userdata('jobdesk') == 'DIREKTURCK') ? 'checker/dashboard' : 'dashboard') ?>">Home</a></li>
                                <li class="breadcrumb-item active">Profile Setting</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                    <!-- Flash messages -->
                    <?php if ($this->session->flashdata('success')) : ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-1"></i>
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('error')) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Profil Card -->
                        <div class="col-md-4">
                            <div class="card card-primary card-outline shadow-sm">
                                <div class="card-body box-profile text-center">
                                    <div class="text-center mb-3">
                                        <div class="d-inline-flex justify-content-center align-items-center bg-light rounded-circle shadow-sm" style="width: 100px; height: 100px; border: 3px solid #007bff;">
                                            <i class="fas fa-user text-primary" style="font-size: 50px;"></i>
                                        </div>
                                    </div>

                                    <h3 class="profile-username font-weight-bold mb-1"><?= htmlspecialchars($user->nm_karyawan) ?></h3>
                                    <p class="text-muted mb-2">
                                        <span class="badge badge-primary px-3 py-1 font-weight-normal"><?= htmlspecialchars($user->jobdesk ?: 'Karyawan') ?></span>
                                    </p>

                                    <ul class="list-group list-group-unbordered text-left mb-3">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-user-tag text-muted mr-2"></i>Username</span>
                                            <strong class="text-dark"><?= htmlspecialchars($user->username) ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-id-card text-muted mr-2"></i>NIK</span>
                                            <span class="text-dark"><?= htmlspecialchars($user->nik ?: '-') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-building text-muted mr-2"></i>Departemen</span>
                                            <span class="text-dark"><?= htmlspecialchars($user->departemen ?: '-') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-shield-alt text-muted mr-2"></i>Akses Level</span>
                                            <span class="badge badge-info">Level <?= htmlspecialchars($user->akses_lv ?: '-') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-toggle-on text-muted mr-2"></i>Status</span>
                                            <span class="badge badge-success"><?= htmlspecialchars($user->status_karyawan ?: 'AKTIF') ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Form Ganti Password & Pengaturan -->
                        <div class="col-md-8">
                            <!-- Card Ganti Password -->
                            <div class="card card-outline card-warning shadow-sm mb-4">
                                <div class="card-header">
                                    <h3 class="card-title font-weight-bold">
                                        <i class="fas fa-key mr-2 text-warning"></i>Ganti Password
                                    </h3>
                                </div>
                                <form action="<?= base_url('profile/update_password') ?>" method="POST" id="formGantiPassword">
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Gunakan kombinasi password yang kuat untuk menjaga keamanan akun Anda. Password baru minimal 6 karakter.
                                        </p>

                                        <div class="form-group">
                                            <label for="password_lama">Password Saat Ini <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                </div>
                                                <input type="password" name="password_lama" id="password_lama" class="form-control" placeholder="Masukkan password lama" required autocomplete="current-password">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="password_lama">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="password_baru">Password Baru <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                </div>
                                                <input type="password" name="password_baru" id="password_baru" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required autocomplete="new-password">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="password_baru">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small id="passStrengthHelp" class="form-text text-muted">Password minimal 6 karakter.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="konfirmasi_password">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                                </div>
                                                <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="form-control" placeholder="Ulangi password baru" minlength="6" required autocomplete="new-password">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="konfirmasi_password">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small id="matchHelp" class="form-text text-danger d-none"><i class="fas fa-times-circle mr-1"></i>Konfirmasi password tidak cocok!</small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light d-flex justify-content-end">
                                        <button type="submit" class="btn btn-warning font-weight-bold px-4" id="btnSubmitPassword">
                                            <i class="fas fa-save mr-1"></i> Simpan Password Baru
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Card Edit Informasi Profil (Nama) -->
                            <div class="card card-outline card-info shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title font-weight-bold">
                                        <i class="fas fa-id-card-alt mr-2 text-info"></i>Ubah Nama Tampilan
                                    </h3>
                                </div>
                                <form action="<?= base_url('profile/update_info') ?>" method="POST">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="nm_karyawan">Nama Lengkap / Tampilan</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                </div>
                                                <input type="text" name="nm_karyawan" id="nm_karyawan" class="form-control" value="<?= htmlspecialchars($user->nm_karyawan) ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light d-flex justify-content-end">
                                        <button type="submit" class="btn btn-info px-4">
                                            <i class="fas fa-check mr-1"></i> Simpan Perubahan Nama
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>
    </div>

    <script>
        $(document).ready(function() {
            // Toggle visibility password
            $('.toggle-pass').on('click', function() {
                var targetId = $(this).data('target');
                var input = $('#' + targetId);
                var icon = $(this).find('i');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Validasi kecocokan konfirmasi password secara real-time
            $('#konfirmasi_password, #password_baru').on('keyup', function() {
                var passBaru = $('#password_baru').val();
                var passKonfirmasi = $('#konfirmasi_password').val();

                if (passKonfirmasi.length > 0 && passBaru !== passKonfirmasi) {
                    $('#matchHelp').removeClass('d-none');
                    $('#btnSubmitPassword').prop('disabled', true);
                } else {
                    $('#matchHelp').addClass('d-none');
                    $('#btnSubmitPassword').prop('disabled', false);
                }
            });
        });
    </script>
