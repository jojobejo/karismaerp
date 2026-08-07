<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-money-bill-wave mr-2 text-success"></i> Form Pengajuan Kas Bon</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('C_Kasbon') ?>">Kas Bon</a></li>
                            <li class="breadcrumb-item active">Form</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                
                <?php if ($msg = $this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?= $msg ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Silakan isi data pengajuan Kas Bon</h3>
                    </div>
                    <form action="<?= base_url('C_Kasbon/store') ?>" method="post">
                        <div class="card-body">
                            
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Pemohon</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Pengajuan</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" value="<?= date('d-m-Y') ?>" readonly>
                                    <small class="text-muted">Nomor Kas Bon akan di-generate secara otomatis setelah disimpan.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="nominal" id="nominal" class="form-control" required placeholder="Contoh: 1.000.000">
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Keterangan / Keperluan <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <textarea name="keterangan" class="form-control" rows="4" required placeholder="Tuliskan keterangan lengkap pengajuan kas bon..."></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer text-right">
                            <a href="<?= base_url('C_Kasbon') ?>" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Format mata uang untuk input nominal
        $('#nominal').on('keyup', function(e) {
            let val = $(this).val().replace(/[^0-9]/g, '');
            if (val) {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            } else {
                $(this).val('');
            }
        });
    });
</script>
