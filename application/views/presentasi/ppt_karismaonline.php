<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KARISMA ONLINE - Presentasi Peluncuran Multi-Platform (iOS, Android, Web)</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root {
      --bg-primary: #080d1a;
      --bg-secondary: #0f172a;
      --bg-card: rgba(15, 23, 42, 0.75);
      --bg-card-hover: rgba(30, 41, 59, 0.85);
      --border-color: rgba(51, 65, 85, 0.5);
      --border-glow: rgba(16, 185, 129, 0.35);
      --emerald-glow: rgba(16, 185, 129, 0.15);
      --cyan-glow: rgba(6, 182, 212, 0.15);
      
      --accent-emerald: #10b981;
      --accent-emerald-light: #34d399;
      --accent-cyan: #06b6d4;
      --accent-cyan-light: #38bdf8;
      --accent-blue: #3b82f6;
      --accent-purple: #8b5cf6;
      --accent-amber: #f59e0b;
      --accent-rose: #f43f5e;
      
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --text-dim: #64748b;
      
      --font-title: 'Outfit', sans-serif;
      --font-body: 'Plus Jakarta Sans', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;
      
      --slide-transition: 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      -webkit-font-smoothing: antialiased;
    }

    body {
      background-color: var(--bg-primary);
      color: var(--text-main);
      font-family: var(--font-body);
      overflow: hidden;
      height: 100vh;
      width: 100vw;
      user-select: none;
    }

    /* Ambient Futuristic Background */
    .space-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      pointer-events: none;
      background: 
        radial-gradient(circle at 15% 15%, rgba(16, 185, 129, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 85% 80%, rgba(6, 182, 212, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 60%);
      overflow: hidden;
    }

    .grid-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
      background-size: 40px 40px;
      mask-image: radial-gradient(circle at center, black 40%, transparent 80%);
    }

    /* Header Nav */
    .deck-header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 70px;
      padding: 0 2.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 100;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      background: rgba(8, 13, 26, 0.8);
      backdrop-filter: blur(12px);
    }

    .brand-group {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .brand-badge {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(6, 182, 212, 0.15));
      border: 1px solid var(--border-glow);
      padding: 0.4rem 0.9rem;
      border-radius: 999px;
    }

    .brand-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--accent-emerald);
      box-shadow: 0 0 10px var(--accent-emerald);
      animation: pulse 2s infinite;
    }

    .brand-title {
      font-family: var(--font-title);
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: 0.05em;
      background: linear-gradient(to right, #ffffff, #94a3b8);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .brand-subtitle {
      font-size: 0.8rem;
      color: var(--accent-cyan);
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }

    .header-controls {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .btn-action {
      background: rgba(30, 41, 59, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-main);
      padding: 0.5rem 0.9rem;
      border-radius: 8px;
      font-size: 0.85rem;
      font-family: var(--font-body);
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      transition: all 0.2s ease;
    }

    .btn-action:hover {
      background: rgba(51, 65, 85, 0.8);
      border-color: var(--accent-cyan);
      color: #ffffff;
      transform: translateY(-1px);
    }

    .btn-action.active {
      background: var(--accent-emerald);
      color: #000;
      border-color: var(--accent-emerald-light);
      font-weight: 700;
    }

    /* Presentation Main Container */
    .deck-container {
      position: relative;
      width: 100vw;
      height: 100vh;
      padding-top: 70px;
      padding-bottom: 70px;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 10;
    }

    .slides-wrapper {
      position: relative;
      width: 90vw;
      max-width: 1400px;
      height: 82vh;
      perspective: 1000px;
    }

    .slide {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 24px;
      padding: 3rem;
      backdrop-filter: blur(20px);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);
      opacity: 0;
      visibility: hidden;
      transform: scale(0.96) translateY(20px);
      transition: all var(--slide-transition);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow-y: auto;
    }

    .slide.active {
      opacity: 1;
      visibility: visible;
      transform: scale(1) translateY(0);
      z-index: 20;
    }

    .slide.prev {
      opacity: 0;
      transform: scale(0.96) translateY(-20px);
    }

    /* Slide Elements */
    .slide-tag-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }

    .slide-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: var(--accent-emerald-light);
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.25);
      padding: 0.35rem 0.8rem;
      border-radius: 999px;
      font-weight: 600;
    }

    .slide-index-number {
      font-family: var(--font-mono);
      font-size: 0.85rem;
      color: var(--text-dim);
      font-weight: 600;
    }

    .slide-title {
      font-family: var(--font-title);
      font-size: 2.3rem;
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -0.02em;
      background: linear-gradient(135deg, #ffffff 40%, #94a3b8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 0.5rem;
    }

    .slide-subtitle {
      font-size: 1.05rem;
      color: var(--text-muted);
      max-width: 900px;
      line-height: 1.5;
      margin-bottom: 1.8rem;
    }

    .slide-content-area {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 1.5rem;
    }

    /* Grid Layouts */
    .grid-2 {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
    }

    .grid-4 {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }

    .grid-1-2 {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 1.75rem;
    }

    .grid-2-1 {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 1.75rem;
    }

    /* Cards & Components */
    .feature-card {
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .feature-card:hover {
      border-color: rgba(6, 182, 212, 0.4);
      background: rgba(30, 41, 59, 0.7);
      transform: translateY(-3px);
      box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.4);
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, var(--accent-emerald), var(--accent-cyan));
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .feature-card:hover::before {
      opacity: 1;
    }

    .card-icon-box {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent-emerald);
      margin-bottom: 1rem;
    }

    .card-icon-box.cyan {
      background: rgba(6, 182, 212, 0.1);
      border-color: rgba(6, 182, 212, 0.2);
      color: var(--accent-cyan);
    }

    .card-icon-box.purple {
      background: rgba(139, 92, 246, 0.1);
      border-color: rgba(139, 92, 246, 0.2);
      color: var(--accent-purple);
    }

    .card-icon-box.amber {
      background: rgba(245, 158, 11, 0.1);
      border-color: rgba(245, 158, 11, 0.2);
      color: var(--accent-amber);
    }

    .card-title {
      font-family: var(--font-title);
      font-size: 1.15rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 0.4rem;
    }

    .card-desc {
      font-size: 0.9rem;
      color: var(--text-muted);
      line-height: 1.5;
    }

    .card-list {
      list-style: none;
      margin-top: 0.75rem;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }

    .card-list li {
      font-size: 0.85rem;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card-list li i {
      color: var(--accent-emerald-light);
      font-size: 0.8rem;
    }

    /* Platform Pill Badge */
    .tech-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      padding: 0.25rem 0.6rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 6px;
      color: var(--text-main);
    }

    /* Hero Slide Specific */
    .hero-container {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 2.5rem;
      align-items: center;
      height: 100%;
    }

    .hero-badge-container {
      display: flex;
      gap: 0.6rem;
      flex-wrap: wrap;
      margin-bottom: 1.2rem;
    }

    .hero-title {
      font-family: var(--font-title);
      font-size: 3.2rem;
      font-weight: 900;
      line-height: 1.05;
      letter-spacing: -0.03em;
      margin-bottom: 1.2rem;
    }

    .hero-title span {
      background: linear-gradient(135deg, var(--accent-emerald) 0%, var(--accent-cyan-light) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .hero-desc {
      font-size: 1.1rem;
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 2rem;
    }

    .hero-stats-row {
      display: flex;
      gap: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hero-stat-item h3 {
      font-family: var(--font-title);
      font-size: 1.8rem;
      font-weight: 800;
      color: var(--accent-cyan-light);
    }

    .hero-stat-item p {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-dim);
    }

    /* Futuristic Device Mockup Box */
    .device-mockup-stack {
      position: relative;
      height: 420px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .mockup-card {
      position: absolute;
      background: linear-gradient(145deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.95));
      border: 1px solid var(--border-glow);
      border-radius: 20px;
      padding: 1.5rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(15px);
    }

    .mockup-main {
      width: 280px;
      z-index: 3;
      transform: rotate(-3deg) translateY(-10px);
      transition: transform 0.4s ease;
    }

    .mockup-main:hover {
      transform: rotate(0deg) translateY(-20px) scale(1.05);
    }

    .mockup-sub {
      width: 260px;
      right: 20px;
      z-index: 2;
      transform: rotate(6deg) translateY(20px);
      opacity: 0.85;
      border-color: rgba(6, 182, 212, 0.3);
    }

    .mockup-badge {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.75rem;
      font-family: var(--font-mono);
      color: var(--accent-emerald);
      margin-bottom: 0.75rem;
    }

    /* Architecture Flow Nodes */
    .node-tree {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      position: relative;
    }

    .node-box {
      flex: 1;
      background: rgba(15, 23, 42, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 1.25rem;
      text-align: center;
      position: relative;
    }

    .node-box.highlight {
      border-color: var(--accent-emerald);
      background: linear-gradient(180deg, rgba(16, 185, 129, 0.1), rgba(15, 23, 42, 0.8));
    }

    .node-arrow {
      color: var(--accent-cyan);
      font-size: 1.5rem;
    }

    /* Timeline / Lifecycle Steps */
    .timeline-steps {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 0.75rem;
      position: relative;
    }

    .timeline-step {
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      padding: 1.2rem 1rem;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .timeline-step.active-step {
      border-color: var(--accent-emerald);
      background: rgba(16, 185, 129, 0.08);
    }

    .step-number {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
    }

    .timeline-step.active-step .step-number {
      background: var(--accent-emerald);
      color: #000000;
    }

    .step-title {
      font-family: var(--font-title);
      font-size: 0.95rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 0.35rem;
    }

    .step-desc {
      font-size: 0.75rem;
      color: var(--text-muted);
      line-height: 1.4;
    }

    /* Footer / Progress Bar */
    .deck-footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 70px;
      padding: 0 2.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 100;
      background: rgba(8, 13, 26, 0.8);
      border-top: 1px solid rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(12px);
    }

    .footer-left {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .progress-bar-container {
      width: 260px;
      height: 6px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 999px;
      overflow: hidden;
    }

    .progress-bar-fill {
      height: 100%;
      width: 8.33%;
      background: linear-gradient(90deg, var(--accent-emerald), var(--accent-cyan));
      border-radius: 999px;
      transition: width 0.3s ease;
    }

    .slide-counter {
      font-family: var(--font-mono);
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 600;
    }

    .nav-buttons {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-nav {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      background: rgba(30, 41, 59, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: var(--text-main);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-nav:hover:not(:disabled) {
      background: var(--accent-emerald);
      color: #000000;
      border-color: var(--accent-emerald-light);
    }

    .btn-nav:disabled {
      opacity: 0.3;
      cursor: not-allowed;
    }


    /* Animations */
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(0.85); }
    }

    /* Role Matrix Tabs */
    .role-badge-row {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-top: 0.5rem;
    }

    .role-pill {
      font-size: 0.75rem;
      padding: 0.2rem 0.5rem;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #cbd5e1;
    }

    .role-pill.admin { border-color: var(--accent-rose); color: #fda4af; }
    .role-pill.sales { border-color: var(--accent-cyan); color: #7dd3fc; }
    .role-pill.dist { border-color: var(--accent-amber); color: #fde68a; }
    .role-pill.cust { border-color: var(--accent-emerald); color: #a7f3d0; }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.2);
    }
    ::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: var(--accent-cyan);
    }
  </style>
</head>
<body>

  <!-- Ambient Visual Background -->
  <div class="space-bg">
    <div class="grid-overlay"></div>
  </div>

  <!-- Header Navigation -->
  <header class="deck-header">
    <div class="brand-group">
      <div class="brand-badge">
        <div class="brand-dot"></div>
        <span class="brand-title">KARISMA ONLINE</span>
      </div>
      <span class="brand-subtitle">Platform Launch Presentation</span>
    </div>

    <div class="header-controls">
      <button class="btn-action" id="btnFullscreen" onclick="toggleFullscreen()">
        <i data-lucide="maximize"></i> Fullscreen
      </button>
    </div>
  </header>

  <!-- Presentation Main Container -->
  <main class="deck-container">
    <div class="slides-wrapper">

      <!-- SLIDE 1: HERO & TITLE -->
      <section class="slide active" data-slide="1">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="zap"></i> LAUNCH-READY MILESTONE
          </div>
          <span class="slide-index-number">01 / 12</span>
        </div>

        <div class="slide-content-area">
          <div class="hero-container">
            <div>
              <div class="hero-badge-container">
                <span class="tech-pill"><i data-lucide="apple"></i> iOS Swift Native</span>
                <span class="tech-pill"><i data-lucide="smartphone"></i> Android Kotlin & Java</span>
                <span class="tech-pill"><i data-lucide="globe"></i> CodeIgniter 3 Web</span>
                <span class="tech-pill"><i data-lucide="server"></i> REST API v1</span>
              </div>
              <h1 class="hero-title">
                Ekosistem Digital <span>Karisma Online</span>
              </h1>
              <p class="hero-desc">
                Solusi commerce dan distribusi pertanian terintegrasi multi-platform. Menghubungkan petani, toko mitra, salesman, logistik, dan manajemen dalam satu alur transaksi yang presisi, cepat, dan siap rilis di <strong>App Store</strong> &amp; <strong>Google Play</strong>.
              </p>
              <div class="hero-stats-row">
                <div class="hero-stat-item">
                  <h3>100%</h3>
                  <p>Launch Readiness</p>
                </div>
                <div class="hero-stat-item">
                  <h3>3 Channel</h3>
                  <p>iOS • Android • Web</p>
                </div>
                <div class="hero-stat-item">
                  <h3>6 Tahap</h3>
                  <p>Order Pipeline Terkontrol</p>
                </div>
                <div class="hero-stat-item">
                  <h3>Zero-PII</h3>
                  <p>Apple 5.1.1(v) Compliant</p>
                </div>
              </div>
            </div>

            <div class="device-mockup-stack">
              <div class="mockup-card mockup-sub">
                <div class="mockup-badge">
                  <i data-lucide="layout-dashboard"></i> Back-Office Portal
                </div>
                <h4 style="font-size: 0.95rem; margin-bottom: 0.4rem; color: #fff;">Admin &amp; Distribusi</h4>
                <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                  Validasi pesanan, persetujuan limit kredit, dispatch logistik, dan laporan performa real-time.
                </p>
              </div>

              <div class="mockup-card mockup-main">
                <div class="mockup-badge">
                  <i data-lucide="smartphone"></i> Customer Native App
                </div>
                <h3 style="font-family: var(--font-title); font-size: 1.2rem; margin-bottom: 0.5rem; color: #fff;">Karisma Customer</h3>
                <div style="background: rgba(0,0,0,0.3); padding: 0.75rem; border-radius: 10px; margin-bottom: 0.75rem;">
                  <div style="display:flex; justify-content:space-between; font-size: 0.8rem; margin-bottom: 0.25rem;">
                    <span>Status Order</span>
                    <span style="color: var(--accent-emerald-light); font-weight:700;">BRIVA Active</span>
                  </div>
                  <div style="font-family: var(--font-mono); font-size: 0.9rem; color: var(--accent-cyan-light);">9111887654321</div>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-muted);">
                  Belanja kebutuhan pertanian, katalog SKU, quick quote ongkir, dan invoice otomatis.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 2: RINGKASAN PLATFORM & EKOSISTEM -->
      <section class="slide" data-slide="2">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="layers"></i> ARSITEKTUR EKOSISTEM
          </div>
          <span class="slide-index-number">02 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Ekosistem Commerce Lintas Saluran</h2>
          <p class="slide-subtitle">
            Karisma Online menyatukan channel transaksi customer dengan sistem operasional backend enterprise sehingga seluruh order terkoordinasi rapi tanpa jeda manual.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-4">
            <div class="feature-card">
              <div class="card-icon-box">
                <i data-lucide="apple"></i>
              </div>
              <h3 class="card-title">Mobile iOS Native</h3>
              <p class="card-desc">Dibangun murni dengan Swift &amp; SwiftUI. Arsitektur MVVM, token Keychain aman, transisi mulus, dan lolos uji ketat App Store Review.</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Guest Browsing Mode</li>
                <li><i data-lucide="check"></i> Self-Service Account Delete</li>
                <li><i data-lucide="check"></i> Fastlane CI/CD Ready</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box cyan">
                <i data-lucide="smartphone"></i>
              </div>
              <h3 class="card-title">Mobile Android Native</h3>
              <p class="card-desc">Menggunakan Kotlin &amp; Java native. Ringan, efisien konsumsi memori di berbagai tipe perangkat petani &amp; kios agribisnis.</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Bearer Token Auth</li>
                <li><i data-lucide="check"></i> Offline Cache Resilient</li>
                <li><i data-lucide="check"></i> Google Play Compliant</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box purple">
                <i data-lucide="globe"></i>
              </div>
              <h3 class="card-title">Web Customer Portal</h3>
              <p class="card-desc">Portal web responsif berbasis CodeIgniter 3. Memudahkan pemesanan dalam jumlah besar lewat browser laptop/PC.</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Katalog Lengkap &amp; Promo</li>
                <li><i data-lucide="check"></i> Riwayat Invoice &amp; Mutasi</li>
                <li><i data-lucide="check"></i> Privacy Policy &amp; Terms</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box amber">
                <i data-lucide="shield-check"></i>
              </div>
              <h3 class="card-title">Back-Office Enterprise</h3>
              <p class="card-desc">Pusat kendali operasional multi-level: Admin Sales, Salesman Lapangan (SE), Logistik Distribusi, dan Keuangan.</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Verifikasi Pesanan &amp; Stok</li>
                <li><i data-lucide="check"></i> Packing &amp; Resi Pengiriman</li>
                <li><i data-lucide="check"></i> Mode Isolasi Akun Internal</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 3: STACK TEKNOLOGI & CORE API -->
      <section class="slide" data-slide="3">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="cpu"></i> TECH STACK &amp; BACKBONE
          </div>
          <span class="slide-index-number">03 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Spesifikasi Teknologi &amp; Integrasi API</h2>
          <p class="slide-subtitle">
            Kombinasi teknologi native di sisi mobile dan modular HMVC di backend memastikan stabilitas, kecepatan eksekusi, serta skalabilitas transaksi.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-2">
            <div class="feature-card">
              <h3 class="card-title" style="display:flex; align-items:center; gap:0.5rem;">
                <i data-lucide="code" style="color:var(--accent-emerald);"></i> Frontend &amp; Client Architecture
              </h3>
              <div style="margin-top: 1rem; display:flex; flex-direction:column; gap:0.8rem;">
                <div style="background: rgba(0,0,0,0.25); padding: 0.8rem; border-radius: 10px;">
                  <strong style="color:var(--accent-emerald-light);">iOS Native:</strong>
                  <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    SwiftUI + MVVM Pattern, Service Protocol Layer, Combine/Async-Await, Keychain Session Storage, Zero third-party bloat.
                  </p>
                </div>
                <div style="background: rgba(0,0,0,0.25); padding: 0.8rem; border-radius: 10px;">
                  <strong style="color:var(--accent-cyan-light);">Android Native:</strong>
                  <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    Kotlin &amp; Java Native, Material Design Components, Retrofit REST Client, Strict Permission &amp; Lifecycle Management.
                  </p>
                </div>
                <div style="background: rgba(0,0,0,0.25); padding: 0.8rem; border-radius: 10px;">
                  <strong style="color:var(--accent-purple);">Web Browser:</strong>
                  <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    CodeIgniter 3 HMVC, Bootstrap, jQuery/Selectize, Responsive Layout optimized for all screens.
                  </p>
                </div>
              </div>
            </div>

            <div class="feature-card">
              <h3 class="card-title" style="display:flex; align-items:center; gap:0.5rem;">
                <i data-lucide="server" style="color:var(--accent-cyan);"></i> Backend &amp; Unified REST API v1
              </h3>
              <div style="margin-top: 1rem; display:flex; flex-direction:column; gap:0.8rem;">
                <div style="background: rgba(0,0,0,0.25); padding: 0.8rem; border-radius: 10px;">
                  <strong style="color:var(--accent-cyan-light);">Modular API Endpoint (`/api/v1`):</strong>
                  <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    Auth, Catalog, Cart (Server DB), Dynamic Shipping Quotes, Orders Checkout, BRIVA Gateway, Messages, and Account Deletion.
                  </p>
                </div>
                <div style="background: rgba(0,0,0,0.25); padding: 0.8rem; border-radius: 10px;">
                  <strong style="color:var(--accent-amber);">Keamanan &amp; Database:</strong>
                  <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    Bearer Token Authorization, SHA-256 Hash Auditing, Dedicated Mobile Tables (`mobile_api_tokens`, `mobile_cart_items`, `mobile_account_deletions`).
                  </p>
                </div>
                <div style="background: rgba(0,0,0,0.25); padding: 0.8rem; border-radius: 10px;">
                  <strong style="color:var(--accent-emerald);">Gateways &amp; Integrasi Eksternal:</strong>
                  <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">
                    BRI Virtual Account (`Brivaws` library real-time create/inquiry) &amp; RajaOngkir Sub-district Level Courier Calculation.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 4: KESIAPAN STORE (APP STORE & GOOGLE PLAY) -->
      <section class="slide" data-slide="4">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="rocket"></i> STORE COMPLIANCE &amp; LAUNCH
          </div>
          <span class="slide-index-number">04 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Kesiapan Rilis: App Store &amp; Google Play</h2>
          <p class="slide-subtitle">
            Seluruh parameter kepatuhan toko aplikasi (App Store Review Guidelines &amp; Play Console Policies) telah dipenuhi dan divalidasi.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-3">
            <div class="feature-card">
              <div class="card-icon-box">
                <i data-lucide="shield-alert"></i>
              </div>
              <h3 class="card-title">Apple Review Resolution</h3>
              <p class="card-desc">Penyelesaian resmi atas 4 catatan Apple Review terdahulu:</p>
              <ul class="card-list">
                <li><i data-lucide="check-circle"></i> <strong>Apple 5.1.1(v):</strong> Hapus Akun in-app + API penghapusan PII otomatis.</li>
                <li><i data-lucide="check-circle"></i> <strong>Guest Browsing:</strong> Akses katalog tanpa wajib login terlebih dahulu.</li>
                <li><i data-lucide="check-circle"></i> <strong>Navigation Fix:</strong> Tombol Tutup &amp; Dismiss di semua sheet/modal.</li>
                <li><i data-lucide="check-circle"></i> <strong>Metadata:</strong> Screenshot 6.5" &amp; rekaman device fisik.</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box cyan">
                <i data-lucide="lock"></i>
              </div>
              <h3 class="card-title">Security &amp; Production Guard</h3>
              <p class="card-desc">Perlindungan level industri sebelum archive release:</p>
              <ul class="card-list">
                <li><i data-lucide="check-circle"></i> <strong>HTTPS Mandatory:</strong> Penolakan koneksi HTTP tidak aman pada mode production.</li>
                <li><i data-lucide="check-circle"></i> <strong>Mock Isolation:</strong> Mock state hanya aktif pada Unit Test &amp; Preview.</li>
                <li><i data-lucide="check-circle"></i> <strong>Privacy Manifest:</strong> `PrivacyInfo.xcprivacy` terintegrasi lengkap.</li>
                <li><i data-lucide="check-circle"></i> <strong>Keychain Access:</strong> Penyimpanan token terenkripsi hardware.</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box purple">
                <i data-lucide="terminal"></i>
              </div>
              <h3 class="card-title">Automated Build &amp; Deploy</h3>
              <p class="card-desc">Pipeline otomasi pengiriman aplikasi:</p>
              <ul class="card-list">
                <li><i data-lucide="check-circle"></i> <strong>Bundle ID:</strong> `com.karismaonline.kiustore` terdaftar.</li>
                <li><i data-lucide="check-circle"></i> <strong>Fastlane Integration:</strong> Satu perintah untuk build beta/archive.</li>
                <li><i data-lucide="check-circle"></i> <strong>App Store Connect API:</strong> Upload otomatis via AuthKey `.p8`.</li>
                <li><i data-lucide="check-circle"></i> <strong>Google Play Track:</strong> Siap upload bundle AAB ke production.</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 5: FITUR UNGGULAN CUSTOMER -->
      <section class="slide" data-slide="5">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="shopping-bag"></i> CUSTOMER JOURNEY
          </div>
          <span class="slide-index-number">05 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Pengalaman Belanja Customer Modern</h2>
          <p class="slide-subtitle">
            Antarmuka mobile dirancang khusus untuk kenyamanan pemilik toko pertanian dan petani dalam mencari produk, membandingkan harga, dan memesan barang.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-2-1">
            <div class="grid-2">
              <div class="feature-card">
                <div class="card-icon-box">
                  <i data-lucide="search"></i>
                </div>
                <h3 class="card-title">Katalog &amp; Smart Search</h3>
                <p class="card-desc">Pencarian instan berdasarkan nama produk atau kode SKU. Filter kategori pupuk, benih, insektisida, fungisida, dan produk promo khusus.</p>
              </div>

              <div class="feature-card">
                <div class="card-icon-box cyan">
                  <i data-lucide="shopping-cart"></i>
                </div>
                <h3 class="card-title">Keranjang Multi-Satuan</h3>
                <p class="card-desc">Mendukung pembelian satuan eceran (botol/pack) maupun grosir (dus/karton) dengan kalkulasi harga bertingkat otomatis.</p>
              </div>

              <div class="feature-card">
                <div class="card-icon-box amber">
                  <i data-lucide="credit-card"></i>
                </div>
                <h3 class="card-title">Limit Kredit &amp; Tagihan</h3>
                <p class="card-desc">Widget ringkasan finansial di beranda untuk customer terpilih yang memiliki fasilitas tempo/kredit dan riwayat jatuh tempo.</p>
              </div>

              <div class="feature-card">
                <div class="card-icon-box purple">
                  <i data-lucide="message-square"></i>
                </div>
                <h3 class="card-title">Direct Chat Support</h3>
                <p class="card-desc">Fitur pesan interaktif langsung dari aplikasi ke tim support Karisma untuk konsultasi produk dan konfirmasi pengiriman.</p>
              </div>
            </div>

            <div class="feature-card" style="display:flex; flex-direction:column; justify-content:space-between; background: linear-gradient(180deg, rgba(16, 185, 129, 0.08), rgba(15, 23, 42, 0.8));">
              <div>
                <div class="card-icon-box" style="background: rgba(16,185,129,0.2);">
                  <i data-lucide="sparkles"></i>
                </div>
                <h3 class="card-title" style="font-size:1.25rem;">Guest Browsing Experience</h3>
                <p class="card-desc" style="margin-top:0.5rem;">
                  Calon pembeli dapat melihat seluruh etalase produk, promo aktif, dan deskripsi teknis tanpa registrasi di awal.
                </p>
                <div style="margin-top:1.25rem; border-top:1px solid rgba(255,255,255,0.08); padding-top:1rem;">
                  <div style="font-size:0.8rem; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.05em;">Keunggulan Konversi:</div>
                  <div style="font-size:0.9rem; color:#fff; font-weight:600; margin-top:0.25rem;">Mengurangi friksi awal hingga 70% dan meningkatkan adopsi pengguna baru.</div>
                </div>
              </div>
              <div style="background:rgba(0,0,0,0.3); padding:0.8rem; border-radius:10px; font-size:0.8rem; color:var(--accent-emerald-light);">
                <i data-lucide="check"></i> Login hanya diminta saat checkout atau mengakses data privat.
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 6: ALUR TRANSAKSI & PAYMENT GATEWAY -->
      <section class="slide" data-slide="6">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="credit-card"></i> PAYMENT &amp; LOGISTICS
          </div>
          <span class="slide-index-number">06 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Sistem Checkout, Ongkir, &amp; Payment Gateway</h2>
          <p class="slide-subtitle">
            Kombinasi kalkulasi ongkir presisi tingkat kecamatan dan integrasi Virtual Account otomatis BRI (BRIVA) menghadirkan kepastian pembayaran.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-3">
            <div class="feature-card">
              <div class="card-icon-box">
                <i data-lucide="truck"></i>
              </div>
              <h3 class="card-title">Dynamic RajaOngkir</h3>
              <p class="card-desc">Perhitungan ongkir akurat multi-kurir (JNE, J&amp;T, SiCepat) berdasarkan berat aktual barang di keranjang.</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Hirarki: Provinsi &gt; Kota &gt; Kecamatan</li>
                <li><i data-lucide="check"></i> Quote token dengan validitas 30 menit</li>
                <li><i data-lucide="check"></i> Mencegah selisih ongkos kirim</li>
              </ul>
            </div>

            <div class="feature-card" style="border-color: rgba(6, 182, 212, 0.4);">
              <div class="card-icon-box cyan">
                <i data-lucide="credit-card"></i>
              </div>
              <h3 class="card-title">Virtual Account BRI (BRIVA)</h3>
              <p class="card-desc">Integrasi langsung dengan core API Bank BRI untuk verifikasi pembayaran tanpa cek mutasi manual.</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> VA Code instan (prefix `91118`)</li>
                <li><i data-lucide="check"></i> Countdown expired timer 15 menit</li>
                <li><i data-lucide="check"></i> Polling status otomatis ke aplikasi</li>
                <li><i data-lucide="check"></i> Auto-switch status order ke 'Siap Kirim'</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box amber">
                <i data-lucide="file-check"></i>
              </div>
              <h3 class="card-title">Transfer Bank &amp; Bukti Bayar</h3>
              <p class="card-desc">Alternatif pembayaran transfer rekening resmi dengan pengunggahan bukti bayar langsung di aplikasi.</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Pilihan rekening bank resmi Karisma</li>
                <li><i data-lucide="check"></i> Upload bukti transfer via base64/file</li>
                <li><i data-lucide="check"></i> Masuk antrean review Admin Keuangan</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 7: HIERARKI ROLE & OPERASIONAL BACK-OFFICE -->
      <section class="slide" data-slide="7">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="users"></i> MULTI-ROLE GOVERNANCE
          </div>
          <span class="slide-index-number">07 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Hierarki Role &amp; Tata Kelola Operasional</h2>
          <p class="slide-subtitle">
            Sistem Karisma Online membagi wewenang secara ketat berdasarkan tugas pokok dan fungsi (tupoksi) kerja untuk menjaga akuntabilitas.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-2">
            <div class="feature-card">
              <h3 class="card-title" style="color:var(--accent-cyan); display:flex; align-items:center; gap:0.5rem;">
                <i data-lucide="shopping-bag"></i> Sisi Pelanggan &amp; Penjualan Lapangan
              </h3>
              <div style="margin-top:1rem; display:flex; flex-direction:column; gap:0.75rem;">
                <div style="background:rgba(0,0,0,0.25); padding:0.75rem; border-radius:8px;">
                  <span class="role-pill cust">Customer</span>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;">
                    Pencarian produk, keranjang, order, pilihan kurir, bayar BRIVA/transfer, invoice, tracking, &amp; chat.
                  </p>
                </div>
                <div style="background:rgba(0,0,0,0.25); padding:0.75rem; border-radius:8px;">
                  <span class="role-pill sales">Salesman (SE)</span>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;">
                    Monitoring pelanggan di wilayah binaan, pendampingan order, penagihan, &amp; follow-up kepuasan layanan.
                  </p>
                </div>
                <div style="background:rgba(0,0,0,0.25); padding:0.75rem; border-radius:8px;">
                  <span class="role-pill sales">Admin Sales</span>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;">
                    Validasi order baru (status 1), konfirmasi stok &amp; harga, penerbitan invoice pembayaran (status 2).
                  </p>
                </div>
              </div>
            </div>

            <div class="feature-card">
              <h3 class="card-title" style="color:var(--accent-amber); display:flex; align-items:center; gap:0.5rem;">
                <i data-lucide="truck"></i> Sisi Logistik, Keuangan &amp; Manajemen
              </h3>
              <div style="margin-top:1rem; display:flex; flex-direction:column; gap:0.75rem;">
                <div style="background:rgba(0,0,0,0.25); padding:0.75rem; border-radius:8px;">
                  <span class="role-pill dist">Distribusi / Logistik</span>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;">
                    Antrean packing barang, pencetakan shipping label, penginputan nomor resi, &amp; monitoring deliver.
                  </p>
                </div>
                <div style="background:rgba(0,0,0,0.25); padding:0.75rem; border-radius:8px;">
                  <span class="role-pill admin">Keuangan / Finance</span>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;">
                    Rekonsiliasi mutasi BRIVA, verifikasi bukti transfer bank manual, &amp; manajemen piutang/tempo.
                  </p>
                </div>
                <div style="background:rgba(0,0,0,0.25); padding:0.75rem; border-radius:8px;">
                  <span class="role-pill admin">Super Admin &amp; Kadep</span>
                  <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.25rem;">
                    Manajemen master data, hak akses akun, laporan penjualan eksekutif, &amp; konfigurasi akun internal testing.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 8: ORDER LIFECYCLE 6 TAHAP -->
      <section class="slide" data-slide="8">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="git-commit"></i> END-TO-END WORKFLOW
          </div>
          <span class="slide-index-number">08 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Siklus Hidup Transaksi (6 Tahap Presisi)</h2>
          <p class="slide-subtitle">
            Alur transaksi dirancang dengan kontrol approval di awal sebelum customer melakukan pembayaran untuk mencegah ketidaksesuaian stok fisik dan harga.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="timeline-steps">
            <div class="timeline-step active-step">
              <div class="step-number">1</div>
              <div>
                <h4 class="step-title">Order Placed</h4>
                <p class="step-desc">Customer checkout dari aplikasi. Status: <strong>Menunggu Diproses</strong>.</p>
              </div>
            </div>

            <div class="timeline-step active-step">
              <div class="step-number">2</div>
              <div>
                <h4 class="step-title">Approval Sales</h4>
                <p class="step-desc">Admin sales memverifikasi stok. Status berubah: <strong>Menunggu Pembayaran</strong>.</p>
              </div>
            </div>

            <div class="timeline-step active-step">
              <div class="step-number">3</div>
              <div>
                <h4 class="step-title">Payment Paid</h4>
                <p class="step-desc">Customer bayar via BRIVA / Transfer. Status: <strong>Pembayaran Diterima</strong>.</p>
              </div>
            </div>

            <div class="timeline-step active-step">
              <div class="step-number">4</div>
              <div>
                <h4 class="step-title">Packing Gudang</h4>
                <p class="step-desc">Tim logistik menyiapkan barang &amp; kemasan. Status: <strong>Diproses Packing</strong>.</p>
              </div>
            </div>

            <div class="timeline-step active-step">
              <div class="step-number">5</div>
              <div>
                <h4 class="step-title">Pengiriman &amp; Resi</h4>
                <p class="step-desc">Input nomor resi kurir / armada Karisma. Status: <strong>Dalam Pengiriman</strong>.</p>
              </div>
            </div>

            <div class="timeline-step active-step">
              <div class="step-number">6</div>
              <div>
                <h4 class="step-title">Order Selesai</h4>
                <p class="step-desc">Barang diterima pembeli. Customer memberikan rating evaluasi layanan sales.</p>
              </div>
            </div>
          </div>

          <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid var(--border-glow); border-radius: 12px; padding: 1rem 1.5rem; display:flex; align-items:center; gap:1rem;">
            <i data-lucide="info" style="color:var(--accent-emerald); font-size:1.5rem; flex-shrink:0;"></i>
            <p style="font-size:0.88rem; color:#e2e8f0; line-height:1.5;">
              <strong>Keunggulan Logika Bisnis:</strong> Aplikasi mobile mengunci keranjang (single active order) selama transaksi berjalan guna menjamin alokasi stok dan menghindari pesanan ganda yang tidak terselesaikan.
            </p>
          </div>
        </div>
      </section>

      <!-- SLIDE 9: PRIVASI & KEAMANAN (APPLE 5.1.1(v)) -->
      <section class="slide" data-slide="9">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="shield-check"></i> PRIVACY &amp; COMPLIANCE
          </div>
          <span class="slide-index-number">09 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Kepatuhan Privasi Data &amp; Regulasi Store</h2>
          <p class="slide-subtitle">
            Implementasi penuh hak privasi pengguna (Right to Erasure) sesuai standar ketat Apple App Store dan Google Play Data Safety.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-2">
            <div class="feature-card">
              <div class="card-icon-box">
                <i data-lucide="user-x"></i>
              </div>
              <h3 class="card-title">Alur Hapus Akun Mandiri (In-App)</h3>
              <p class="card-desc">Sesuai Apple Guideline 5.1.1(v), customer dapat menghapus akun secara instan melalui menu <code>Profil &gt; Pengaturan &gt; Hapus Akun</code>.</p>
              <div style="margin-top: 1rem; background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 10px; font-size: 0.85rem;">
                <div style="color: var(--accent-emerald-light); font-weight:700; margin-bottom: 0.3rem;">Proses Otomatisasi Backend:</div>
                <ul style="list-style:none; display:flex; flex-direction:column; gap:0.35rem; color:var(--text-muted);">
                  <li>1. Semua Bearer Token di <code>mobile_api_tokens</code> di-revoke seketika.</li>
                  <li>2. Data keranjang &amp; quote ongkir aktif dibersihkan.</li>
                  <li>3. Data identitas di <code>customers</code> &amp; <code>users</code> dianonimkan (anonymized).</li>
                  <li>4. Histori transaksi non-PII tetap tersimpan untuk integritas pembukuan finansial.</li>
                  <li>5. Audit trail disimpan dalam bentuk <strong>SHA-256 Email Hash</strong> di <code>mobile_account_deletions</code>.</li>
                </ul>
              </div>
            </div>

            <div class="feature-card">
              <div class="card-icon-box purple">
                <i data-lucide="filter"></i>
              </div>
              <h3 class="card-title">Isolasi Akun Internal (Anti-Polusi KPI)</h3>
              <p class="card-desc">Fitur khusus untuk pengujian sistem (UAT), training staf baru, dan review akun demo Apple tanpa merusak laporan bisnis nyata.</p>
              <div style="margin-top: 1rem; background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 10px; font-size: 0.85rem;">
                <div style="color: var(--accent-cyan-light); font-weight:700; margin-bottom: 0.3rem;">Mekanisme Audit Terpisah:</div>
                <ul style="list-style:none; display:flex; flex-direction:column; gap:0.35rem; color:var(--text-muted);">
                  <li>• Akun test ditandai dengan flag <code>is_internal_account = 1</code> di database.</li>
                  <li>• Seluruh transaksi test dieksklusikan dari Laporan Penjualan Utama &amp; KPI Sales.</li>
                  <li>• Jejak audit tetap terekam lengkap di sistem tanpa mengorbankan akurasi data finansial.</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 10: QUALITY ASSURANCE & TESTING -->
      <section class="slide" data-slide="10">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="check-square"></i> QUALITY ASSURANCE
          </div>
          <span class="slide-index-number">10 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Kualitas Kode, Unit Testing &amp; UAT</h2>
          <p class="slide-subtitle">
            Aplikasi telah melalui pengujian menyeluruh (Unit Test, Integration Test, dan skenario UAT role-based) untuk menjamin keandalan saat trafik tinggi.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-3">
            <div class="feature-card">
              <div class="card-icon-box">
                <i data-lucide="code"></i>
              </div>
              <h3 class="card-title">iOS Unit Test Suite</h3>
              <p class="card-desc">Pengujian otomatis komponen krusial pada project iOS:</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> <code>AuthViewModelTests</code> (Login, Register, Delete)</li>
                <li><i data-lucide="check"></i> <code>CartViewModelTests</code> (Price, Unit, Stock)</li>
                <li><i data-lucide="check"></i> <code>CheckoutViewModelTests</code> (Quote, Courier)</li>
                <li><i data-lucide="check"></i> <code>OrderViewModelTests</code> (Invoice, BRIVA)</li>
                <li><i data-lucide="check"></i> <code>ProductionSafetyTests</code> (HTTPS Guard)</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box cyan">
                <i data-lucide="activity"></i>
              </div>
              <h3 class="card-title">Backend API Verification</h3>
              <p class="card-desc">Validasi performa dan respon endpoint CodeIgniter 3:</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Response code standard (200, 401, 409, 422)</li>
                <li><i data-lucide="check"></i> JSON schema validation untuk mobile</li>
                <li><i data-lucide="check"></i> BRIVA API sandbox retry &amp; error handling</li>
                <li><i data-lucide="check"></i> Upload bukti bayar max-size &amp; MIME validation</li>
              </ul>
            </div>

            <div class="feature-card">
              <div class="card-icon-box amber">
                <i data-lucide="user-check"></i>
              </div>
              <h3 class="card-title">Role-Based UAT Matrix</h3>
              <p class="card-desc">Skenario uji penerimaan pengguna lintas peran:</p>
              <ul class="card-list">
                <li><i data-lucide="check"></i> Skenario Pembeli: Dari browse hingga invoice.</li>
                <li><i data-lucide="check"></i> Skenario Sales: Konfirmasi pesanan &amp; tracking.</li>
                <li><i data-lucide="check"></i> Skenario Logistik: Cetak packing &amp; input resi.</li>
                <li><i data-lucide="check"></i> Skenario Finance: Validasi VA &amp; transfer bank.</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 11: ROADMAP & PENGEMBANGAN LANJUTAN -->
      <section class="slide" data-slide="11">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="compass"></i> NEXT SPRINT ROADMAP
          </div>
          <span class="slide-index-number">11 / 12</span>
        </div>

        <div>
          <h2 class="slide-title">Rencana Pengembangan &amp; Peningkatan Skala</h2>
          <p class="slide-subtitle">
            Setelah fase peluncuran awal, pengembangan difokuskan pada peningkatan interaksi pengguna, otomasi notifikasi, dan perluasan fitur sales lapangan.
          </p>
        </div>

        <div class="slide-content-area">
          <div class="grid-4">
            <div class="feature-card">
              <div class="card-icon-box">
                <i data-lucide="bell"></i>
              </div>
              <h3 class="card-title">Push Notifications</h3>
              <p class="card-desc">Integrasi Apple APNs dan Firebase FCM untuk notifikasi update status resi pengiriman, konfirmasi bayar, dan promo flash sale.</p>
            </div>

            <div class="feature-card">
              <div class="card-icon-box cyan">
                <i data-lucide="briefcase"></i>
              </div>
              <h3 class="card-title">Sales Companion App</h3>
              <p class="card-desc">Pengembangan modul mobile native khusus salesman lapangan untuk input order langsung saat berkunjung ke kios pertanian mitra.</p>
            </div>

            <div class="feature-card">
              <div class="card-icon-box purple">
                <i data-lucide="pie-chart"></i>
              </div>
              <h3 class="card-title">Advanced Analytics</h3>
              <p class="card-desc">Dashboard analitik prediksi tren kebutuhan pupuk &amp; benih berdasarkan siklus musim tanam dan wilayah geografis.</p>
            </div>

            <div class="feature-card">
              <div class="card-icon-box amber">
                <i data-lucide="heart"></i>
              </div>
              <h3 class="card-title">Loyalty &amp; Point Reward</h3>
              <p class="card-desc">Program poin reward dan diskon berkala bagi toko mitra aktif guna meningkatkan retensi dan volume transaksi tahunan.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- SLIDE 12: KESIMPULAN & GO-LIVE MILESTONE -->
      <section class="slide" data-slide="12">
        <div class="slide-tag-row">
          <div class="slide-badge">
            <i data-lucide="award"></i> CONCLUSION &amp; LAUNCH
          </div>
          <span class="slide-index-number">12 / 12</span>
        </div>

        <div class="slide-content-area">
          <div style="text-align: center; max-width: 900px; margin: 0 auto;">
            <div class="brand-badge" style="display:inline-flex; margin-bottom: 1.5rem; padding: 0.5rem 1.2rem;">
              <i data-lucide="check-circle-2" style="color:var(--accent-emerald);"></i>
              <span style="font-weight: 700; color: #fff;">READY FOR PRODUCTION GO-LIVE</span>
            </div>
            
            <h1 style="font-family: var(--font-title); font-size: 2.8rem; font-weight: 900; line-height: 1.15; margin-bottom: 1rem;">
              Karisma Online Siap Menjadi Motor Digitalisasi Pertanian
            </h1>
            
            <p style="font-size: 1.1rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem;">
              Dengan integrasi multi-platform (iOS Swift, Android Kotlin/Java, dan Web CodeIgniter 3), seluruh rantai perdagangan agribisnis Karisma kini berjalan lebih cepat, transparan, dan siap dinikmati pelanggan di seluruh Indonesia.
            </p>

            <div class="grid-3" style="margin-bottom: 2rem; text-align: left;">
              <div style="background: rgba(0,0,0,0.3); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
                <div style="color:var(--accent-emerald-light); font-weight:700; font-size:1.1rem; margin-bottom:0.3rem;">App Store Ready</div>
                <p style="font-size:0.85rem; color:var(--text-muted);">Sesuai pedoman Apple 5.1.1(v), guest browsing, dan metadata rilis lengkap.</p>
              </div>

              <div style="background: rgba(0,0,0,0.3); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
                <div style="color:var(--accent-cyan-light); font-weight:700; font-size:1.1rem; margin-bottom:0.3rem;">Google Play Ready</div>
                <p style="font-size:0.85rem; color:var(--text-muted);">Build native Android performa tinggi siap didistribusikan ke publik.</p>
              </div>

              <div style="background: rgba(0,0,0,0.3); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
                <div style="color:var(--accent-amber); font-weight:700; font-size:1.1rem; margin-bottom:0.3rem;">Back-Office Ready</div>
                <p style="font-size:0.85rem; color:var(--text-muted);">Manajemen sales, logistik distribusi, dan BRIVA siap operasional penuh.</p>
              </div>
            </div>

            <div style="display:inline-flex; align-items:center; gap:0.5rem; font-family:var(--font-mono); font-size:0.85rem; color:var(--text-dim);">
              <i data-lucide="calendar"></i> Target Peluncuran: Q3 2026 • Karisma Online Development Team
            </div>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- Footer Navigation -->
  <footer class="deck-footer">
    <div class="footer-left">
      <div class="progress-bar-container">
        <div class="progress-bar-fill" id="progressBar"></div>
      </div>
      <div class="slide-counter" id="slideCounter">Slide 1 / 12</div>
    </div>

    <div class="nav-buttons">
      <button class="btn-nav" id="btnPrev" onclick="prevSlide()" title="Slide Sebelumnya (Panah Kiri)">
        <i data-lucide="chevron-left"></i>
      </button>
      <button class="btn-nav" id="btnNext" onclick="nextSlide()" title="Slide Berikutnya (Panah Kanan / Spasi)">
        <i data-lucide="chevron-right"></i>
      </button>
    </div>
  </footer>

  <script>
    let currentSlide = 1;
    const totalSlides = 12;

    function updateSlide() {
      document.querySelectorAll('.slide').forEach((slide) => {
        const slideIndex = parseInt(slide.getAttribute('data-slide'));
        slide.classList.remove('active', 'prev');
        if (slideIndex === currentSlide) {
          slide.classList.add('active');
        } else if (slideIndex < currentSlide) {
          slide.classList.add('prev');
        }
      });

      // Update progress bar & counter
      const progressPercent = (currentSlide / totalSlides) * 100;
      document.getElementById('progressBar').style.width = progressPercent + '%';
      document.getElementById('slideCounter').textContent = `Slide ${currentSlide} / ${totalSlides}`;

      // Update nav buttons
      document.getElementById('btnPrev').disabled = currentSlide === 1;
      document.getElementById('btnNext').disabled = currentSlide === totalSlides;

      // Re-initialize Lucide Icons for dynamic content
      lucide.createIcons();
    }

    function nextSlide() {
      if (currentSlide < totalSlides) {
        currentSlide++;
        updateSlide();
      }
    }

    function prevSlide() {
      if (currentSlide > 1) {
        currentSlide--;
        updateSlide();
      }
    }

    function toggleFullscreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch((err) => {
          alert(`Error fullscreen: ${err.message}`);
        });
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        }
      }
    }

    // Keyboard Navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') {
        e.preventDefault();
        nextSlide();
      } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
        e.preventDefault();
        prevSlide();
      } else if (e.key.toLowerCase() === 'f') {
        e.preventDefault();
        toggleFullscreen();
      } else if (e.key === 'Home') {
        currentSlide = 1;
        updateSlide();
      } else if (e.key === 'End') {
        currentSlide = totalSlides;
        updateSlide();
      }
    });

    // Touch / Swipe Navigation
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      if (touchStartX - touchEndX > 50) {
        nextSlide();
      } else if (touchEndX - touchStartX > 50) {
        prevSlide();
      }
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => {
      updateSlide();
      lucide.createIcons();
    });
  </script>
</body>
</html>
