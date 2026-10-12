<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KARISMA ERP — Presentasi Sistem Terpadu & Ekosistem Bisnis Digital</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-space: #050B14;
      --bg-surface: #0B1528;
      --bg-card: rgba(15, 29, 54, 0.75);
      --bg-card-hover: rgba(22, 43, 80, 0.9);
      --border-cyber: rgba(0, 229, 255, 0.25);
      --border-gold: rgba(245, 208, 96, 0.35);
      --gold-main: #F5D060;
      --gold-accent: #E5B82C;
      --cyan-neon: #00F0FF;
      --cyan-glow: rgba(0, 240, 255, 0.4);
      --emerald-neon: #10B981;
      --rose-neon: #F43F5E;
      --text-white: #FFFFFF;
      --text-silver: #CBD5E1;
      --text-muted: #8492A6;
      --gradient-cyber: linear-gradient(135deg, #00F0FF 0%, #3B82F6 50%, #8B5CF6 100%);
      --gradient-gold: linear-gradient(135deg, #FFF099 0%, #F5D060 50%, #C69214 100%);
      --gradient-dark: linear-gradient(180deg, #0B1528 0%, #050B14 100%);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body {
      background-color: var(--bg-space);
      color: var(--text-white);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      overflow-x: hidden;
      background-image: 
        radial-gradient(circle at 15% 20%, rgba(0, 240, 255, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 85% 80%, rgba(245, 208, 96, 0.06) 0%, transparent 40%),
        linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
      background-size: 100% 100%, 100% 100%, 40px 40px, 40px 40px;
    }

    .topbar {
      width: 100%;
      max-width: 1280px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 24px;
      background: var(--bg-card);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-radius: 14px;
      border: 1px solid var(--border-cyber);
      margin-bottom: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }

    .brand-title {
      display: flex;
      align-items: center;
      gap: 12px;
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 700;
      font-size: 20px;
      letter-spacing: 1px;
      background: var(--gradient-gold);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .badge-cyber {
      background: rgba(0, 240, 255, 0.15);
      color: var(--cyan-neon);
      border: 1px solid var(--cyan-neon);
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      -webkit-text-fill-color: var(--cyan-neon);
    }

    .nav-controls {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-nav {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: var(--text-white);
      padding: 8px 18px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      text-decoration: none;
    }

    .btn-nav:hover {
      background: var(--cyan-neon);
      color: var(--bg-space);
      border-color: var(--cyan-neon);
      box-shadow: 0 0 16px var(--cyan-glow);
      transform: translateY(-1px);
    }

    .btn-gold {
      background: var(--gold-main);
      color: var(--bg-space);
      border: 1px solid var(--gold-main);
      font-weight: 700;
    }

    .btn-gold:hover {
      background: #FFF;
      color: var(--bg-space);
      box-shadow: 0 0 20px rgba(245, 208, 96, 0.6);
      border-color: #FFF;
    }

    .counter-pill {
      font-family: 'Space Grotesk', monospace;
      font-weight: 700;
      font-size: 14px;
      color: var(--gold-main);
      background: rgba(0, 0, 0, 0.3);
      padding: 6px 14px;
      border-radius: 8px;
      border: 1px solid rgba(245, 208, 96, 0.2);
    }

    .slide-viewport {
      width: 100%;
      max-width: 1280px;
      aspect-ratio: 16 / 9;
      background: var(--gradient-dark);
      border-radius: 20px;
      border: 1px solid rgba(0, 240, 255, 0.3);
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.7), 0 0 40px rgba(0, 240, 255, 0.1);
      position: relative;
      overflow: hidden;
      display: flex;
    }

    .slide {
      position: absolute;
      inset: 0;
      opacity: 0;
      visibility: hidden;
      transform: scale(0.98) translateY(10px);
      transition: opacity 0.4s ease, transform 0.4s ease, visibility 0.4s;
      padding: 44px 56px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .slide.active {
      opacity: 1;
      visibility: visible;
      transform: scale(1) translateY(0);
    }

    .slide-tagline {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: var(--cyan-neon);
      background: rgba(0, 240, 255, 0.1);
      padding: 5px 14px;
      border-radius: 20px;
      border-left: 3px solid var(--cyan-neon);
      margin-bottom: 12px;
      width: fit-content;
    }

    .slide-tagline.gold {
      color: var(--gold-main);
      background: rgba(245, 208, 96, 0.1);
      border-left-color: var(--gold-main);
    }

    .slide-heading {
      font-size: 32px;
      font-weight: 800;
      color: #FFFFFF;
      line-height: 1.25;
      margin-bottom: 6px;
      letter-spacing: -0.5px;
    }

    .slide-subheading {
      font-size: 15px;
      color: var(--text-silver);
      font-weight: 400;
      line-height: 1.5;
      margin-bottom: 20px;
      max-width: 95%;
    }

    .accent-bar {
      width: 60px;
      height: 3px;
      background: var(--gradient-cyber);
      border-radius: 2px;
      margin-bottom: 24px;
    }

    .accent-bar.gold {
      background: var(--gradient-gold);
    }

    .grid-2 {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      flex: 1;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      flex: 1;
    }

    .grid-4 {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      flex: 1;
    }

    .grid-split {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 24px;
      flex: 1;
    }

    .glass-card {
      background: var(--bg-card);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      padding: 20px 22px;
      display: flex;
      flex-direction: column;
      backdrop-filter: blur(12px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .glass-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, rgba(0, 240, 255, 0.4), transparent);
    }

    .glass-card:hover {
      background: var(--bg-card-hover);
      border-color: rgba(0, 240, 255, 0.4);
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.5), 0 0 20px rgba(0, 240, 255, 0.15);
    }

    .card-icon {
      font-size: 24px;
      margin-bottom: 10px;
      display: inline-block;
    }

    .card-title {
      font-size: 16px;
      font-weight: 700;
      color: #FFFFFF;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-title.cyan { color: var(--cyan-neon); }
    .card-title.gold { color: var(--gold-main); }
    .card-title.emerald { color: var(--emerald-neon); }

    .card-desc {
      font-size: 13px;
      color: var(--text-silver);
      line-height: 1.55;
    }

    .card-list {
      list-style: none;
      margin-top: 8px;
    }

    .card-list li {
      font-size: 12.5px;
      color: var(--text-silver);
      margin-bottom: 6px;
      display: flex;
      align-items: flex-start;
      gap: 8px;
      line-height: 1.45;
    }

    .card-list li::before {
      content: '▹';
      color: var(--cyan-neon);
      font-weight: bold;
    }

    .metric-hero {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 24px;
      background: rgba(0, 240, 255, 0.04);
      border-radius: 16px;
      border: 1px solid var(--border-cyber);
    }

    .metric-number {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 48px;
      font-weight: 800;
      background: var(--gradient-gold);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      line-height: 1;
      margin-bottom: 8px;
    }

    .metric-label {
      font-size: 13px;
      font-weight: 600;
      color: var(--cyan-neon);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .flow-horizontal {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 16px 0;
      width: 100%;
    }

    .flow-step {
      flex: 1;
      background: var(--bg-card);
      border: 1px solid rgba(0, 240, 255, 0.2);
      border-radius: 12px;
      padding: 14px;
      text-align: center;
      position: relative;
    }

    .flow-step.active {
      border-color: var(--gold-main);
      background: rgba(245, 208, 96, 0.08);
    }

    .flow-step-number {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 11px;
      font-weight: 800;
      color: var(--cyan-neon);
      margin-bottom: 4px;
    }

    .flow-step-title {
      font-size: 13px;
      font-weight: 700;
      color: #FFFFFF;
      margin-bottom: 2px;
    }

    .flow-step-desc {
      font-size: 11px;
      color: var(--text-muted);
    }

    .flow-arrow {
      color: var(--gold-main);
      font-size: 16px;
      font-weight: bold;
    }

    .compare-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      flex: 1;
    }

    .compare-box {
      border-radius: 14px;
      padding: 20px;
      display: flex;
      flex-direction: column;
    }

    .compare-box.before {
      background: rgba(244, 63, 94, 0.06);
      border: 1px solid rgba(244, 63, 94, 0.25);
    }

    .compare-box.after {
      background: rgba(16, 185, 129, 0.08);
      border: 1px solid rgba(16, 185, 129, 0.35);
    }

    .compare-header {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .compare-box.before .compare-header { color: var(--rose-neon); }
    .compare-box.after .compare-header { color: var(--emerald-neon); }

    .slide-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 14px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      font-size: 11px;
      color: var(--text-muted);
      font-weight: 500;
      letter-spacing: 0.5px;
    }

    .slide-footer span strong {
      color: var(--gold-main);
    }

    .hero-container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      height: 100%;
      max-width: 900px;
    }

    .hero-title {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 54px;
      font-weight: 900;
      line-height: 1.1;
      margin-bottom: 14px;
      background: linear-gradient(135deg, #FFFFFF 0%, #E2E8F0 50%, #94A3B8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: -1.5px;
    }

    .hero-highlight {
      background: var(--gradient-gold);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .hero-pillars {
      display: flex;
      gap: 14px;
      margin-top: 30px;
      width: 100%;
    }

    .pillar-item {
      flex: 1;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(0, 240, 255, 0.2);
      border-radius: 12px;
      padding: 14px 18px;
    }

    .pillar-title {
      font-size: 13px;
      font-weight: 700;
      color: var(--cyan-neon);
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .pillar-desc {
      font-size: 11.5px;
      color: var(--text-silver);
      line-height: 1.4;
    }

    .kpi-row {
      display: flex;
      gap: 12px;
      margin-bottom: 16px;
    }

    .kpi-mini {
      flex: 1;
      background: rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 10px 14px;
    }

    .kpi-mini-val {
      font-family: 'Space Grotesk', monospace;
      font-size: 20px;
      font-weight: 800;
      color: var(--gold-main);
    }

    .kpi-mini-lbl {
      font-size: 10.5px;
      color: var(--text-muted);
      text-transform: uppercase;
      margin-top: 2px;
    }

    .keyboard-hint {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 14px;
    }

    .kbd-key {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 4px;
      padding: 2px 8px;
      font-family: monospace;
      font-size: 11px;
      color: var(--gold-main);
    }
  </style>
</head>
<body>

  <!-- Top Navigation Bar -->
  <header class="topbar">
    <div class="brand-title">
      <span>KARISMA ERP</span>
      <span class="badge-cyber">Integrated Digital Ecosystem</span>
    </div>
    <div class="nav-controls">
      <button class="btn-nav" onclick="prevSlide()">◀ Slide Sebelumnya</button>
      <span id="slideCounter" class="counter-pill">Slide 1 / 12</span>
      <button class="btn-nav" onclick="nextSlide()">Slide Selanjutnya ▶</button>
      <button class="btn-nav btn-gold" onclick="toggleFullscreen()">⛶ Layar Penuh</button>
    </div>
  </header>

  <!-- Slide Master Container -->
  <main class="slide-viewport" id="viewport">

    <!-- ========================================== -->
    <!-- SLIDE 1: COVER & EXECUTIVE SUMMARY -->
    <!-- ========================================== -->
    <section class="slide active">
      <div class="hero-container">
        <div class="slide-tagline gold">ENTERPRISE RESOURCE PLANNING SYSTEM</div>
        <h1 class="hero-title">
          KARISMA ERP<br>
          <span class="hero-highlight">Sistem Terpadu & Ekosistem Digital</span>
        </h1>
        <p class="slide-subheading" style="font-size: 16px;">
          Digitalisasi menyeluruh alur transaksi bisnis lintas departemen berbasis cloud & web. Mewujudkan efisiensi tinggi, validitas data mutlak, akurasi stok, dan presisi akuntansi dimana saja.
        </p>

        <div class="hero-pillars">
          <div class="pillar-item">
            <div class="pillar-title">⚡ Efisiensi Tinggi</div>
            <div class="pillar-desc">Otomasi alur pesanan ke pengiriman memangkas waktu proses hingga 70% secara paperless.</div>
          </div>
          <div class="pillar-item">
            <div class="pillar-title">🎯 Valid & Akurat</div>
            <div class="pillar-desc">Verifikasi berjenjang dari plafon kredit, tonase armada, hingga saldo buku besar.</div>
          </div>
          <div class="pillar-item">
            <div class="pillar-title">🌐 Remote Autonomous</div>
            <div class="pillar-desc">Monitoring, approval, dan eksekusi operasional dapat dikerjakan 100% dari mana saja.</div>
          </div>
        </div>
      </div>
      <div class="slide-footer">
        <span>PT. Karisma Indoagro Universal • <strong>Executive Presentation 2026</strong></span>
        <span>Slide 01 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 2: VISI & INTEGRASI LINTAS DEPARTEMEN -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline">ARCHITECTURE PHILOSOPHY</div>
        <h2 class="slide-heading">Visi & Integrasi Ekosistem Digital</h2>
        <p class="slide-subheading">
          Mengakhiri era data terisolasi (silo). Karisma ERP menghubungkan seluruh divisi dalam satu arsitektur terintegrasi dengan prinsip <em>Single Source of Truth</em>.
        </p>
        <div class="accent-bar"></div>
      </div>

      <div class="grid-3">
        <div class="glass-card">
          <div class="card-icon">🔄</div>
          <div class="card-title cyan">Single Source of Truth</div>
          <p class="card-desc">Satu database terpusat yang diakses bersama oleh Sales, Gudang, Logistik, Purchasing, Keuangan, dan Direksi tanpa duplikasi pencatatan.</p>
        </div>
        <div class="glass-card">
          <div class="card-icon">🛡️</div>
          <div class="card-title gold">Zero Human Error</div>
          <p class="card-desc">Validasi sistematis di setiap titik: pengecekan plafon kredit otomatis, kapasitas tonase armada, dan sinkronisasi saldo persediaan realtime.</p>
        </div>
        <div class="glass-card">
          <div class="card-icon">⚡</div>
          <div class="card-title emerald">Kecepatan & Responsif</div>
          <p class="card-desc">Penerbitan faktur instan, live nesting filter tanpa reload, serta pelacakan status pesanan hulu ke hilir secara real-time.</p>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Filosofi Arsitektur</strong></span>
        <span>Slide 02 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 3: ALUR BISNIS TERPADU (END-TO-END FLOW) -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline gold">END-TO-END BUSINESS PIPELINE</div>
        <h2 class="slide-heading">Alur Bisnis Terintegrasi Lintas Divisi</h2>
        <p class="slide-subheading">
          Setiap transaksi mengalir otomatis dari penjualan hingga buku besar keuangan tanpa celah proses manual.
        </p>
        <div class="accent-bar gold"></div>
      </div>

      <div class="flow-horizontal">
        <div class="flow-step active">
          <div class="flow-step-number">01. SALES</div>
          <div class="flow-step-title">Sales Order (SO)</div>
          <div class="flow-step-desc">Input SO, Plafon Cek & Auto Tonase</div>
        </div>
        <div class="flow-arrow">➔</div>
        <div class="flow-step">
          <div class="flow-step-number">02. LOGISTIK</div>
          <div class="flow-step-title">Armada & Checker</div>
          <div class="flow-step-desc">Loading Order & Digital Checklist</div>
        </div>
        <div class="flow-arrow">➔</div>
        <div class="flow-step">
          <div class="flow-step-number">03. INVOICING</div>
          <div class="flow-step-title">Faktur & Surat Jalan</div>
          <div class="flow-step-desc">BKP 11%, Non-BKP, Faktur Z & DO</div>
        </div>
        <div class="flow-arrow">➔</div>
        <div class="flow-step">
          <div class="flow-step-number">04. WAREHOUSE</div>
          <div class="flow-step-title">ICS & Buffer Stock</div>
          <div class="flow-step-desc">Mutasi Batch & Auto Reorder Alert</div>
        </div>
        <div class="flow-arrow">➔</div>
        <div class="flow-step">
          <div class="flow-step-number">05. PURCHASING</div>
          <div class="flow-step-title">PO & LPB Terpadu</div>
          <div class="flow-step-desc">Split Invoice & DPP Nilai Lain</div>
        </div>
        <div class="flow-arrow">➔</div>
        <div class="flow-step">
          <div class="flow-step-number">06. FINANCE</div>
          <div class="flow-step-title">Jurnal & Buku Besar</div>
          <div class="flow-step-desc">Auto-Journal & Laporan Keuangan</div>
        </div>
      </div>

      <div class="grid-2" style="margin-top: 10px;">
        <div class="glass-card" style="padding: 12px 16px;">
          <div class="card-title cyan" style="font-size: 13px;">🔗 Sinkronisasi Logistik-ke-Faktur</div>
          <p class="card-desc" style="font-size: 12px;">Hanya barang yang telah diverifikasi fisik oleh Checker Gudang yang dapat diterbitkan Faktur Penjualan resminya.</p>
        </div>
        <div class="glass-card" style="padding: 12px 16px;">
          <div class="card-title gold" style="font-size: 13px;">🔗 Sinkronisasi Purchasing-ke-Jurnal</div>
          <p class="card-desc" style="font-size: 12px;">Laporan Penerimaan Barang (LPB) yang di-POST otomatis mencatat pengakuan persediaan dan hutang dagang supplier secara presisi.</p>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>End-to-End Pipeline</strong></span>
        <span>Slide 03 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 4: PETA MODUL APLIKASI (EXISTING MODULES) -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline">MODULAR ECOSYSTEM</div>
        <h2 class="slide-heading">Modul-Modul yang Siap Beroperasi</h2>
        <p class="slide-subheading">
          Ekosistem Karisma ERP terdiri dari modul-modul fungsional lengkap yang telah aktif mendukung operasional harian.
        </p>
        <div class="accent-bar"></div>
      </div>

      <div class="grid-3">
        <div class="glass-card">
          <div class="card-title cyan">📦 Sales & Order Counter</div>
          <ul class="card-list">
            <li>Input Sales Order (SO) Multi-Skema</li>
            <li>Proteksi Plafon Kredit Pelanggan</li>
            <li>Grouping Rute & Rayon Pengiriman</li>
            <li>Pengajuan Diskon ke Manager SC</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title cyan">🚚 Logistik & Armada</div>
          <ul class="card-list">
            <li>Loading Order (Urutan Muat Truk)</li>
            <li>Kalkulasi Tonase & Kubikasi Maksimal</li>
            <li>Digital Checker Gudang Checklist</li>
            <li>Penerbitan Surat Jalan (DO) Resmi</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title gold">📑 Invoicing & Admin SC</div>
          <ul class="card-list">
            <li>Penerbitan Faktur Standar & Faktur Z</li>
            <li>Fitur Split / Pecah Faktur Turunan</li>
            <li>Pajak BKP 11%, Non-BKP, & Promosi</li>
            <li>Cetak Faktur Massal per Rute</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title gold">🏬 ICS & Warehouse Control</div>
          <ul class="card-list">
            <li>Stock Opname Scan & Realtime Lot/Exp</li>
            <li>Buffer Stock Control & Live Nesting Filter</li>
            <li>Rekomendasi Auto-Reorder (Box/Pcs)</li>
            <li>Mutasi Barang Antar-Gudang</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title emerald">🛒 Purchasing & LPB</div>
          <ul class="card-list">
            <li>Data PO vs Laporan Penerimaan (LPB)</li>
            <li>Detail Record LPB Split & Unpost Alasan</li>
            <li>Kalkulasi DPP Nilai Lain & PPN 11%</li>
            <li>Retur Pembelian & Penyesuaian Harga</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title emerald">💰 Keuangan & Akuntansi</div>
          <ul class="card-list">
            <li>Chart of Accounts (COA) / Peta Akun</li>
            <li>Jurnal Umum & Buku Besar Otomatis</li>
            <li>Kas Masuk, Kas Keluar & Kasbon</li>
            <li>Jadwal Pembayaran Hutang Supplier</li>
          </ul>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Daftar Modul Terpadu</strong></span>
        <span>Slide 04 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 5: MODUL SALES & LOGISTIK PRESISI -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline gold">DEEP DIVE: SALES & LOGISTICS</div>
        <h2 class="slide-heading">Manajemen Sales & Logistik Berbasis Presisi</h2>
        <p class="slide-subheading">
          Mengoptimalkan muatan armada, rute distribusi, serta mencegah piutang tak tertagih sejak awal input pesanan.
        </p>
        <div class="accent-bar gold"></div>
      </div>

      <div class="grid-3">
        <div class="glass-card">
          <div class="card-title cyan">🛡️ Plafon Kredit & Skema Bayar</div>
          <p class="card-desc">Sistem memvalidasi batas kredit secara otomatis. SO akan terkunci jika melebihi plafon atau ada tunggakan jatuh tempo (Cash, Transfer, BG, Tempo 30/60/90).</p>
        </div>

        <div class="glass-card">
          <div class="card-title gold">⚖️ Otomasi Tonase & Kubikasi</div>
          <p class="card-desc">Kalkulator berat otomatis membatasi muatan truk (standar maks 7.5 Ton dan 25 m³). Mencegah armada overload dan mematuhi regulasi transportasi.</p>
        </div>

        <div class="glass-card">
          <div class="card-title emerald">🗺️ Urutan Muat (Loading Order)</div>
          <p class="card-desc">Penataan barang dalam truk disesuaikan rute pengiriman (drop point). Barang toko pertama diletakkan paling belakang untuk kecepatan bongkar muat.</p>
        </div>
      </div>

      <div class="grid-2" style="margin-top: 14px;">
        <div class="glass-card">
          <div class="card-title cyan" style="font-size: 14px;">📋 Digital Checker Gudang</div>
          <p class="card-desc">Petugas gudang menggunakan tablet/ponsel untuk verifikasi fisik barang. Status SO berubah otomatis menjadi <em>Siap Faktur</em> hanya setelah diverifikasi checker.</p>
        </div>
        <div class="glass-card">
          <div class="card-title gold" style="font-size: 14px;">✂️ Invoicing Fleksibel & Split Faktur Z</div>
          <p class="card-desc">Admin SC dapat menerbitkan Faktur Induk dan memecahnya ke beberapa kios turunan secara sah, akurat, dan langsung terintegrasi dengan Surat Jalan (DO).</p>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Sales & Logistik</strong></span>
        <span>Slide 05 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 6: GUDANG, BUFFER STOCK & PURCHASING -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline">DEEP DIVE: INVENTORY & PROCUREMENT</div>
        <h2 class="slide-heading">ICS, Buffer Stock Control & Smart Purchasing</h2>
        <p class="slide-subheading">
          Kendali persediaan real-time dengan FEFO (First-Expired, First-Out) dan peringatan otomatis stok kritis.
        </p>
        <div class="accent-bar"></div>
      </div>

      <div class="grid-split">
        <div class="glass-card">
          <div class="card-title cyan">📊 Buffer Stock Control & Live Nesting Filter</div>
          <p class="card-desc" style="margin-bottom: 12px;">Sistem klasifikasi 4 status stok secara otomatis tanpa perlu submit ulang:</p>
          <div class="kpi-row">
            <div class="kpi-mini" style="border-left: 3px solid var(--rose-neon);">
              <div class="kpi-mini-val" style="color: var(--rose-neon);">CRITICAL</div>
              <div class="kpi-mini-lbl">Stok = 0 / Segera Habis</div>
            </div>
            <div class="kpi-mini" style="border-left: 3px solid var(--gold-main);">
              <div class="kpi-mini-val" style="color: var(--gold-main);">UNDER BUFFER</div>
              <div class="kpi-mini-lbl">Di Bawah Batas Minimum</div>
            </div>
            <div class="kpi-mini" style="border-left: 3px solid var(--emerald-neon);">
              <div class="kpi-mini-val" style="color: var(--emerald-neon);">SAFE</div>
              <div class="kpi-mini-lbl">Stok Aman & Terkendali</div>
            </div>
          </div>
          <ul class="card-list">
            <li><strong>Auto Reorder Recommendation</strong>: Hitungan otomatis jumlah pembelian dalam satuan Box & Pcs.</li>
            <li><strong>Export CSV/Excel Instan</strong>: Satu klik untuk mengirimkan daftar order ke supplier.</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title emerald">🛒 Purchasing & LPB Terpadu</div>
          <ul class="card-list">
            <li><strong>LPB vs PO Monitoring</strong>: Pencocokan surat jalan supplier dengan PO Logistik.</li>
            <li><strong>Split Invoice & Multi-Invoice</strong>: Fleksibilitas penerimaan bertahap per faktur supplier.</li>
            <li><strong>DPP Nilai Lain & PPN 11%</strong>: Perhitungan pajak akurat dan otomatis sinkron ke akuntansi.</li>
            <li><strong>Audit Post / Unpost</strong>: Proteksi data dengan kewajiban mengisi alasan pembatalan.</li>
          </ul>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Gudang & Purchasing</strong></span>
        <span>Slide 06 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 7: KEUANGAN & AKUNTANSI OTOMATIS -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline gold">DEEP DIVE: FINANCE & ACCOUNTING</div>
        <h2 class="slide-heading">Keuangan & Akuntansi Otomatis</h2>
        <p class="slide-subheading">
          Pencatatan pembukuan otomatis (General Ledger) berbasis transaksi operasional tanpa entri manual ganda.
        </p>
        <div class="accent-bar gold"></div>
      </div>

      <div class="grid-3">
        <div class="glass-card">
          <div class="card-title cyan">📖 Chart of Accounts (COA)</div>
          <p class="card-desc">Struktur akun hierarki resmi perusahaan: Kas, Bank, Piutang Usaha, Persediaan, Hutang Supplier, Pendapatan Penjualan, HPP, hingga Beban Operasional.</p>
        </div>

        <div class="glass-card">
          <div class="card-title gold">⚡ Auto-Journaling System</div>
          <p class="card-desc">Setiap penerbitan faktur penjualan atau posting LPB langsung membentuk jurnal debit-kredit secara otomatis, seimbang, dan valid.</p>
        </div>

        <div class="glass-card">
          <div class="card-title emerald">💳 Pengendalian Kas & Hutang</div>
          <p class="card-desc">Modul Kas Masuk, Kas Keluar, Kasbon Karyawan, dan Penjadwalan Pembayaran Hutang Supplier dengan kontrol jatuh tempo presisi.</p>
        </div>
      </div>

      <div class="glass-card" style="margin-top: 14px; padding: 14px 20px;">
        <div class="card-title cyan" style="font-size: 14px;">⚖️ Rekonsiliasi & Sinkronisasi</div>
        <p class="card-desc" style="font-size: 12.5px;">
          Dilengkapi modul data akuntansi dan penyesuaian periode fiskal resmi untuk pelaporan laba rugi dan neraca keuangan yang dapat diaudit (auditable).
        </p>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Finance & Accounting</strong></span>
        <span>Slide 07 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 8: HARAPAN & DAMPAK POSITIF -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline">BUSINESS VALUE & IMPACT</div>
        <h2 class="slide-heading">Harapan & Dampak Positif Implementasi</h2>
        <p class="slide-subheading">
          Peningkatan kinerja terukur di seluruh aspek operasional, keuangan, dan manajemen strategis.
        </p>
        <div class="accent-bar"></div>
      </div>

      <div class="grid-4">
        <div class="metric-hero">
          <div class="metric-number">70%</div>
          <div class="metric-label">Efisiensi Waktu</div>
          <p class="card-desc" style="margin-top: 8px; font-size: 11.5px;">Pemrosesan pesanan dari SO hingga faktur jauh lebih cepat.</p>
        </div>

        <div class="metric-hero">
          <div class="metric-number">99.8%</div>
          <div class="metric-label">Akurasi Stok</div>
          <p class="card-desc" style="margin-top: 8px; font-size: 11.5px;">Eliminasi selisih stok fisik vs sistem dengan scan & opname.</p>
        </div>

        <div class="metric-hero">
          <div class="metric-number">100%</div>
          <div class="metric-label">Paperless</div>
          <p class="card-desc" style="margin-top: 8px; font-size: 11.5px;">Seluruh dokumen (SO, DO, LPB, Jurnal) terarsip digital.</p>
        </div>

        <div class="metric-hero">
          <div class="metric-number">0%</div>
          <div class="metric-label">Redundansi</div>
          <p class="card-desc" style="margin-top: 8px; font-size: 11.5px;">Satu input mengalir ke seluruh departemen tanpa re-entry.</p>
        </div>
      </div>

      <div class="grid-2" style="margin-top: 16px;">
        <div class="glass-card">
          <div class="card-title cyan">📈 Keputusan Direksi Cepat & Tepat</div>
          <p class="card-desc">Executive Dashboard menyajikan data real-time penjualan, margin keuntungan, dan perputaran modal kerja dalam hitungan detik.</p>
        </div>
        <div class="glass-card">
          <div class="card-title gold">🚚 Optimalisasi Utilisasi Armada</div>
          <p class="card-desc">Tonase maksimal dan urutan muat yang rapi menekan biaya bahan bakar dan meminimalisir keterlambatan pengiriman ke toko.</p>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Dampak & Nilai Bisnis</strong></span>
        <span>Slide 08 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 9: MITIGASI DAMPAK NEGATIF & OTOMASI NOTIFIKASI -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline gold">RISK MITIGATION & SMART ALERTS</div>
        <h2 class="slide-heading">Meminimalisir Dampak Negatif & Otomasi Notifikasi</h2>
        <p class="slide-subheading">
          Menggantikan titik rawan manual dengan proteksi sistem otomatis dan sistem notifikasi pintar.
        </p>
        <div class="accent-bar gold"></div>
      </div>

      <div class="compare-container">
        <div class="compare-box before">
          <div class="compare-header">❌ Risiko Operasional Konvensional</div>
          <ul class="card-list">
            <li>Piutang macet karena penjualan melebihi batas kredit.</li>
            <li>Truk kelebihan muatan (overload) & risiko denda jembatan timbang.</li>
            <li>Barang kadaluarsa (expired) akibat tidak terpantaunya batch.</li>
            <li>Kehabisan stok barang laris (stockout) mendadak.</li>
            <li>Keterlambatan approval karena menunggu tanda tangan fisik.</li>
          </ul>
        </div>

        <div class="compare-box after">
          <div class="compare-header">✅ Solusi & Otomasi Notifikasi Karisma ERP</div>
          <ul class="card-list">
            <li><strong>Auto Credit Lock</strong>: Otomatis memblokir SO pelanggan bermasalah.</li>
            <li><strong>Auto Tonase Alert</strong>: Peringatan visual jika berat SO > 7.5 Ton.</li>
            <li><strong>FEFO Warning Trigger</strong>: Notifikasi berkala barang mendekati masa expired.</li>
            <li><strong>Smart Buffer Stock Push</strong>: Notifikasi WhatsApp/Email ke Purchasing saat status Critical.</li>
            <li><strong>Instant Digital Approval</strong>: Notifikasi push ke Manager SC saat ada diskon khusus.</li>
          </ul>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Mitigasi Risiko & Otomasi</strong></span>
        <span>Slide 09 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 10: KAPABILITAS KERJA TANPA ON-SITE (REMOTE READY) -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline">100% REMOTE & CLOUD-READY</div>
        <h2 class="slide-heading">Kapabilitas Eksekusi Dimana Saja</h2>
        <p class="slide-subheading">
          Operasional bisnis berjalan mandiri dan fleksibel. Manajemen serta tim dapat bekerja dari mana saja tanpa hambatan fisik.
        </p>
        <div class="accent-bar"></div>
      </div>

      <div class="grid-3">
        <div class="glass-card">
          <div class="card-icon">☁️</div>
          <div class="card-title cyan">Akses Web & Mobile Cloud</div>
          <p class="card-desc">Dapat diakses melalui browser di laptop, tablet, atau smartphone tanpa instalasi rumit di setiap komputer cabang.</p>
        </div>

        <div class="glass-card">
          <div class="card-icon">✍️</div>
          <div class="card-title gold">Persetujuan Berjenjang Digital</div>
          <p class="card-desc">Manager & Direksi dapat meninjau, menyetujui, atau menolak pengajuan harga dan retur secara instan dari lokasi manapun.</p>
        </div>

        <div class="glass-card">
          <div class="card-icon">📊</div>
          <div class="card-title emerald">Pengawasan Real-Time</div>
          <p class="card-desc">Monitoring status pengiriman, laporan stok gudang, dan omset harian langsung terpampang di dashboard dimana saja.</p>
        </div>
      </div>

      <div class="grid-2" style="margin-top: 14px;">
        <div class="glass-card">
          <div class="card-title cyan" style="font-size: 14px;">📄 Dokumen Digital & e-Faktur</div>
          <p class="card-desc">Surat Jalan (DO), Faktur, dan Bukti Kas Masuk dapat dicetak atau dikirimkan langsung dalam format PDF/digital ke pelanggan dan supplier.</p>
        </div>
        <div class="glass-card">
          <div class="card-title gold" style="font-size: 14px;">🔒 Keamanan Multi-Layer</div>
          <p class="card-desc">Autentikasi sesi aman, pembatasan hak akses berbasis peran (RBAC), serta enkripsi data melindungi kerahasiaan transaksi perusahaan.</p>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Remote Execution</strong></span>
        <span>Slide 10 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 11: TATA KELOLA & KEAMANAN SISTEM (RBAC) -->
    <!-- ========================================== -->
    <section class="slide">
      <div>
        <div class="slide-tagline gold">GOVERNANCE & SECURITY</div>
        <h2 class="slide-heading">Keamanan Data & Hak Akses Berjenjang (RBAC)</h2>
        <p class="slide-subheading">
          Perlindungan data ketat dengan pemisahan tugas (Segregation of Duties) sesuai struktur organisasi perusahaan.
        </p>
        <div class="accent-bar gold"></div>
      </div>

      <div class="grid-4">
        <div class="glass-card">
          <div class="card-title cyan" style="font-size: 14px;">👑 Direksi & Management</div>
          <ul class="card-list">
            <li>Akses Executive Dashboard</li>
            <li>Laporan Finansial Komprehensif</li>
            <li>KPI & Penilaian Supervisi</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title cyan" style="font-size: 14px;">💼 Sales & Counter</div>
          <ul class="card-list">
            <li>Input Sales Order (SO)</li>
            <li>Pemeriksaan Plafon Pelanggan</li>
            <li>Pengajuan Retur (SPR)</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title gold" style="font-size: 14px;">🚚 Logistik & Warehouse</div>
          <ul class="card-list">
            <li>Loading Order Armada</li>
            <li>Digital Checker Gudang</li>
            <li>Stock Opname & Mutasi</li>
          </ul>
        </div>

        <div class="glass-card">
          <div class="card-title emerald" style="font-size: 14px;">💰 Purchasing & Keuangan</div>
          <ul class="card-list">
            <li>LPB & Verifikasi Harga PO</li>
            <li>Jurnal & Buku Besar Akuntansi</li>
            <li>Kas Masuk / Keluar & Hutang</li>
          </ul>
        </div>
      </div>

      <div class="glass-card" style="margin-top: 14px; padding: 14px 20px;">
        <div class="card-title gold" style="font-size: 13.5px;">🔍 Rekam Jejak Aktivitas (Audit Trail)</div>
        <p class="card-desc" style="font-size: 12.5px;">
          Setiap tindakan perubahan harga, pembatalan pesanan (Unpost), dan penghapusan data terekam lengkap beserta identitas pengguna (user ID), waktu transaksi (timestamp), dan catatan alasan.
        </p>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>Tata Kelola & Keamanan</strong></span>
        <span>Slide 11 / 12</span>
      </div>
    </section>

    <!-- ========================================== -->
    <!-- SLIDE 12: KESIMPULAN & ROADMAP MASA DEPAN -->
    <!-- ========================================== -->
    <section class="slide">
      <div style="margin: auto 0;">
        <div class="slide-tagline gold">THE FUTURE OF ENTERPRISE</div>
        <h2 class="slide-heading" style="font-size: 38px;">
          Karisma ERP
        </h2>
        <p class="slide-subheading" style="font-size: 16px; margin-bottom: 24px;">
          Transformasi digital bukan lagi pilihan, melainkan keunggulan kompetitif. Karisma ERP siap membawa Karisma Indoagro Universal menuju era efisiensi, akurasi, dan pertumbuhan eksponensial.
        </p>

        <div class="grid-3" style="margin-bottom: 24px;">
          <div class="glass-card">
            <div class="card-title cyan">🚀 Fase Saat Ini</div>
            <p class="card-desc">Stabilisasi seluruh alur SO-Faktur-LPB-Jurnal, implementasi Buffer Stock Control, dan optimasi checker armada.</p>
          </div>
          <div class="glass-card">
            <div class="card-title gold">🔔 Fase Peningkatan Otomasi</div>
            <p class="card-desc">Integrasi WhatsApp Gateway untuk push notification stok kritis, jatuh tempo piutang, dan approval remote instan.</p>
          </div>
          <div class="glass-card">
            <div class="card-title emerald">📱 Fase Mobile Native</div>
            <p class="card-desc">Peluncuran aplikasi Android/iOS native khusus checker barcode scanner dan dashboard eksekutif pintar.</p>
          </div>
        </div>
      </div>

      <div class="slide-footer">
        <span>KARISMA ERP • <strong>PT. Karisma Indoagro Universal</strong></span>
        <span>Slide 12 / 12</span>
      </div>
    </section>

  </main>

  <!-- Keyboard Shortcuts & Controls Hint -->
  <div class="keyboard-hint">
    <span>Gunakan tombol panah keyboard:</span>
    <span class="kbd-key">◀ Kiri</span>
    <span class="kbd-key">Kanan ▶</span>
    <span class="kbd-key">Spasi</span>
    <span>atau tombol navigasi di atas untuk berpindah slide.</span>
  </div>

  <!-- Interactive Slide Navigation Script -->
  <script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;
    const counter = document.getElementById('slideCounter');
    const viewport = document.getElementById('viewport');

    function showSlide(index) {
      if (index < 0 || index >= totalSlides) return;
      currentSlide = index;
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === currentSlide);
      });
      counter.textContent = `Slide ${currentSlide + 1} / ${totalSlides}`;
    }

    function nextSlide() {
      if (currentSlide < totalSlides - 1) {
        showSlide(currentSlide + 1);
      }
    }

    function prevSlide() {
      if (currentSlide > 0) {
        showSlide(currentSlide - 1);
      }
    }

    function toggleFullscreen() {
      if (!document.fullscreenElement) {
        viewport.requestFullscreen().catch(err => {
          alert(`Gagal masuk mode layar penuh: ${err.message}`);
        });
      } else {
        document.exitFullscreen();
      }
    }

    // Keyboard Event Listener
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') {
        e.preventDefault();
        nextSlide();
      } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
        e.preventDefault();
        prevSlide();
      } else if (e.key === 'Home') {
        e.preventDefault();
        showSlide(0);
      } else if (e.key === 'End') {
        e.preventDefault();
        showSlide(totalSlides - 1);
      } else if (e.key === 'f' || e.key === 'F') {
        toggleFullscreen();
      }
    });

    // Touch Swipe Support for Mobile/Tablet
    let touchStartX = 0;
    let touchEndX = 0;

    viewport.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, false);

    viewport.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      if (touchStartX - touchEndX > 50) {
        nextSlide();
      } else if (touchEndX - touchStartX > 50) {
        prevSlide();
      }
    }, false);
  </script>
</body>
</html>
