<style>
.pad-page{--pad-ink:#17233a;--pad-muted:#6f7d91;color:var(--pad-ink)}
.pad-hero{background:linear-gradient(120deg,#10283d,#145d69 65%,#1c8d83);border-radius:18px;color:#fff;padding:24px;box-shadow:0 16px 34px rgba(16,40,61,.16)}
.pad-panel{background:#fff;border:1px solid #e5eaf2;border-radius:15px;box-shadow:0 8px 24px rgba(19,35,59,.05)}
.pad-muted{color:var(--pad-muted)}.pad-url{overflow-wrap:anywhere;max-width:320px}.pad-icon{width:40px;height:40px;border-radius:12px;display:inline-grid;place-items:center;background:#f1f5f9;font-size:18px}
.pad-table th{white-space:nowrap}.pad-table td{vertical-align:middle}.pad-table .pad-description{max-width:270px;white-space:normal}
@media(max-width:767.98px){.pad-hero{padding:20px}.pad-table{min-width:850px}}
</style>
<section class="content p-3 p-md-4 pad-page">
    <div class="pad-hero mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div><div class="small text-uppercase font-weight-bold mb-1" style="letter-spacing:.12em;opacity:.72">Pengaturan portal</div><h2 class="font-weight-bold mb-1">Application Directory</h2><div style="opacity:.8">Ubah judul, tautan, dan informasi yang ditampilkan pada kartu aplikasi.</div></div>
        <a href="<?= base_url('portal') ?>" class="btn btn-outline-light font-weight-bold mt-3 mt-md-0"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Portal</a>
    </div>
    <?php if (!$database_ready): ?>
        <div class="alert alert-warning"><strong>Penyimpanan belum disiapkan.</strong> Jalankan <code>docs/database/portal_app_directory_20261029.sql</code> pada database aplikasi untuk mengaktifkan penyimpanan perubahan.</div>
    <?php endif ?>
    <div class="pad-panel p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
            <div><h5 class="font-weight-bold mb-1">Daftar aplikasi</h5><div class="small pad-muted">Aplikasi bertanda tersembunyi tetap terdaftar, tetapi kartunya tidak muncul di portal pengguna.</div></div>
            <span class="badge badge-light mt-2 mt-md-0"><?= count($apps) ?> aplikasi</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover pad-table mb-0">
                <thead class="thead-light"><tr><th>Judul</th><th>URL</th><th>Deskripsi</th><th>Tampilan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($apps as $code => $app): ?>
                        <tr>
                            <td><strong><?= html_escape($app['name']) ?></strong><div class="small pad-muted"><code><?= html_escape($code) ?></code></div></td>
                            <td class="small pad-url"><a href="<?= html_escape($app['url']) ?>" target="_blank" rel="noopener noreferrer"><?= html_escape($app['url']) ?></a></td>
                            <td class="small pad-muted pad-description"><?= html_escape($app['description'] ?? '') ?></td>
                            <td><span class="pad-icon mr-2" style="color:<?= html_escape($app['accent']) ?>"><i class="fas <?= html_escape($app['icon']) ?>"></i></span><code><?= html_escape($app['accent']) ?></code></td>
                            <td><?= !empty($app['hidden']) ? '<span class="badge badge-secondary">Tersembunyi</span>' : '<span class="badge badge-success">Tampil</span>' ?></td>
                            <td><button type="button" class="btn btn-sm btn-outline-primary pad-edit"
                                data-code="<?= html_escape($code) ?>"
                                data-name="<?= html_escape($app['name']) ?>"
                                data-url="<?= html_escape($app['url']) ?>"
                                data-description="<?= html_escape($app['description'] ?? '') ?>"
                                data-icon="<?= html_escape($app['icon']) ?>"
                                data-accent="<?= html_escape($app['accent']) ?>"
                                data-hidden="<?= !empty($app['hidden']) ? '1' : '0' ?>">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="pad-editor" tabindex="-1" role="dialog" aria-labelledby="pad-editor-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="pad-form">
                <div class="modal-header"><div><h5 class="modal-title font-weight-bold" id="pad-editor-title">Edit aplikasi</h5><div class="small pad-muted" id="pad-app-code"></div></div><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body">
                    <input type="hidden" name="app_code" id="pad-code">
                    <input type="hidden" name="form_token" id="pad-token" value="<?= html_escape($form_token) ?>">
                    <div class="form-group"><label for="pad-name">Judul aplikasi</label><input class="form-control" type="text" name="app_name" id="pad-name" maxlength="150" required></div>
                    <div class="form-group"><label for="pad-url">URL aplikasi</label><input class="form-control" type="url" name="app_url" id="pad-url" maxlength="500" placeholder="https://aplikasi.karismaerp.com" required><small class="form-text text-muted">Gunakan alamat HTTPS penuh.</small></div>
                    <div class="form-group"><label for="pad-description">Deskripsi kartu</label><textarea class="form-control" name="description" id="pad-description" rows="3" maxlength="2000"></textarea></div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label for="pad-icon">Ikon Font Awesome</label><input class="form-control" type="text" name="icon" id="pad-icon" maxlength="63" pattern="fa-[a-z0-9-]+" placeholder="fa-shopping-cart" required></div>
                        <div class="form-group col-md-6"><label for="pad-accent">Warna aksen</label><div class="input-group"><input class="form-control" type="color" name="accent" id="pad-accent" required><div class="input-group-append"><span class="input-group-text" id="pad-accent-value"></span></div></div></div>
                    </div>
                    <div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" name="is_hidden" value="1" id="pad-hidden"><label class="custom-control-label" for="pad-hidden">Sembunyikan kartu dari pengguna</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="pad-save"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    const saveUrl = '<?= base_url('admin/portal-app-directory/save') ?>';

    $('.pad-edit').on('click', function () {
        const app = this.dataset;
        $('#pad-code').val(app.code);
        $('#pad-app-code').text(app.code);
        $('#pad-name').val(app.name);
        $('#pad-url').val(app.url);
        $('#pad-description').val(app.description);
        $('#pad-icon').val(app.icon);
        $('#pad-accent').val(app.accent);
        $('#pad-accent-value').text(app.accent);
        $('#pad-hidden').prop('checked', app.hidden === '1');
        $('#pad-editor').modal('show');
    });

    $('#pad-accent').on('input', function () { $('#pad-accent-value').text(this.value); });

    $('#pad-form').on('submit', function (event) {
        event.preventDefault();
        const button = $('#pad-save');
        button.prop('disabled', true);
        $.ajax({url: saveUrl, type: 'POST', data: $(this).serialize(), dataType: 'json'})
            .done(function (response) {
                if (response.data && response.data.form_token) $('#pad-token').val(response.data.form_token);
                if (!response.status) {
                    Swal.fire({icon: 'error', title: 'Belum berhasil', text: response.message});
                    return;
                }
                $('#pad-editor').modal('hide');
                Swal.fire({icon: 'success', title: 'Tersimpan', text: response.message, timer: 1100, showConfirmButton: false})
                    .then(function () { window.location.reload(); });
            })
            .fail(function (xhr) {
                Swal.fire({icon: 'error', title: 'Gagal menyimpan', text: xhr.status === 403 ? 'Sesi formulir kedaluwarsa. Muat ulang halaman.' : 'Perubahan gagal disimpan. Periksa koneksi dan status database.'});
            })
            .always(function () { button.prop('disabled', false); });
    });
});
</script>
