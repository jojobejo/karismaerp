<section class="section-stack">
    <div id="mobileIssueDetail" class="section-stack" data-id="<?= intval($issue_id ?? 0) ?>">
        <div class="skeleton"></div>
        <div class="skeleton"></div>
    </div>

    <div class="mobile-card">
        <div class="card-title-row">
            <h2>Timeline</h2>
            <span class="status-badge status-muted">Dummy</span>
        </div>
        <div class="data-card border-bottom pb-3 mb-3">
            <div class="data-card-head">
                <div>
                    <h3>Laporan dibuat</h3>
                    <div class="meta-row"><span>Otomatis dari form mobile</span></div>
                </div>
                <i class="fas fa-circle-check text-success"></i>
            </div>
        </div>
        <div class="data-card">
            <div class="data-card-head">
                <div>
                    <h3>Menunggu proses GA</h3>
                    <div class="meta-row"><span>Prioritas mengikuti rating issue</span></div>
                </div>
                <i class="far fa-clock text-warning"></i>
            </div>
        </div>
    </div>
</section>
