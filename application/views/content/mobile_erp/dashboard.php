<section class="section-stack">
    <div class="mobile-card">
        <div class="card-title-row">
            <div>
                <span class="app-eyebrow">Hari ini</span>
                <h2>Ringkasan Operasional</h2>
            </div>
            <span class="status-badge status-progress">Live</span>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon"><i class="fas fa-clipboard-check"></i></span>
                <small>Laporan Masuk</small>
                <strong><?= intval($stats['reports'] ?? 24) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-icon"><i class="fas fa-clock"></i></span>
                <small>Pending</small>
                <strong><?= intval($stats['pending'] ?? 7) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-icon"><i class="fas fa-route"></i></span>
                <small>Area Aktif</small>
                <strong><?= intval($stats['areas'] ?? 12) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-icon"><i class="fas fa-circle-check"></i></span>
                <small>Selesai</small>
                <strong><?= intval($stats['done'] ?? 18) ?></strong>
            </div>
        </div>
    </div>

    <div class="mobile-card">
        <div class="card-title-row">
            <h2>Aktivitas Cepat</h2>
            <a href="<?= site_url('mobile-erp/list') ?>" class="text-decoration-none fw-bold">Lihat</a>
        </div>
        <div class="d-grid gap-2">
            <a href="<?= site_url('penilaian_lingkungan') ?>" class="btn btn-primary mobile-btn">
                <i class="fas fa-plus me-2"></i>Buat Laporan
            </a>
            <button class="btn btn-light mobile-btn js-demo-submit" type="button">
                <i class="fas fa-download me-2"></i>Sinkron Data Dummy
            </button>
        </div>
    </div>

    <div class="mobile-card">
        <div class="card-title-row">
            <h2>Prioritas Area</h2>
            <span class="status-badge status-open">Butuh tindak lanjut</span>
        </div>
        <?php foreach (($priority_items ?? []) as $item) : ?>
            <div class="data-card border-bottom pb-3 mb-3">
                <div class="data-card-head">
                    <div>
                        <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="meta-row"><span><?= htmlspecialchars($item['time'], ENT_QUOTES, 'UTF-8') ?></span></div>
                    </div>
                    <span class="status-badge <?= $item['class'] ?>"><?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<a href="<?= site_url('penilaian_lingkungan') ?>" class="fab" aria-label="Tambah data">
    <i class="fas fa-plus"></i>
</a>
