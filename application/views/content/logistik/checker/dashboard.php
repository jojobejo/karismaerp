<?php
// view/content/logistik/checker/dashboard.php
// Dashboard Aktivitas Warehouse — Real-time Overview
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Nunito:wght@400;600;700;800&display=swap');

:root {
    --bg-main:    #0b1120;
    --bg-card:    #111827;
    --bg-card2:   #1a2235;
    --border:     rgba(255,255,255,0.07);
    --accent-b:   #f59e0b;  /* bongkaran - amber */
    --accent-lk:  #3b82f6;  /* loading LK - blue */
    --accent-kk:  #10b981;  /* loading KK - green */
    --text-main:  #e2e8f0;
    --text-muted: #64748b;
    --status-menunggu: #475569;
    --status-proses:   #f59e0b;
    --status-done:     #22c55e;
    --glow-b:  0 0 20px rgba(245,158,11,0.25);
    --glow-lk: 0 0 20px rgba(59,130,246,0.25);
    --glow-kk: 0 0 20px rgba(16,185,129,0.25);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

.dash-wrapper {
    background: var(--bg-main);
    min-height: 100vh;
    font-family: 'Nunito', sans-serif;
    color: var(--text-main);
    padding: 0 0 40px 0;
}

/* ── HEADER ── */
.dash-header {
    background: linear-gradient(135deg, #0d1b2e 0%, #0f2240 60%, #0b1120 100%);
    border-bottom: 1px solid var(--border);
    padding: 18px 28px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(8px);
}
.dash-header-title {
    display: flex;
    align-items: center;
    gap: 14px;
}
.dash-header-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #1565c0, #0d47a1);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 14px rgba(21,101,192,0.5);
}
.dash-header h1 {
    font-family: 'Rajdhani', sans-serif;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #fff;
}
.dash-header-sub {
    font-size: 12px;
    color: var(--text-muted);
    font-family: 'IBM Plex Mono', monospace;
    letter-spacing: .04em;
}
.dash-header-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.dash-clock {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 20px;
    font-weight: 500;
    color: var(--accent-b);
    letter-spacing: .08em;
}
.dash-date {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    color: var(--text-muted);
    text-align: right;
}
.badge-live {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.4);
    color: #ef4444;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    animation: pulse-badge 2s infinite;
}
@keyframes pulse-badge {
    0%,100% { opacity: 1; }
    50%      { opacity: .6; }
}
.live-dot {
    width: 7px; height: 7px;
    background: #ef4444;
    border-radius: 50%;
    animation: pulse-dot 1s infinite;
}
@keyframes pulse-dot {
    0%,100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239,68,68,.6); }
    50%      { transform: scale(1.3); box-shadow: 0 0 0 4px rgba(239,68,68,0); }
}

/* ── SUMMARY STRIP ── */
.summary-strip {
    display: flex;
    gap: 12px;
    padding: 18px 28px 0;
    flex-wrap: wrap;
}
.sum-card {
    flex: 1; min-width: 130px;
    background: var(--bg-card2);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform .2s, box-shadow .2s;
}
.sum-card:hover { transform: translateY(-2px); }
.sum-card.amber { border-color: rgba(245,158,11,.35); box-shadow: 0 0 14px rgba(245,158,11,.1); }
.sum-card.blue  { border-color: rgba(59,130,246,.35); box-shadow: 0 0 14px rgba(59,130,246,.1); }
.sum-card.green { border-color: rgba(16,185,129,.35); box-shadow: 0 0 14px rgba(16,185,129,.1); }
.sum-card.red   { border-color: rgba(239,68,68,.35);  box-shadow: 0 0 14px rgba(239,68,68,.1); }
.sum-icon { font-size: 24px; line-height: 1; }
.sum-num  { font-family: 'Rajdhani', sans-serif; font-size: 28px; font-weight: 700; line-height: 1; }
.sum-label{ font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; margin-top: 2px; }
.sum-card.amber .sum-num { color: var(--accent-b); }
.sum-card.blue  .sum-num { color: #60a5fa; }
.sum-card.green .sum-num { color: #34d399; }
.sum-card.red   .sum-num { color: #f87171; }

/* ── PINTU GUDANG ── */
.warehouse-section {
    padding: 22px 28px 0;
}
.section-label {
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}
.gates-row {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.gate-card {
    flex: 1; min-width: 140px; max-width: 220px;
    border-radius: 16px;
    border: 2px solid;
    padding: 18px 14px 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
    cursor: default;
}
.gate-card:hover { transform: translateY(-4px); }
.gate-card.gate-active {
    background: linear-gradient(160deg, #1a2a10 0%, #0f2010 100%);
    border-color: #22c55e;
    box-shadow: 0 0 28px rgba(34,197,94,.3), inset 0 0 20px rgba(34,197,94,.05);
}
.gate-card.gate-busy {
    background: linear-gradient(160deg, #2a1e08 0%, #1e1505 100%);
    border-color: var(--accent-b);
    box-shadow: 0 0 28px rgba(245,158,11,.3), inset 0 0 20px rgba(245,158,11,.05);
}
.gate-card.gate-idle {
    background: linear-gradient(160deg, #131c2e 0%, #0b1120 100%);
    border-color: #2d3748;
    box-shadow: none;
}
.gate-card.gate-done {
    background: linear-gradient(160deg, #1a2235 0%, #0f172a 100%);
    border-color: #3b82f6;
    box-shadow: 0 0 20px rgba(59,130,246,.2);
}

/* Warehouse door SVG icon */
.gate-door {
    width: 64px; height: 72px;
    position: relative;
    flex-shrink: 0;
}
.gate-door svg { width: 100%; height: 100%; }

.gate-num {
    font-family: 'Rajdhani', sans-serif;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.gate-card.gate-active  .gate-num { color: #4ade80; }
.gate-card.gate-busy    .gate-num { color: #fbbf24; }
.gate-card.gate-idle    .gate-num { color: #475569; }
.gate-card.gate-done    .gate-num { color: #93c5fd; }

.gate-status-badge {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
}
.gate-card.gate-active  .gate-status-badge { background: rgba(34,197,94,.2);  color: #4ade80; border: 1px solid rgba(34,197,94,.4); }
.gate-card.gate-busy    .gate-status-badge { background: rgba(245,158,11,.2); color: #fbbf24; border: 1px solid rgba(245,158,11,.4); }
.gate-card.gate-idle    .gate-status-badge { background: rgba(71,85,105,.15); color: #64748b; border: 1px solid rgba(71,85,105,.3); }
.gate-card.gate-done    .gate-status-badge { background: rgba(59,130,246,.2);  color: #93c5fd; border: 1px solid rgba(59,130,246,.4); }

.gate-info { font-size: 11px; color: var(--text-muted); text-align: center; line-height: 1.4; }
.gate-card.gate-active  .gate-info { color: #86efac; }
.gate-card.gate-busy    .gate-info { color: #fde68a; }

/* Pulse ring for active gates */
.gate-card.gate-active::before,
.gate-card.gate-busy::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 16px;
    animation: gate-pulse 2.5s ease-in-out infinite;
    pointer-events: none;
}
.gate-card.gate-active::before { box-shadow: 0 0 0 4px rgba(34,197,94,.2); }
.gate-card.gate-busy::before   { box-shadow: 0 0 0 4px rgba(245,158,11,.2); }
@keyframes gate-pulse {
    0%,100% { opacity: 0; }
    50%      { opacity: 1; }
}

/* ── MAIN PANELS ── */
.panels-grid {
    padding: 22px 28px 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    gap: 20px;
}

.panel {
    background: var(--bg-card);
    border-radius: 18px;
    border: 1px solid var(--border);
    overflow: hidden;
}
.panel-header {
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid var(--border);
}
.panel-header-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.panel.bongkaran .panel-header-icon { background: rgba(245,158,11,.15); }
.panel.loading-lk .panel-header-icon { background: rgba(59,130,246,.15); }
.panel.loading-kk .panel-header-icon { background: rgba(16,185,129,.15); }

.panel-header-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.panel.bongkaran  .panel-header-title { color: #fbbf24; }
.panel.loading-lk .panel-header-title { color: #60a5fa; }
.panel.loading-kk .panel-header-title { color: #34d399; }

.panel-header-sub { font-size: 11px; color: var(--text-muted); }
.panel-header-count {
    margin-left: auto;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 500;
}
.panel.bongkaran  .panel-header-count { color: #fbbf24; }
.panel.loading-lk .panel-header-count { color: #60a5fa; }
.panel.loading-kk .panel-header-count { color: #34d399; }

/* Sub-sections inside panel */
.subsection {
    border-bottom: 1px solid var(--border);
}
.subsection:last-child { border-bottom: none; }

.subsection-header {
    padding: 9px 18px 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    cursor: pointer;
    user-select: none;
    transition: background .15s;
}
.subsection-header:hover { background: rgba(255,255,255,.03); }

.sub-menunggu .subsection-header { color: #94a3b8; background: rgba(71,85,105,.1); }
.sub-proses   .subsection-header { color: #fbbf24; background: rgba(245,158,11,.08); }
.sub-done     .subsection-header { color: #4ade80; background: rgba(34,197,94,.08); }

.subsection-header .sub-count {
    margin-left: auto;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    background: rgba(255,255,255,.08);
    padding: 1px 8px;
    border-radius: 10px;
}
.subsection-header .sub-toggle { font-size: 10px; margin-left: 4px; transition: transform .2s; }
.subsection-header.collapsed .sub-toggle { transform: rotate(-90deg); }

.subsection-body { padding: 8px 12px 10px; display: flex; flex-direction: column; gap: 6px; }
.subsection-body.collapsed { display: none; }

/* Item card */
.item-card {
    background: var(--bg-card2);
    border-radius: 10px;
    border: 1px solid var(--border);
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: transform .15s, border-color .15s;
    animation: fadeIn .3s ease;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
.item-card:hover { transform: translateX(3px); }

.item-card.item-menunggu { border-left: 3px solid var(--status-menunggu); }
.item-card.item-proses   { border-left: 3px solid var(--status-proses); }
.item-card.item-done     { border-left: 3px solid var(--status-done); opacity: .8; }

.item-icon { font-size: 16px; flex-shrink: 0; }
.item-body { flex: 1; min-width: 0; }
.item-title { font-size: 13px; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-meta  { font-size: 11px; color: var(--text-muted); margin-top: 2px; display: flex; gap: 10px; flex-wrap: wrap; }
.item-meta span { display: flex; align-items: center; gap: 4px; }

.item-right { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0; }

/* Progress bar */
.mini-progress { width: 80px; height: 6px; background: rgba(255,255,255,.08); border-radius: 3px; overflow: hidden; }
.mini-progress-bar { height: 100%; border-radius: 3px; transition: width .4s ease; }
.item-menunggu .mini-progress-bar { background: var(--status-menunggu); }
.item-proses   .mini-progress-bar { background: var(--status-proses); }
.item-done     .mini-progress-bar { background: var(--status-done); }

.item-progres-txt { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--text-muted); }
.item-card.item-proses .item-progres-txt { color: #fbbf24; }
.item-card.item-done   .item-progres-txt { color: #4ade80; }

.empty-state { padding: 14px 12px; text-align: center; color: var(--text-muted); font-size: 12px; font-style: italic; }

/* Duration live badge */
.dur-live { font-size: 10px; color: #fbbf24; font-weight: 700; display: flex; align-items: center; gap: 3px; }
.dur-live::before { content: ''; display: inline-block; width: 5px; height: 5px; background: #fbbf24; border-radius: 50%; animation: pulse-dot 1s infinite; }

/* Truck SVG for LK & KK */
.truck-svg { width: 22px; height: 22px; }

/* ── REFRESH BUTTON ── */
.btn-refresh {
    background: rgba(59,130,246,.15);
    border: 1px solid rgba(59,130,246,.35);
    color: #60a5fa;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    letter-spacing: .06em;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background .2s, transform .15s;
    font-family: 'Nunito', sans-serif;
}
.btn-refresh:hover { background: rgba(59,130,246,.3); transform: scale(1.03); }
.btn-refresh.spinning i { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── RESPONSIVE ── */
@media (max-width: 600px) {
    .dash-header { padding: 14px 16px; }
    .summary-strip, .warehouse-section, .panels-grid { padding-left: 14px; padding-right: 14px; }
    .panels-grid { grid-template-columns: 1fr; }
    .gate-card { min-width: 120px; }
}
</style>

<div class="dash-wrapper">

    <!-- ── HEADER ── -->
    <div class="dash-header">
        <div class="dash-header-title">
            <div class="dash-header-icon">🏭</div>
            <div>
                <h1>Warehouse Dashboard</h1>
                <div class="dash-header-sub">PT. KARISMA INDOARGO UNIVERSAL</div>
            </div>
        </div>
        <div class="dash-header-meta">
            <div class="badge-live"><span class="live-dot"></span>Live</div>
            <button class="btn-refresh" id="btnRefreshDash"><i class="fas fa-sync-alt"></i> Refresh</button>
            <div>
                <div class="dash-clock" id="dashClock">--:--:--</div>
                <div class="dash-date" id="dashDate"><?= date('l, j F Y') ?></div>
            </div>
        </div>
    </div>

    <?php
    // ── Compute stats ──
    $b_menunggu = array_filter($bongkaran, fn($r) => $r['status'] === 'MENUNGGU');
    $b_proses   = array_filter($bongkaran, fn($r) => in_array($r['status'], ['PROSES','PENYIAPAN_BARANG','CETAK_DO']));
    $b_done     = array_filter($bongkaran, fn($r) => $r['status'] === 'DONE');

    $lk_menunggu = array_filter($list_lk, fn($r) => in_array($r['status'], ['MENUNGGU','CETAK_DO']));
    $lk_proses   = array_filter($list_lk, fn($r) => $r['status'] === 'PROSES_LOADING');
    $lk_do       = array_filter($list_lk, fn($r) => $r['status'] === 'DO_SELESAI');
    $lk_done     = array_filter($list_lk, fn($r) => $r['status'] === 'DONE');

    $kk_menunggu = array_filter($list_kk, fn($r) => in_array($r['status'], ['MENUNGGU','CETAK_DO']));
    $kk_proses   = array_filter($list_kk, fn($r) => $r['status'] === 'PROSES_LOADING');
    $kk_do       = array_filter($list_kk, fn($r) => $r['status'] === 'DO_SELESAI');
    $kk_done     = array_filter($list_kk, fn($r) => $r['status'] === 'DONE');

    $total_aktif  = count($b_proses)  + count($lk_proses)  + count($kk_proses);
    $total_menunggu = count($b_menunggu) + count($lk_menunggu) + count($lk_do) + count($kk_menunggu) + count($kk_do);
    $total_done   = count($b_done) + count($lk_done) + count($kk_done);
    $total_semua  = count($bongkaran) + count($list_lk) + count($list_kk);

    // ── Build gate states (4 pintu) ──
    // Assign active bongkaran to gates round-robin; idle = empty
    $gate_states = [];
    for ($i = 1; $i <= 4; $i++) { $gate_states[$i] = ['state' => 'idle', 'info' => 'Tidak ada aktivitas', 'checker' => '']; }

    $gateIdx = 1;
    foreach ($b_proses as $b) {
        if ($gateIdx > 4) break;
        $gate_states[$gateIdx] = ['state' => 'busy', 'info' => htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']), 'checker' => htmlspecialchars($b['nm_checker'] ?? '')];
        $gateIdx++;
    }
    foreach ($b_done as $b) {
        if ($gateIdx > 4) break;
        $gate_states[$gateIdx] = ['state' => 'done', 'info' => htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']), 'checker' => htmlspecialchars($b['nm_checker'] ?? '')];
        $gateIdx++;
    }
    ?>

    <!-- ── SUMMARY STRIP ── -->
    <div class="summary-strip">
        <div class="sum-card amber">
            <div class="sum-icon">⚡</div>
            <div>
                <div class="sum-num"><?= $total_aktif ?></div>
                <div class="sum-label">Sedang Proses</div>
            </div>
        </div>
        <div class="sum-card red">
            <div class="sum-icon">⏳</div>
            <div>
                <div class="sum-num"><?= $total_menunggu ?></div>
                <div class="sum-label">Menunggu</div>
            </div>
        </div>
        <div class="sum-card green">
            <div class="sum-icon">✅</div>
            <div>
                <div class="sum-num"><?= $total_done ?></div>
                <div class="sum-label">Selesai</div>
            </div>
        </div>
        <div class="sum-card blue">
            <div class="sum-icon">📋</div>
            <div>
                <div class="sum-num"><?= $total_semua ?></div>
                <div class="sum-label">Total Aktivitas</div>
            </div>
        </div>
    </div>

    <!-- ── PINTU GUDANG ── -->
    <div class="warehouse-section">
        <div class="section-label">
            <i class="fas fa-warehouse"></i> Pintu Gudang (<?= count($b_proses) + count($b_done) ?>/4 Aktif)
        </div>
        <div class="gates-row">
            <?php for ($i = 1; $i <= 4; $i++) :
                $g = $gate_states[$i];
                $cls_map = ['idle' => 'gate-idle', 'busy' => 'gate-busy', 'done' => 'gate-done', 'active' => 'gate-active'];
                $state_cls = $cls_map[$g['state']] ?? 'gate-idle';
                $label_map = ['idle' => 'Kosong', 'busy' => 'Bongkar Aktif', 'done' => 'Selesai', 'active' => 'Aktif'];
                $state_label = $label_map[$g['state']] ?? 'Kosong';

                // Door color by state
                $door_fill   = ['idle' => '#2d3748', 'busy' => '#78350f', 'done' => '#1e3a5f', 'active' => '#14532d'][$g['state']] ?? '#2d3748';
                $panel_fill  = ['idle' => '#374151', 'busy' => '#92400e', 'done' => '#1e40af', 'active' => '#166534'][$g['state']] ?? '#374151';
                $stripe_col  = ['idle' => '#4b5563', 'busy' => '#d97706', 'done' => '#2563eb', 'active' => '#16a34a'][$g['state']] ?? '#4b5563';
                $light_col   = ['idle' => '#6b7280', 'busy' => '#fbbf24', 'done' => '#60a5fa', 'active' => '#4ade80'][$g['state']] ?? '#6b7280';
            ?>
            <div class="gate-card <?= $state_cls ?>">
                <!-- Warehouse Door SVG -->
                <div class="gate-door">
                    <svg viewBox="0 0 64 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Door frame -->
                        <rect x="2" y="2" width="60" height="70" rx="4" fill="#1a2235" stroke="<?= $stripe_col ?>" stroke-width="2.5"/>
                        <!-- Door panels left -->
                        <rect x="6" y="6"  width="24" height="60" rx="2" fill="<?= $door_fill ?>"/>
                        <rect x="8" y="9"  width="20" height="26" rx="1.5" fill="<?= $panel_fill ?>" opacity=".8"/>
                        <rect x="8" y="38" width="20" height="26" rx="1.5" fill="<?= $panel_fill ?>" opacity=".8"/>
                        <!-- Door panels right -->
                        <rect x="34" y="6"  width="24" height="60" rx="2" fill="<?= $door_fill ?>"/>
                        <rect x="36" y="9"  width="20" height="26" rx="1.5" fill="<?= $panel_fill ?>" opacity=".8"/>
                        <rect x="36" y="38" width="20" height="26" rx="1.5" fill="<?= $panel_fill ?>" opacity=".8"/>
                        <!-- Center gap -->
                        <rect x="30" y="2" width="4" height="70" fill="#0b1120"/>
                        <!-- Stripe warnings -->
                        <?php if ($g['state'] === 'busy') : ?>
                        <rect x="2" y="62" width="60" height="8" rx="0" fill="#1a1000"/>
                        <rect x="2"  y="62" width="10" height="8" fill="#d97706"/>
                        <rect x="22" y="62" width="10" height="8" fill="#d97706"/>
                        <rect x="42" y="62" width="10" height="8" fill="#d97706"/>
                        <?php endif; ?>
                        <!-- Status light -->
                        <circle cx="32" cy="4" r="3.5" fill="<?= $light_col ?>" opacity=".9"/>
                        <!-- Handle left -->
                        <rect x="27" y="34" width="2.5" height="7" rx="1.25" fill="<?= $stripe_col ?>"/>
                        <!-- Handle right -->
                        <rect x="34.5" y="34" width="2.5" height="7" rx="1.25" fill="<?= $stripe_col ?>"/>
                        <?php if ($g['state'] === 'done') : ?>
                        <!-- Checkmark overlay -->
                        <circle cx="32" cy="36" r="10" fill="rgba(34,197,94,0.15)" stroke="#22c55e" stroke-width="1.5"/>
                        <path d="M27 36 L31 40 L37 32" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <?php endif; ?>
                    </svg>
                </div>
                <div class="gate-num">Pintu <?= $i ?></div>
                <div class="gate-status-badge"><?= $state_label ?></div>
                <?php if (!empty($g['info']) && $g['state'] !== 'idle') : ?>
                <div class="gate-info"><?= $g['info'] ?><?= $g['checker'] ? '<br><span style="opacity:.7">👷 '.$g['checker'].'</span>' : '' ?></div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- ── MAIN PANELS ── -->
    <div class="panels-grid">

        <!-- ════ BONGKARAN ════ -->
        <div class="panel bongkaran">
            <div class="panel-header">
                <div class="panel-header-icon">
                    <!-- Forklift icon -->
                    <svg viewBox="0 0 32 32" fill="none" width="22" height="22" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 22 L10 22 L10 10 L18 10 L22 22" stroke="#fbbf24" stroke-width="2" stroke-linejoin="round" fill="none"/>
                        <rect x="10" y="14" width="8" height="6" rx="1" fill="#fbbf24" opacity=".5"/>
                        <circle cx="6"  cy="25" r="3" stroke="#fbbf24" stroke-width="2" fill="none"/>
                        <circle cx="20" cy="25" r="3" stroke="#fbbf24" stroke-width="2" fill="none"/>
                        <path d="M2 10 L2 22" stroke="#fbbf24" stroke-width="2" stroke-linecap="round"/>
                        <path d="M2 10 L8 10" stroke="#fbbf24" stroke-width="2" stroke-linecap="round"/>
                        <path d="M2 14 L8 14" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round" opacity=".6"/>
                        <path d="M22 12 L28 12 L28 22 L22 22" stroke="#fbbf24" stroke-width="2" stroke-linejoin="round" fill="none"/>
                    </svg>
                </div>
                <div>
                    <div class="panel-header-title">🏭 Bongkaran</div>
                    <div class="panel-header-sub">Unloading Gudang</div>
                </div>
                <div class="panel-header-count"><?= count($b_proses) ?> aktif / <?= count($bongkaran) ?> total</div>
            </div>

            <!-- Belum Bongkar -->
            <div class="subsection sub-menunggu">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>⏳</span> Belum Bongkar
                    <span class="sub-count"><?= count($b_menunggu) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body <?= empty($b_menunggu) ? 'collapsed' : '' ?>">
                    <?php if (empty($b_menunggu)) : ?>
                    <div class="empty-state">Tidak ada antrian</div>
                    <?php endif; ?>
                    <?php foreach ($b_menunggu as $b) : ?>
                    <div class="item-card item-menunggu">
                        <div class="item-icon">📦</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?></div>
                            <div class="item-meta">
                                <span><i class="fas fa-barcode" style="font-size:9px"></i> <?= htmlspecialchars($b['kode_bongkar']) ?></span>
                                <span><i class="fas fa-calendar" style="font-size:9px"></i> <?= date('d/m/Y', strtotime($b['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:0%"></div></div>
                            <div class="item-progres-txt">0%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Proses Bongkar -->
            <div class="subsection sub-proses">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span style="animation:spin 2s linear infinite;display:inline-block">⚙️</span> Proses Bongkar
                    <span class="sub-count"><?= count($b_proses) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body">
                    <?php if (empty($b_proses)) : ?>
                    <div class="empty-state">Tidak ada yang sedang diproses</div>
                    <?php endif; ?>
                    <?php foreach ($b_proses as $b) :
                        $progres = (int)($b['progres'] ?? 0);
                        $durasi  = '';
                        if (!empty($b['waktu_mulai'])) {
                            $sel = time() - strtotime($b['waktu_mulai']);
                            if ($sel > 0) { $j = floor($sel/3600); $m = floor(($sel%3600)/60); $durasi = ($j>0?"$j j ":"")."$m mnt"; }
                        }
                    ?>
                    <div class="item-card item-proses">
                        <div class="item-icon">🚛</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?></div>
                            <div class="item-meta">
                                <span>👷 <?= htmlspecialchars($b['nm_checker'] ?? '-') ?></span>
                                <?php if ($durasi) : ?>
                                <span class="dur-live"><?= $durasi ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:<?= $progres ?>%"></div></div>
                            <div class="item-progres-txt"><?= $progres ?>%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Done Bongkar -->
            <div class="subsection sub-done">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>✅</span> Done Bongkar
                    <span class="sub-count"><?= count($b_done) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body <?= empty($b_done) ? 'collapsed' : '' ?>">
                    <?php if (empty($b_done)) : ?>
                    <div class="empty-state">Belum ada yang selesai</div>
                    <?php endif; ?>
                    <?php foreach ($b_done as $b) :
                        $durasi = '';
                        if (!empty($b['waktu_mulai']) && !empty($b['waktu_selesai'])) {
                            $sel = strtotime($b['waktu_selesai']) - strtotime($b['waktu_mulai']);
                            if ($sel > 0) { $j = floor($sel/3600); $m = floor(($sel%3600)/60); $durasi = ($j>0?"$j j ":"")."$m mnt"; }
                        }
                    ?>
                    <div class="item-card item-done">
                        <div class="item-icon">✅</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?></div>
                            <div class="item-meta">
                                <span>👷 <?= htmlspecialchars($b['nm_checker'] ?? '-') ?></span>
                                <?php if ($durasi) : ?><span>⏱ <?= $durasi ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:100%"></div></div>
                            <div class="item-progres-txt">100%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


        <!-- ════ LOADING LK (Luar Kota) ════ -->
        <div class="panel loading-lk">
            <div class="panel-header">
                <div class="panel-header-icon">
                    <!-- Long-haul truck SVG -->
                    <svg viewBox="0 0 36 24" fill="none" width="26" height="18" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0" y="5" width="24" height="14" rx="2" fill="#1e3a5f"/>
                        <rect x="1" y="6" width="22" height="12" rx="1.5" fill="#2563eb" opacity=".4"/>
                        <path d="M24 9 L30 9 L34 14 L34 19 L24 19 Z" fill="#1e3a5f" stroke="#3b82f6" stroke-width="1.2"/>
                        <rect x="25" y="10" width="6" height="5" rx="1" fill="#60a5fa" opacity=".5"/>
                        <circle cx="7"  cy="21" r="2.5" stroke="#60a5fa" stroke-width="1.5" fill="none"/>
                        <circle cx="20" cy="21" r="2.5" stroke="#60a5fa" stroke-width="1.5" fill="none"/>
                        <circle cx="30" cy="21" r="2.5" stroke="#60a5fa" stroke-width="1.5" fill="none"/>
                        <path d="M0 14 L34 14" stroke="#3b82f6" stroke-width=".8" opacity=".4"/>
                        <!-- Speed lines -->
                        <path d="M2 3 L8 3" stroke="#60a5fa" stroke-width="1" stroke-linecap="round" opacity=".5"/>
                        <path d="M0 1 L5 1" stroke="#60a5fa" stroke-width="1" stroke-linecap="round" opacity=".3"/>
                    </svg>
                </div>
                <div>
                    <div class="panel-header-title">🛣️ Loading LK</div>
                    <div class="panel-header-sub">Luar Kota / Long Haul</div>
                </div>
                <div class="panel-header-count"><?= count($lk_proses) ?> aktif / <?= count($list_lk) ?> total</div>
            </div>

            <!-- Menunggu LK -->
            <div class="subsection sub-menunggu">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>⏳</span> Belum Loading
                    <span class="sub-count"><?= count($lk_menunggu) + count($lk_do) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body <?= (empty($lk_menunggu) && empty($lk_do)) ? 'collapsed' : '' ?>">
                    <?php if (empty($lk_menunggu) && empty($lk_do)) : ?>
                    <div class="empty-state">Tidak ada antrian</div>
                    <?php endif; ?>
                    <?php foreach (array_merge($lk_menunggu, $lk_do) as $lk) :
                        $st_label = ['MENUNGGU'=>'Menunggu','CETAK_DO'=>'Cetak DO','DO_SELESAI'=>'DO Selesai'][$lk['status']] ?? $lk['status'];
                    ?>
                    <div class="item-card item-menunggu">
                        <div class="item-icon">🛣️</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($lk['keterangan']) ?></div>
                            <div class="item-meta">
                                <span><i class="fas fa-barcode" style="font-size:9px"></i> <?= htmlspecialchars($lk['kode']??'-') ?></span>
                                <span><?= $st_label ?></span>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:<?= (int)($lk['progres']??0) ?>%"></div></div>
                            <div class="item-progres-txt"><?= (int)($lk['progres']??0) ?>%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Proses Loading LK -->
            <div class="subsection sub-proses">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>🚛</span> Proses Loading
                    <span class="sub-count"><?= count($lk_proses) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body">
                    <?php if (empty($lk_proses)) : ?>
                    <div class="empty-state">Tidak ada yang sedang loading</div>
                    <?php endif; ?>
                    <?php foreach ($lk_proses as $lk) :
                        $progres = (int)($lk['progres']??0);
                        $durasi  = '';
                        if (!empty($lk['waktu_mulai'])) {
                            $sel = time() - strtotime($lk['waktu_mulai']);
                            if ($sel > 0) { $j = floor($sel/3600); $m = floor(($sel%3600)/60); $durasi = ($j>0?"$j j ":"")."$m mnt"; }
                        }
                    ?>
                    <div class="item-card item-proses">
                        <div class="item-icon">🚛</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($lk['keterangan']) ?></div>
                            <div class="item-meta">
                                <span>👷 <?= htmlspecialchars($lk['nm_checker']??'-') ?></span>
                                <?php if ($durasi) : ?><span class="dur-live"><?= $durasi ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:<?= $progres ?>%"></div></div>
                            <div class="item-progres-txt"><?= $progres ?>%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Done LK -->
            <div class="subsection sub-done">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>✅</span> Done Loading LK
                    <span class="sub-count"><?= count($lk_done) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body <?= empty($lk_done) ? 'collapsed' : '' ?>">
                    <?php if (empty($lk_done)) : ?>
                    <div class="empty-state">Belum ada yang selesai</div>
                    <?php endif; ?>
                    <?php foreach ($lk_done as $lk) :
                        $durasi = '';
                        if (!empty($lk['waktu_mulai']) && !empty($lk['waktu_selesai'])) {
                            $sel = strtotime($lk['waktu_selesai']) - strtotime($lk['waktu_mulai']);
                            if ($sel > 0) { $j = floor($sel/3600); $m = floor(($sel%3600)/60); $durasi = ($j>0?"$j j ":"")."$m mnt"; }
                        }
                    ?>
                    <div class="item-card item-done">
                        <div class="item-icon">✅</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($lk['keterangan']) ?></div>
                            <div class="item-meta">
                                <span>👷 <?= htmlspecialchars($lk['nm_checker']??'-') ?></span>
                                <?php if ($durasi) : ?><span>⏱ <?= $durasi ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:100%"></div></div>
                            <div class="item-progres-txt">100%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>


        <!-- ════ LOADING KK (Kota-Kota) ════ -->
        <div class="panel loading-kk">
            <div class="panel-header">
                <div class="panel-header-icon">
                    <!-- City delivery truck SVG -->
                    <svg viewBox="0 0 36 24" fill="none" width="26" height="18" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0" y="6" width="22" height="13" rx="2" fill="#064e3b"/>
                        <rect x="1" y="7" width="20" height="11" rx="1.5" fill="#065f46" opacity=".5"/>
                        <path d="M22 10 L28 10 L32 14 L32 19 L22 19 Z" fill="#064e3b" stroke="#10b981" stroke-width="1.2"/>
                        <rect x="23" y="11" width="5" height="4" rx="1" fill="#34d399" opacity=".5"/>
                        <circle cx="7"  cy="21" r="2.5" stroke="#10b981" stroke-width="1.5" fill="none"/>
                        <circle cx="19" cy="21" r="2.5" stroke="#10b981" stroke-width="1.5" fill="none"/>
                        <circle cx="29" cy="21" r="2.5" stroke="#10b981" stroke-width="1.5" fill="none"/>
                        <path d="M0 15 L32 15" stroke="#10b981" stroke-width=".8" opacity=".4"/>
                        <!-- Building silhouettes as city marker -->
                        <rect x="3" y="2" width="3" height="5" fill="#10b981" opacity=".4"/>
                        <rect x="8" y="1" width="3" height="6" fill="#10b981" opacity=".4"/>
                        <rect x="13" y="3" width="3" height="4" fill="#10b981" opacity=".4"/>
                    </svg>
                </div>
                <div>
                    <div class="panel-header-title">🏙️ Loading KK</div>
                    <div class="panel-header-sub">Kota-Kota / Local</div>
                </div>
                <div class="panel-header-count"><?= count($kk_proses) ?> aktif / <?= count($list_kk) ?> total</div>
            </div>

            <!-- Menunggu KK -->
            <div class="subsection sub-menunggu">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>⏳</span> Belum Loading
                    <span class="sub-count"><?= count($kk_menunggu) + count($kk_do) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body <?= (empty($kk_menunggu) && empty($kk_do)) ? 'collapsed' : '' ?>">
                    <?php if (empty($kk_menunggu) && empty($kk_do)) : ?>
                    <div class="empty-state">Tidak ada antrian</div>
                    <?php endif; ?>
                    <?php foreach (array_merge($kk_menunggu, $kk_do) as $kk) :
                        $st_label = ['MENUNGGU'=>'Menunggu','CETAK_DO'=>'Cetak DO','DO_SELESAI'=>'DO Selesai'][$kk['status']] ?? $kk['status'];
                    ?>
                    <div class="item-card item-menunggu">
                        <div class="item-icon">🏙️</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($kk['keterangan']) ?></div>
                            <div class="item-meta">
                                <span><i class="fas fa-barcode" style="font-size:9px"></i> <?= htmlspecialchars($kk['kode']??'-') ?></span>
                                <span><?= $st_label ?></span>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:<?= (int)($kk['progres']??0) ?>%"></div></div>
                            <div class="item-progres-txt"><?= (int)($kk['progres']??0) ?>%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Proses Loading KK -->
            <div class="subsection sub-proses">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>🚚</span> Proses Loading
                    <span class="sub-count"><?= count($kk_proses) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body">
                    <?php if (empty($kk_proses)) : ?>
                    <div class="empty-state">Tidak ada yang sedang loading</div>
                    <?php endif; ?>
                    <?php foreach ($kk_proses as $kk) :
                        $progres = (int)($kk['progres']??0);
                        $durasi  = '';
                        if (!empty($kk['waktu_mulai'])) {
                            $sel = time() - strtotime($kk['waktu_mulai']);
                            if ($sel > 0) { $j = floor($sel/3600); $m = floor(($sel%3600)/60); $durasi = ($j>0?"$j j ":"")."$m mnt"; }
                        }
                    ?>
                    <div class="item-card item-proses">
                        <div class="item-icon">🚚</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($kk['keterangan']) ?></div>
                            <div class="item-meta">
                                <span>👷 <?= htmlspecialchars($kk['nm_checker']??'-') ?></span>
                                <?php if ($durasi) : ?><span class="dur-live"><?= $durasi ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:<?= $progres ?>%"></div></div>
                            <div class="item-progres-txt"><?= $progres ?>%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Done KK -->
            <div class="subsection sub-done">
                <div class="subsection-header" onclick="toggleSub(this)">
                    <span>✅</span> Done Loading KK
                    <span class="sub-count"><?= count($kk_done) ?></span>
                    <span class="sub-toggle">▼</span>
                </div>
                <div class="subsection-body <?= empty($kk_done) ? 'collapsed' : '' ?>">
                    <?php if (empty($kk_done)) : ?>
                    <div class="empty-state">Belum ada yang selesai</div>
                    <?php endif; ?>
                    <?php foreach ($kk_done as $kk) :
                        $durasi = '';
                        if (!empty($kk['waktu_mulai']) && !empty($kk['waktu_selesai'])) {
                            $sel = strtotime($kk['waktu_selesai']) - strtotime($kk['waktu_mulai']);
                            if ($sel > 0) { $j = floor($sel/3600); $m = floor(($sel%3600)/60); $durasi = ($j>0?"$j j ":"")."$m mnt"; }
                        }
                    ?>
                    <div class="item-card item-done">
                        <div class="item-icon">✅</div>
                        <div class="item-body">
                            <div class="item-title"><?= htmlspecialchars($kk['keterangan']) ?></div>
                            <div class="item-meta">
                                <span>👷 <?= htmlspecialchars($kk['nm_checker']??'-') ?></span>
                                <?php if ($durasi) : ?><span>⏱ <?= $durasi ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="item-right">
                            <div class="mini-progress"><div class="mini-progress-bar" style="width:100%"></div></div>
                            <div class="item-progres-txt">100%</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div><!-- /panels-grid -->

</div><!-- /dash-wrapper -->

<script>
// ── Live clock ──
(function tick() {
    var now = new Date();
    var h = String(now.getHours()).padStart(2,'0');
    var m = String(now.getMinutes()).padStart(2,'0');
    var s = String(now.getSeconds()).padStart(2,'0');
    document.getElementById('dashClock').textContent = h+':'+m+':'+s;
    setTimeout(tick, 1000);
})();

// ── Toggle subsection ──
function toggleSub(header) {
    var body = header.nextElementSibling;
    var toggle = header.querySelector('.sub-toggle');
    var isCollapsed = body.classList.contains('collapsed');
    body.classList.toggle('collapsed', !isCollapsed);
    header.classList.toggle('collapsed', !isCollapsed);
}

// ── Refresh button ──
var BASE = '<?= base_url() ?>';
document.getElementById('btnRefreshDash').addEventListener('click', function() {
    this.classList.add('spinning');
    setTimeout(function() { location.reload(); }, 300);
});

// ── Auto-refresh every 30s ──
setTimeout(function() { location.reload(); }, 30000);
</script>