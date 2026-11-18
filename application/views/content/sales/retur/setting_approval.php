<!-- views/content/sales/retur/setting_approval.php -->
<style>
    .approval-setting-card {
        border-radius: 8px;
    }
    .approval-step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 28px;
        padding: 0 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
        background: #e2e8f0;
        color: #1e293b;
    }
    .approval-group-row td {
        background: #f1f5f9 !important;
        font-weight: 700;
        color: #0f172a;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .approval-default-note {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #006fe6;
        color: #fff;
        font-size: 12px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 4px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-user-cog mr-2 text-primary"></i> Setting Approval Retur Penjualan
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Setting Approval</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Cara Kerja Setting Approval Dinamis:</strong>
                    <ul class="mb-0 mt-1 pl-3">
                        <li>Pilih satu atau beberapa karyawan untuk setiap tahap approval. Jika ada karyawan resign atau mutasi, cukup ubah di halaman ini tanpa perlu mengubah kode program.</li>
                        <li>Jika suatu tahap <strong>sudah diisi user</strong>, maka <strong>hanya user tersebut</strong> (ditambah Admin) yang berhak melakukan approval pada tahap itu.</li>
                        <li>Jika suatu tahap <strong>dibiarkan kosong</strong>, sistem otomatis menggunakan aturan default berdasarkan <em>Jobdesk/Bagian</em>.</li>
                        <li>User yang dipilih pada halaman ini otomatis mendapat izin akses masuk ke modul Retur Penjualan.</li>
                    </ul>
                </div>

                <form action="<?= base_url('retur_penjualan/setting_approval/save') ?>" method="post" id="formSettingApproval">
                    <div class="card card-primary card-outline approval-setting-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-sitemap mr-1"></i> Konfigurasi PIC / Approver Per Tahap
                            </h3>
                            <div class="ml-auto">
                                <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-secondary btn-sm mr-1">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar SPR
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width: 70px;" class="text-center">Tahap</th>
                                            <th style="width: 30%;">Peran / Tahap Approval</th>
                                            <th style="width: 48%;">Karyawan / User yang Bertugas</th>
                                            <th style="width: 15%;" class="text-center">Status Mode</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $lastGroup = '';
                                        foreach (($role_definitions ?? []) as $roleKey => $def):
                                            $currentEntries = $settings_map[$roleKey] ?? [];
                                            $selectedIds    = array_map('intval', array_column($currentEntries, 'user_id'));
                                            $isCustom       = !empty($selectedIds);

                                            if ($def['group'] !== $lastGroup):
                                                $lastGroup = $def['group'];
                                        ?>
                                            <tr class="approval-group-row">
                                                <td colspan="4">
                                                    <i class="fas fa-layer-group mr-1 text-primary"></i> <?= htmlspecialchars($lastGroup) ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <span class="approval-step-badge"><?= htmlspecialchars($def['step']) ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="font-weight-bold text-dark"><?= htmlspecialchars($def['label']) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($def['description']) ?></div>
                                                </td>
                                                <td class="align-middle">
                                                    <select name="roles[<?= htmlspecialchars($roleKey) ?>][]"
                                                            class="form-control select2-approver"
                                                            multiple="multiple"
                                                            data-placeholder="-- Kosongkan untuk gunakan default jobdesk --"
                                                            style="width: 100%;">
                                                        <?php foreach (($active_users ?? []) as $u):
                                                            $uid   = (int)$u['id'];
                                                            $label = trim((string)($u['nama_lngkp'] ?: $u['username']))
                                                                   . ' (' . $u['username'] . ')'
                                                                   . (!empty($u['bagian']) ? ' — ' . $u['bagian'] : '');
                                                        ?>
                                                            <option value="<?= $uid ?>" <?= in_array($uid, $selectedIds, true) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($label) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="approval-default-note">
                                                        <i class="fas fa-shield-alt mr-1"></i> Default jika kosong: <?= htmlspecialchars($def['default']) ?>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if ($isCustom): ?>
                                                        <span class="badge badge-primary px-2 py-1">
                                                            <i class="fas fa-user-check mr-1"></i> Custom (<?= count($selectedIds) ?> user)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary px-2 py-1">
                                                            <i class="fas fa-cog mr-1"></i> Default Jobdesk
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-lock mr-1"></i> User dengan level Admin selalu memiliki akses penuh pada seluruh tahap.
                            </small>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Semua Pengaturan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('.select2-approver').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            closeOnSelect: false
        });
    }
});
</script>

