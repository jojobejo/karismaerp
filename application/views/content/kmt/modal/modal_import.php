

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="modalImportLabel">
                    <i class="fas fa-file-import mr-1"></i>
                    <?= $import_title ?? 'Import Data dari Excel' ?>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form action="<?= $import_url ?>" method="POST" enctype="multipart/form-data" id="formImport">
              

                <div class="modal-body">

                    <!-- Panduan singkat -->
                    <div class="alert alert-info alert-dismissible mb-3 p-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Panduan:</strong>
                        <ol class="mb-0 mt-1 pl-3" style="font-size:0.85rem;">
                            <li>Download template Excel terlebih dahulu.</li>
                            <li>Isi data mulai <strong>baris ke-3</strong> (jangan ubah baris 1 & 2).</li>
                            <li>Simpan file lalu upload di sini.</li>
                            <?php if (!empty($import_note)): ?>
                            <li><?= $import_note ?></li>
                            <?php endif; ?>
                        </ol>
                    </div>

                    <!-- Tombol Download Template -->
                    <div class="mb-3">
                        <a href="<?= $template_url ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i> Download Template Excel
                        </a>
                    </div>

                    <!-- Input File -->
                    <div class="form-group">
                        <label for="file_excel">
                            <i class="fas fa-upload mr-1"></i> Pilih File Excel
                            <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file"
                                   class="custom-file-input"
                                   id="file_excel"
                                   name="file_excel"
                                   accept=".xlsx,.xls"
                                   required>
                            <label class="custom-file-label" for="file_excel">
                                Pilih file .xlsx / .xls ...
                            </label>
                        </div>
                        <small class="form-text text-muted">Format yang didukung: .xlsx, .xls</small>
                    </div>

                    <!-- Preview nama file yang dipilih -->
                    <div id="fileInfo" class="d-none">
                        <div class="alert alert-secondary p-2 mb-0">
                            <i class="fas fa-file-excel text-success mr-1"></i>
                            <span id="fileName"></span>
                            <span class="text-muted ml-2" id="fileSize"></span>
                        </div>
                    </div>

                </div><!-- /.modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnImport">
                        <i class="fas fa-file-import mr-1"></i> Import Sekarang
                    </button>
                </div>

            </form><!-- /form -->

        </div>
    </div>
</div>
<!-- /Modal Import Excel -->

<script>
$(function () {
    // Update label custom-file-input dan tampilkan info file
    $('#file_excel').on('change', function () {
        var file = this.files[0];
        if (file) {
            $(this).next('.custom-file-label').text(file.name);
            $('#fileName').text(file.name);
            var kb = (file.size / 1024).toFixed(1);
            var size = kb > 1024 ? (kb / 1024).toFixed(2) + ' MB' : kb + ' KB';
            $('#fileSize').text('(' + size + ')');
            $('#fileInfo').removeClass('d-none');
        } else {
            $(this).next('.custom-file-label').text('Pilih file .xlsx / .xls ...');
            $('#fileInfo').addClass('d-none');
        }
    });

    // Konfirmasi sebelum submit
    $('#formImport').on('submit', function (e) {
        if (!$('#file_excel').val()) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'File belum dipilih', text: 'Silakan pilih file Excel terlebih dahulu.' });
            return;
        }
        // Tampilkan loading di tombol
        $('#btnImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
    });
});
</script>