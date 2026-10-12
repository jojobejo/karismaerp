<body class="hold-transition layout-top-nav modern-survey-body">
    <div class="wrapper">

        <!-- Ambient Background Lights & Gradient -->
        <div class="ambient-glow glow-1"></div>
        <div class="ambient-glow glow-2"></div>
        <div class="ambient-glow glow-3"></div>

        <!-- Canvas for Confetti Fireworks -->
        <canvas id="confettiCanvas"></canvas>

        <!-- Kiosk Main Container -->
        <div class="survey-kiosk-container">

            <!-- Kiosk Header -->
            <header class="survey-header">
                <div class="header-branding">
                    <div class="brand-logo-card">
                        <img src="<?= base_url('assets/images/Karisma.png?v=' . (file_exists(FCPATH . 'assets/images/Karisma.png') ? filemtime(FCPATH . 'assets/images/Karisma.png') : '1')) ?>" alt="PT Karisma Indoagro Universal" class="img-fluid brand-img">
                        <div class="brand-divider"></div>
                        <img src="<?= base_url('assets/images/pelayanan_prima_official.png?v=' . (file_exists(FCPATH . 'assets/images/pelayanan_prima_official.png') ? filemtime(FCPATH . 'assets/images/pelayanan_prima_official.png') : '1')) ?>" alt="Pelayanan Prima" class="img-fluid brand-img-prima" title="Pelayanan Prima - Cepat, Santun & Sigap">
                    </div>
                    <div class="brand-info text-white">
                        <h5 class="brand-title mb-0">PT KARISMA INDOAGRO UNIVERSAL</h5>
                        <span class="brand-tagline">Pelayanan Cepat, Santun & Sigap</span>
                    </div>
                </div>

                <div class="header-controls">
                    <!-- Live Digital Clock & Date -->
                    <div class="live-clock-card text-white shadow-sm">
                        <div class="clock-time" id="liveClockTime">00:00:00 WIB</div>
                        <div class="clock-date" id="liveClockDate"><?= format_indo(date('Y-m-d')) ?></div>
                    </div>

                    <!-- Audio Sound Toggle -->
                    <button type="button" id="btnToggleSound" class="kiosk-ctrl-btn shadow-sm" title="Aktifkan/Nonaktifkan Suara" onclick="toggleSound()">
                        <i class="fas fa-volume-up" id="soundIcon"></i>
                    </button>

                    <!-- Fullscreen Kiosk Toggle -->
                    <button type="button" id="btnToggleFullscreen" class="kiosk-ctrl-btn shadow-sm" title="Mode Layar Penuh" onclick="toggleFullScreen()">
                        <i class="fas fa-expand" id="fsIcon"></i>
                    </button>
                </div>
            </header>

            <!-- Survey Hero Section -->
            <main class="survey-main-content">
                <div class="hero-badge-wrap">
                    <span class="hero-pill-badge shadow-sm">
                        <i class="fas fa-heart text-danger mr-2 animate-beat"></i>
                        SURVEI INDEKS KEPUASAN PELANGGAN
                    </span>
                </div>

                <h1 class="survey-hero-title">
                    Seberapa Puas Anda dengan Pelayanan Kami?
                </h1>
                
                <p class="survey-hero-subtitle" id="dynamicSubtitle">
                    Sentuh salah satu emotikon di bawah untuk memberikan penilaian Anda
                </p>

                <!-- 5 Interactive Creative Rating Cards -->
                <div class="rating-cards-grid">

                    <!-- RATING 1: SANGAT TIDAK PUAS -->
                    <div class="rating-card-col" onmouseenter="setHoverSubtitle(1)" onmouseleave="resetHoverSubtitle()">
                        <button type="button" class="rating-card card-rating-1" data-rating="1" onclick="submitRating(1)">
                            <div class="card-inner-glow"></div>
                            <div class="card-num-badge">1</div>
                            <div class="emoji-wrapper">
                                <!-- Animated SVG Emoji: Sangat Tidak Puas (Angry / Disappointed) -->
                                <svg class="animated-emoji emoji-angry" viewBox="0 0 100 100">
                                    <defs>
                                        <radialGradient id="gradAngry" cx="38%" cy="32%" r="68%">
                                            <stop offset="0%" stop-color="#FF8585" />
                                            <stop offset="60%" stop-color="#EF4444" />
                                            <stop offset="100%" stop-color="#B91C1C" />
                                        </radialGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="45" fill="url(#gradAngry)" filter="drop-shadow(0 8px 12px rgba(220,38,38,0.45))" />
                                    <!-- 3D Glossy Dome Highlight -->
                                    <ellipse cx="50" cy="21" rx="28" ry="10" fill="#ffffff" opacity="0.32" />
                                    <!-- Angry Eyebrows -->
                                    <path d="M22 34 L42 43" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round" />
                                    <path d="M78 34 L58 43" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round" />
                                    <!-- Disappointed Piercing Eyes -->
                                    <circle cx="33" cy="47" r="5.5" fill="#1e293b" />
                                    <circle cx="67" cy="47" r="5.5" fill="#1e293b" />
                                    <circle cx="35" cy="45" r="2" fill="#ffffff" />
                                    <circle cx="69" cy="45" r="2" fill="#ffffff" />
                                    <!-- Angry Cheek Flush -->
                                    <circle cx="23" cy="56" r="6" fill="#991b1b" opacity="0.4" />
                                    <circle cx="77" cy="56" r="6" fill="#991b1b" opacity="0.4" />
                                    <!-- Downturned Grimace Mouth -->
                                    <path d="M31 72 Q50 55 69 72" stroke="#1e293b" stroke-width="5.5" fill="none" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="rating-card-label">SANGAT TIDAK PUAS</div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-score-badge badge-rating-1">Nilai 1</span>
                        </button>
                    </div>

                    <!-- RATING 2: KURANG PUAS -->
                    <div class="rating-card-col" onmouseenter="setHoverSubtitle(2)" onmouseleave="resetHoverSubtitle()">
                        <button type="button" class="rating-card card-rating-2" data-rating="2" onclick="submitRating(2)">
                            <div class="card-inner-glow"></div>
                            <div class="card-num-badge">2</div>
                            <div class="emoji-wrapper">
                                <!-- Animated SVG Emoji: Kurang Puas (Sad/Pouting) -->
                                <svg class="animated-emoji emoji-sad" viewBox="0 0 100 100">
                                    <defs>
                                        <radialGradient id="gradSad" cx="38%" cy="32%" r="68%">
                                            <stop offset="0%" stop-color="#FDBA74" />
                                            <stop offset="60%" stop-color="#F97316" />
                                            <stop offset="100%" stop-color="#C2410C" />
                                        </radialGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="45" fill="url(#gradSad)" filter="drop-shadow(0 8px 12px rgba(249,115,22,0.45))" />
                                    <!-- 3D Glossy Dome Highlight -->
                                    <ellipse cx="50" cy="21" rx="28" ry="10" fill="#ffffff" opacity="0.32" />
                                    <!-- Sad Eyebrows -->
                                    <path d="M25 38 Q34 31 43 37" stroke="#ffffff" stroke-width="4" fill="none" stroke-linecap="round" />
                                    <path d="M75 38 Q66 31 57 37" stroke="#ffffff" stroke-width="4" fill="none" stroke-linecap="round" />
                                    <!-- Droopy Eyes -->
                                    <circle cx="34" cy="47" r="5.5" fill="#1e293b" />
                                    <circle cx="66" cy="47" r="5.5" fill="#1e293b" />
                                    <circle cx="36" cy="45" r="2" fill="#ffffff" />
                                    <circle cx="68" cy="45" r="2" fill="#ffffff" />
                                    <!-- Tear drop -->
                                    <path d="M71 56 C71 53 74 50 74 50 C74 50 77 53 77 56 C77 58 75.5 60 74 60 C72.5 60 71 58 71 56 Z" fill="#67e8f9" opacity="0.9" />
                                    <!-- Pouting Mouth -->
                                    <path d="M34 71 Q50 61 66 71" stroke="#1e293b" stroke-width="5" fill="none" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="rating-card-label">KURANG PUAS</div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-score-badge badge-rating-2">Nilai 2</span>
                        </button>
                    </div>

                    <!-- RATING 3: CUKUP PUAS -->
                    <div class="rating-card-col" onmouseenter="setHoverSubtitle(3)" onmouseleave="resetHoverSubtitle()">
                        <button type="button" class="rating-card card-rating-3" data-rating="3" onclick="submitRating(3)">
                            <div class="card-inner-glow"></div>
                            <div class="card-num-badge">3</div>
                            <div class="emoji-wrapper">
                                <!-- Animated SVG Emoji: Cukup (Neutral) -->
                                <svg class="animated-emoji emoji-neutral" viewBox="0 0 100 100">
                                    <defs>
                                        <radialGradient id="gradNeutral" cx="38%" cy="32%" r="68%">
                                            <stop offset="0%" stop-color="#FEF08A" />
                                            <stop offset="60%" stop-color="#EAB308" />
                                            <stop offset="100%" stop-color="#A16207" />
                                        </radialGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="45" fill="url(#gradNeutral)" filter="drop-shadow(0 8px 12px rgba(234,179,8,0.45))" />
                                    <!-- 3D Glossy Dome Highlight -->
                                    <ellipse cx="50" cy="21" rx="28" ry="10" fill="#ffffff" opacity="0.32" />
                                    <!-- Neutral Eyes with Blink Animation -->
                                    <circle class="blinking-eye" cx="34" cy="46" r="6" fill="#1e293b" />
                                    <circle class="blinking-eye" cx="66" cy="46" r="6" fill="#1e293b" />
                                    <circle cx="36" cy="44" r="2.2" fill="#ffffff" />
                                    <circle cx="68" cy="44" r="2.2" fill="#ffffff" />
                                    <!-- Subtle Rosy Cheeks -->
                                    <circle cx="23" cy="55" r="5.5" fill="#ca8a04" opacity="0.3" />
                                    <circle cx="77" cy="55" r="5.5" fill="#ca8a04" opacity="0.3" />
                                    <!-- Straight Neutral Mouth -->
                                    <line x1="34" y1="68" x2="66" y2="68" stroke="#1e293b" stroke-width="5.5" stroke-linecap="round" />
                                </svg>
                            </div>
                            <div class="rating-card-label">CUKUP PUAS</div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-score-badge badge-rating-3">Nilai 3</span>
                        </button>
                    </div>

                    <!-- RATING 4: PUAS -->
                    <div class="rating-card-col" onmouseenter="setHoverSubtitle(4)" onmouseleave="resetHoverSubtitle()">
                        <button type="button" class="rating-card card-rating-4" data-rating="4" onclick="submitRating(4)">
                            <div class="card-inner-glow"></div>
                            <div class="card-num-badge">4</div>
                            <div class="emoji-wrapper">
                                <!-- Animated SVG Emoji: Puas (Happy / Big Smile) -->
                                <svg class="animated-emoji emoji-happy" viewBox="0 0 100 100">
                                    <defs>
                                        <radialGradient id="gradHappy" cx="38%" cy="32%" r="68%">
                                            <stop offset="0%" stop-color="#6EE7B7" />
                                            <stop offset="60%" stop-color="#10B981" />
                                            <stop offset="100%" stop-color="#047857" />
                                        </radialGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="45" fill="url(#gradHappy)" filter="drop-shadow(0 8px 12px rgba(16,185,129,0.45))" />
                                    <!-- 3D Glossy Dome Highlight -->
                                    <ellipse cx="50" cy="21" rx="28" ry="10" fill="#ffffff" opacity="0.32" />
                                    <!-- Happy Crescent Eyes -->
                                    <path d="M25 45 Q35 34 45 45" stroke="#1e293b" stroke-width="5" fill="none" stroke-linecap="round" />
                                    <path d="M55 45 Q65 34 75 45" stroke="#1e293b" stroke-width="5" fill="none" stroke-linecap="round" />
                                    <!-- Rosy Glowing Cheeks -->
                                    <circle cx="23" cy="54" r="5.5" fill="#f87171" opacity="0.45" />
                                    <circle cx="77" cy="54" r="5.5" fill="#f87171" opacity="0.45" />
                                    <!-- Warm Open Smile with White Teeth -->
                                    <path d="M29 58 Q50 84 71 58 Z" fill="#1e293b" />
                                    <path d="M33 58 Q50 72 67 58 Z" fill="#ffffff" />
                                </svg>
                            </div>
                            <div class="rating-card-label">PUAS</div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-score-badge badge-rating-4">Nilai 4</span>
                        </button>
                    </div>

                    <!-- RATING 5: SANGAT PUAS / EXCELLENT -->
                    <div class="rating-card-col" onmouseenter="setHoverSubtitle(5)" onmouseleave="resetHoverSubtitle()">
                        <button type="button" class="rating-card card-rating-5" data-rating="5" onclick="submitRating(5)">
                            <div class="card-inner-glow"></div>
                            <div class="card-num-badge">5</div>
                            <!-- Floating Star/Heart Decor -->
                            <div class="card-star-badge">
                                <i class="fas fa-crown text-warning mr-1"></i> TERBAIK
                            </div>
                            <div class="emoji-wrapper">
                                <!-- Animated SVG Emoji: Sangat Puas / Love Eyes (Loving Star Smile) -->
                                <svg class="animated-emoji emoji-love" viewBox="0 0 100 100">
                                    <defs>
                                        <radialGradient id="gradLove" cx="38%" cy="32%" r="68%">
                                            <stop offset="0%" stop-color="#38BDF8" />
                                            <stop offset="60%" stop-color="#0284C7" />
                                            <stop offset="100%" stop-color="#0369A1" />
                                        </radialGradient>
                                        <linearGradient id="gradHeart" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#FF6B8B" />
                                            <stop offset="100%" stop-color="#E11D48" />
                                        </linearGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="45" fill="url(#gradLove)" filter="drop-shadow(0 8px 12px rgba(2,132,199,0.5))" />
                                    <!-- 3D Glossy Dome Highlight -->
                                    <ellipse cx="50" cy="21" rx="28" ry="10" fill="#ffffff" opacity="0.35" />
                                    <!-- Heart Eyes with Pulsing Effect -->
                                    <path class="heart-eye" d="M34 37 C34 31 25 30 24 36 C23 43 34 49 34 49 C34 49 45 43 44 36 C43 30 34 31 34 37 Z" fill="url(#gradHeart)" filter="drop-shadow(0 2px 4px rgba(225,29,72,0.4))" />
                                    <path class="heart-eye" d="M66 37 C66 31 57 30 56 36 C55 43 66 49 66 49 C66 49 77 43 76 36 C75 30 66 31 66 37 Z" fill="url(#gradHeart)" filter="drop-shadow(0 2px 4px rgba(225,29,72,0.4))" />
                                    <!-- Rosy Cheeks -->
                                    <circle cx="21" cy="56" r="5.5" fill="#f43f5e" opacity="0.45" />
                                    <circle cx="79" cy="56" r="5.5" fill="#f43f5e" opacity="0.45" />
                                    <!-- Beaming Smile with Teeth & Tongue -->
                                    <path d="M27 56 Q50 88 73 56 Z" fill="#1e293b" />
                                    <path d="M30 56 Q50 73 70 56 Z" fill="#ffffff" />
                                    <path d="M41 75 Q50 68 59 75 Q50 86 41 75 Z" fill="#FB7185" />
                                </svg>
                            </div>
                            <div class="rating-card-label">SANGAT PUAS</div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-score-badge badge-rating-5">★ Nilai 5 ★</span>
                        </button>
                    </div>

                </div>

            </main>

            <!-- Kiosk Footer Info Bar -->
            <footer class="survey-footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-6 col-12 text-md-left text-center mb-2 mb-md-0">
                            <span class="footer-company text-white">
                                <i class="fas fa-building mr-1 text-warning"></i> PT. Karisma Indoagro Universal &copy; <?= date('Y') ?>
                            </span>
                            <span class="footer-address d-none d-lg-inline ml-3 text-white-50">
                                <i class="fas fa-map-marker-alt mr-1 text-danger"></i> Jl. Semeru 89 Ajung, Jember - Jawa Timur
                            </span>
                        </div>
                        <div class="col-md-6 col-12 text-md-right text-center">
                            <span class="footer-social mr-3 text-white-50">
                                <i class="fab fa-instagram text-warning mr-1"></i> @karisma_indoagro
                            </span>
                            <span class="footer-social text-white-50">
                                <i class="fas fa-globe text-info mr-1"></i> www.kiu.co.id
                            </span>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
        <!-- /.survey-kiosk-container -->

    </div>
    <!-- ./wrapper -->

    <!-- Interactive Thank You & Celebration Modal Overlay -->
    <div id="thankYouOverlay" class="thank-you-overlay d-none" onclick="dismissThankYou()">
        <div class="thank-you-card shadow-lg" onclick="event.stopPropagation()">
            <!-- Floating Close Button -->
            <button type="button" class="btn-close-overlay" onclick="dismissThankYou()" title="Tutup">
                <i class="fas fa-times"></i>
            </button>

            <!-- Dynamic Icon/Emoji Container -->
            <div class="thank-you-emoji-box mb-3" id="thankYouEmojiBox">
                <!-- Injected dynamically -->
            </div>

            <!-- Header Title -->
            <h2 class="thank-you-title mb-2" id="thankYouTitle">Terima Kasih Banyak!</h2>

            <!-- Rating Stars Selected -->
            <div class="thank-you-stars mb-3" id="thankYouStars">
                <!-- Injected dynamically -->
            </div>

            <!-- Message Description -->
            <p class="thank-you-message mb-4" id="thankYouMsg">
                Penilaian Anda sangat berharga bagi kami dalam meningkatkan mutu pelayanan.
            </p>

            <!-- Countdown Timer & Auto Reset -->
            <div class="countdown-wrapper mb-3">
                <div class="countdown-pill">
                    <i class="fas fa-spinner fa-spin mr-2 text-primary"></i>
                    Kembali otomatis dalam <span id="countdownSec" class="font-weight-bold mx-1 text-primary">5</span> detik
                </div>
            </div>

            <!-- Action Button -->
            <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow font-weight-bold" onclick="dismissThankYou()">
                <i class="fas fa-check-circle mr-2"></i> Selesai
            </button>
        </div>
    </div>

    <!-- Scoped Modern CSS for Interactive Survey Kiosk -->
    <style>
        /* General Page Styling */
        html, body.modern-survey-body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #021B38 0%, #003B73 50%, #005AA6 100%) !important;
            user-select: none;
            -webkit-user-select: none;
        }

        /* Ambient Lights in Background */
        .ambient-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.35;
            pointer-events: none;
            z-index: 1;
        }
        .glow-1 {
            width: 450px;
            height: 450px;
            background: #00B894;
            top: -100px;
            right: -80px;
            animation: pulseGlow 10s ease-in-out infinite alternate;
        }
        .glow-2 {
            width: 500px;
            height: 500px;
            background: #0984E3;
            bottom: -120px;
            left: -100px;
            animation: pulseGlow 12s ease-in-out infinite alternate-reverse;
        }
        .glow-3 {
            width: 350px;
            height: 350px;
            background: #FDCB6E;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.15;
        }

        @keyframes pulseGlow {
            0% { transform: scale(1) translate(0, 0); opacity: 0.25; }
            100% { transform: scale(1.2) translate(30px, -20px); opacity: 0.45; }
        }

        /* Confetti Canvas */
        #confettiCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 9999;
        }

        /* Main Kiosk Container */
        .survey-kiosk-container {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            justify-content: space-between;
            padding: 1.25rem 2.5rem 1rem 2.5rem;
            box-sizing: border-box;
        }

        /* Header */
        .survey-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            padding-bottom: 0.5rem;
        }

        .header-branding {
            display: flex;
            align-items: center;
        }

        .brand-logo-card {
            background: #ffffff;
            border-radius: 50px;
            padding: 10px 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.35rem;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.2);
            gap: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .brand-logo-card:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
            transform: translateY(-1px);
        }

        .brand-img {
            max-height: 56px;
            width: auto;
            object-fit: contain;
            margin: 2px 0;
            transition: transform 0.3s ease;
        }

        .brand-img:hover {
            transform: scale(1.05);
        }

        .brand-divider {
            width: 1.5px;
            height: 42px;
            background-color: #cbd5e1;
            border-radius: 2px;
        }

        .brand-img-prima {
            max-height: 58px;
            width: auto;
            object-fit: contain;
            margin: 2px 0;
            transition: transform 0.3s ease;
        }

        .brand-img-prima:hover {
            transform: scale(1.08);
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.6px;
        }

        .brand-tagline {
            font-size: 0.95rem;
            color: #d1e3f8;
            font-weight: 500;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 0.5rem;
        }

        .live-clock-card {
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            border-radius: 18px;
            padding: 10px 24px;
            text-align: right;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
        }

        .clock-time {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: #ffffff;
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
        }

        .clock-tz {
            font-size: 1rem;
            font-weight: 700;
            margin-left: 6px;
            color: #93c5fd;
            letter-spacing: 0.5px;
        }

        .clock-date {
            font-size: 0.98rem;
            font-weight: 600;
            color: #dbeafe;
            margin-top: 3px;
            letter-spacing: 0.3px;
        }

        .kiosk-ctrl-btn {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.28);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.25s ease;
            outline: none !important;
            text-decoration: none !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        }

        .kiosk-ctrl-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.08);
            color: #ffffff;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        /* Hero Survey Section */
        .survey-main-content {
            text-align: center;
            padding: 0.25rem 0 1.25rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            margin-top: -24px;
        }

        .hero-badge-wrap {
            margin-bottom: 0.65rem;
        }

        .hero-pill-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            padding: 7px 18px;
            border-radius: 50px;
        }

        .animate-beat {
            animation: heartBeat 1.4s ease-in-out infinite;
        }

        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.25); }
        }

        .survey-hero-title {
            color: #ffffff;
            font-size: 2.55rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 0.65rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
            max-width: 950px;
        }

        .survey-hero-subtitle {
            color: #d1e3f8;
            font-size: 1.22rem;
            font-weight: 500;
            margin-bottom: 3.6rem;
            min-height: 28px;
            transition: all 0.3s ease;
        }

        /* 5 Creative Interactive Cards Grid */
        .rating-cards-grid {
            display: flex;
            justify-content: center;
            align-items: stretch;
            gap: 26px;
            width: 100%;
            max-width: 1680px;
            flex-wrap: wrap;
            padding: 0 10px;
        }

        .rating-card-col {
            flex: 1 1 250px;
            max-width: 310px;
            min-width: 220px;
            display: flex;
        }

        .rating-card {
            position: relative;
            width: 100%;
            background: #ffffff;
            border-radius: 36px;
            border: 3.5px solid transparent;
            padding: 3rem 1.4rem 2.2rem 1.4rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            outline: none !important;
            transition: all 0.38s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.22), 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            min-height: 485px;
        }

        /* Glass Light Beam Sweep on Hover */
        .rating-card::before {
            content: '';
            position: absolute;
            top: -60%;
            left: -80%;
            width: 50%;
            height: 220%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.5) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            transition: all 0.8s ease;
            pointer-events: none;
            opacity: 0;
            z-index: 10;
        }

        .rating-card:hover::before {
            left: 150%;
            opacity: 1;
        }

        .card-inner-glow {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            border-radius: 36px 36px 0 0;
            transition: all 0.3s ease;
        }

        /* Number Badge in Top-Left of Card */
        .card-num-badge {
            position: absolute;
            top: 18px;
            left: 20px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
            font-size: 1.18rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            z-index: 5;
        }

        /* Card Specific Backgrounds, Accents & Glows */
        .card-rating-1 {
            background: linear-gradient(175deg, #ffffff 50%, #fff1f2 100%);
            border-color: rgba(239, 68, 68, 0.2);
        }
        .card-rating-1 .card-inner-glow { background: linear-gradient(90deg, #ff7675, #ef4444); }
        .card-rating-1 .card-num-badge {
            border: 2.5px solid #ef4444;
            color: #ef4444;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.22);
        }
        .card-rating-1 .rating-card-label {
            color: #dc2626;
        }
        .card-rating-1:hover {
            border-color: #ef4444;
            transform: translateY(-20px) scale(1.045);
            box-shadow: 0 32px 60px rgba(239, 68, 68, 0.42), 0 0 30px rgba(239, 68, 68, 0.22);
        }
        .card-rating-1:hover .card-num-badge {
            background: #ef4444;
            color: #ffffff;
            transform: scale(1.1);
            box-shadow: 0 6px 18px rgba(239, 68, 68, 0.5);
        }
        .card-rating-1:hover .animated-emoji {
            animation: shakeEmoji 0.6s infinite alternate;
        }

        .card-rating-2 {
            background: linear-gradient(175deg, #ffffff 50%, #fff7ed 100%);
            border-color: rgba(249, 115, 22, 0.2);
        }
        .card-rating-2 .card-inner-glow { background: linear-gradient(90deg, #fdba74, #f97316); }
        .card-rating-2 .card-num-badge {
            border: 2.5px solid #f97316;
            color: #f97316;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.22);
        }
        .card-rating-2 .rating-card-label {
            color: #ea580c;
        }
        .card-rating-2:hover {
            border-color: #f97316;
            transform: translateY(-20px) scale(1.045);
            box-shadow: 0 32px 60px rgba(249, 115, 22, 0.42), 0 0 30px rgba(249, 115, 22, 0.22);
        }
        .card-rating-2:hover .card-num-badge {
            background: #f97316;
            color: #ffffff;
            transform: scale(1.1);
            box-shadow: 0 6px 18px rgba(249, 115, 22, 0.5);
        }
        .card-rating-2:hover .animated-emoji {
            animation: droopEmoji 0.8s ease-in-out infinite alternate;
        }

        .card-rating-3 {
            background: linear-gradient(175deg, #ffffff 50%, #fefce8 100%);
            border-color: rgba(245, 158, 11, 0.25);
        }
        .card-rating-3 .card-inner-glow { background: linear-gradient(90deg, #fde047, #f59e0b); }
        .card-rating-3 .card-num-badge {
            border: 2.5px solid #f59e0b;
            color: #ca8a04;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.22);
        }
        .card-rating-3 .rating-card-label {
            color: #ca8a04;
        }
        .card-rating-3:hover {
            border-color: #f59e0b;
            transform: translateY(-20px) scale(1.045);
            box-shadow: 0 32px 60px rgba(245, 158, 11, 0.42), 0 0 30px rgba(245, 158, 11, 0.22);
        }
        .card-rating-3:hover .card-num-badge {
            background: #f59e0b;
            color: #ffffff;
            transform: scale(1.1);
            box-shadow: 0 6px 18px rgba(245, 158, 11, 0.5);
        }
        .card-rating-3:hover .animated-emoji {
            animation: tiltEmoji 0.8s ease-in-out infinite alternate;
        }

        .card-rating-4 {
            background: linear-gradient(175deg, #ffffff 50%, #f0fdf4 100%);
            border-color: rgba(16, 185, 129, 0.25);
        }
        .card-rating-4 .card-inner-glow { background: linear-gradient(90deg, #6ee7b7, #10b981); }
        .card-rating-4 .card-num-badge {
            border: 2.5px solid #10b981;
            color: #10b981;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.22);
        }
        .card-rating-4 .rating-card-label {
            color: #059669;
        }
        .card-rating-4:hover {
            border-color: #10b981;
            transform: translateY(-20px) scale(1.045);
            box-shadow: 0 32px 60px rgba(16, 185, 129, 0.45), 0 0 30px rgba(16, 185, 129, 0.22);
        }
        .card-rating-4:hover .card-num-badge {
            background: #10b981;
            color: #ffffff;
            transform: scale(1.1);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.5);
        }
        .card-rating-4:hover .animated-emoji {
            animation: bounceEmoji 0.8s ease infinite alternate;
        }

        .card-rating-5 {
            background: linear-gradient(175deg, #ffffff 45%, #e0f2fe 100%);
            border: 3.5px solid rgba(2, 132, 199, 0.45);
        }
        .card-rating-5 .card-inner-glow { background: linear-gradient(90deg, #38bdf8, #0284c7, #005aa6); }
        .card-rating-5 .card-num-badge {
            border: 2.5px solid #0284c7;
            color: #0284c7;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
        }
        .card-rating-5 .rating-card-label {
            color: #0284c7;
        }
        .card-rating-5:hover {
            border-color: #0284c7;
            transform: translateY(-22px) scale(1.06);
            box-shadow: 0 36px 70px rgba(2, 132, 199, 0.58), 0 0 35px rgba(56, 189, 248, 0.4);
        }
        .card-rating-5:hover .card-num-badge {
            background: #0284c7;
            color: #ffffff;
            transform: scale(1.1);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.55);
        }
        .card-rating-5:hover .animated-emoji {
            animation: loveEmoji 0.7s ease infinite alternate;
        }

        .rating-card:active {
            transform: translateY(2px) scale(0.975) !important;
        }

        .card-star-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: linear-gradient(135deg, #0284c7, #005aa6);
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 800;
            padding: 5px 13px;
            border-radius: 50px;
            letter-spacing: 0.8px;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.45);
            border: 1.5px solid rgba(255, 255, 255, 0.4);
            z-index: 5;
        }

        /* SVG Emoji Animations */
        .emoji-wrapper {
            width: 175px;
            height: 175px;
            margin-top: 0.6rem;
            margin-bottom: 1.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.35s ease;
            filter: drop-shadow(0 14px 24px rgba(0, 0, 0, 0.18));
            position: relative;
        }

        .rating-card:hover .emoji-wrapper {
            filter: drop-shadow(0 18px 28px rgba(0, 0, 0, 0.26));
            transform: scale(1.05);
        }

        .animated-emoji {
            width: 100%;
            height: 100%;
            transition: all 0.3s ease;
        }

        @keyframes shakeEmoji {
            0% { transform: rotate(-6deg) translate(-3px, 0); }
            100% { transform: rotate(6deg) translate(3px, 0); }
        }

        @keyframes droopEmoji {
            0% { transform: translateY(0); }
            100% { transform: translateY(8px); }
        }

        @keyframes tiltEmoji {
            0% { transform: rotate(-5deg); }
            100% { transform: rotate(5deg); }
        }

        @keyframes bounceEmoji {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(-10px) scale(1.06); }
        }

        @keyframes loveEmoji {
            0% { transform: scale(1) rotate(-4deg); }
            100% { transform: scale(1.12) rotate(4deg); }
        }

        .heart-eye {
            animation: pulseEye 1.2s ease-in-out infinite;
            transform-origin: center;
        }

        @keyframes pulseEye {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.18); }
        }

        /* Card Text & Badges */
        .rating-card-label {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            margin-bottom: 0.65rem;
            text-align: center;
            line-height: 1.25;
            transition: color 0.3s ease;
        }

        .rating-stars {
            font-size: 1.45rem;
            margin-bottom: 1.25rem;
            display: flex;
            gap: 6px;
            color: #cbd5e1;
        }

        .rating-stars .fas.fa-star {
            color: #f59e0b;
            filter: drop-shadow(0 2px 5px rgba(245, 158, 11, 0.45));
        }

        .rating-score-badge {
            font-size: 1.05rem;
            font-weight: 800;
            padding: 8px 24px;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.06);
            letter-spacing: 0.5px;
        }

        .badge-rating-1 {
            background: #fef2f2;
            color: #dc2626;
            border: 1.5px solid #fecaca;
        }
        .badge-rating-2 {
            background: #fff7ed;
            color: #ea580c;
            border: 1.5px solid #fed7aa;
        }
        .badge-rating-3 {
            background: #fefce8;
            color: #ca8a04;
            border: 1.5px solid #fef08a;
        }
        .badge-rating-4 {
            background: #ecfdf5;
            color: #059669;
            border: 1.5px solid #a7f3d0;
        }
        .badge-rating-5 {
            background: #f0f9ff;
            color: #0284c7;
            border: 1.5px solid #bae6fd;
        }

        .card-rating-1:hover .rating-score-badge {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            border-color: #ef4444;
            box-shadow: 0 6px 18px rgba(239, 68, 68, 0.45);
        }
        .card-rating-2:hover .rating-score-badge {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #ffffff;
            border-color: #f97316;
            box-shadow: 0 6px 18px rgba(249, 115, 22, 0.45);
        }
        .card-rating-3:hover .rating-score-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            border-color: #f59e0b;
            box-shadow: 0 6px 18px rgba(245, 158, 11, 0.45);
        }
        .card-rating-4:hover .rating-score-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            border-color: #10b981;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }
        .card-rating-5:hover .rating-score-badge {
            background: linear-gradient(135deg, #0284c7, #005aa6);
            color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.5);
        }

        /* Footer */
        .survey-footer {
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            font-size: 0.85rem;
        }

        /* Thank You Modal Overlay */
        .thank-you-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(2, 27, 56, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 1.5rem;
            animation: fadeInOverlay 0.35s ease;
        }

        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .thank-you-card {
            position: relative;
            background: #ffffff;
            border-radius: 28px;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            animation: scaleInCard 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes scaleInCard {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .btn-close-overlay {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #f1f5f9;
            border: none;
            color: #64748b;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-close-overlay:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .thank-you-emoji-box {
            width: 110px;
            height: 110px;
            margin: 0 auto;
            animation: popEmoji 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popEmoji {
            0% { transform: scale(0.5); }
            70% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .thank-you-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
        }

        .thank-you-stars {
            font-size: 1.25rem;
        }

        .thank-you-message {
            font-size: 1.05rem;
            color: #475569;
            line-height: 1.55;
        }

        .countdown-pill {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            color: #64748b;
        }

        /* ========================================================
           TOUCH & TABLET INTERACTIVITY OPTIMIZATIONS
           ======================================================== */
        * {
            -webkit-tap-highlight-color: transparent;
        }

        body {
            touch-action: manipulation;
            -webkit-touch-callout: none;
            user-select: none;
            -webkit-user-select: none;
        }

        .rating-card, .kiosk-ctrl-btn, .btn-close-overlay {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        /* Tactile touch down feedback for tablets */
        .rating-card:active,
        .rating-card.is-touched {
            transform: scale(0.95) translateY(2px) !important;
            transition: transform 0.1s ease !important;
        }
        .card-rating-1:active, .card-rating-1.is-touched {
            border-color: #ef4444 !important;
            box-shadow: 0 10px 24px rgba(239, 68, 68, 0.45) !important;
        }
        .card-rating-2:active, .card-rating-2.is-touched {
            border-color: #f97316 !important;
            box-shadow: 0 10px 24px rgba(249, 115, 22, 0.45) !important;
        }
        .card-rating-3:active, .card-rating-3.is-touched {
            border-color: #f59e0b !important;
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.45) !important;
        }
        .card-rating-4:active, .card-rating-4.is-touched {
            border-color: #10b981 !important;
            box-shadow: 0 10px 24px rgba(16, 185, 129, 0.45) !important;
        }
        .card-rating-5:active, .card-rating-5.is-touched {
            border-color: #0284c7 !important;
            box-shadow: 0 12px 28px rgba(2, 132, 199, 0.5) !important;
        }

        /* ========================================================
           TABLET LANDSCAPE & COMPACT DESKTOPS (width <= 1280px or height <= 820px)
           e.g. iPad 1024x768, Galaxy Tab 1280x800, Surface Pro
           ======================================================== */
        @media screen and (max-width: 1280px), screen and (max-height: 820px) and (orientation: landscape) {
            .survey-kiosk-container {
                padding: 0.75rem 1.4rem;
                min-height: 100vh;
            }

            .survey-header {
                padding-bottom: 0.25rem;
                flex-wrap: nowrap;
            }

            .brand-logo-card {
                padding: 6px 18px;
                gap: 12px;
                margin-right: 0.75rem;
            }

            .brand-img {
                max-height: 42px;
            }

            .brand-divider {
                height: 32px;
            }

            .brand-img-prima {
                max-height: 44px;
            }

            .brand-title {
                font-size: 1.1rem;
                line-height: 1.2;
            }

            .brand-tagline {
                font-size: 0.8rem;
            }

            .live-clock-card {
                padding: 6px 16px;
                border-radius: 14px;
            }

            .clock-time {
                font-size: 1.35rem;
            }

            .clock-tz {
                font-size: 0.85rem;
            }

            .clock-date {
                font-size: 0.8rem;
            }

            .kiosk-ctrl-btn {
                width: 46px;
                height: 46px;
                font-size: 1.1rem;
                border-radius: 14px;
            }

            .survey-main-content {
                padding: 0.2rem 0 0.6rem 0;
                margin-top: -6px;
            }

            .hero-badge-wrap {
                margin-bottom: 0.4rem;
            }

            .hero-pill-badge {
                font-size: 0.74rem;
                padding: 4px 14px;
            }

            .survey-hero-title {
                font-size: 1.95rem;
                margin-bottom: 0.35rem;
            }

            .survey-hero-subtitle {
                font-size: 1rem;
                margin-bottom: 1.6rem;
                min-height: 22px;
            }

            /* 5 Cards Row across Tablet Landscape */
            .rating-cards-grid {
                gap: 14px;
                max-width: 1000px;
                flex-wrap: nowrap;
                padding: 0 4px;
            }

            .rating-card-col {
                flex: 1 1 170px;
                max-width: 190px;
                min-width: 145px;
            }

            .rating-card {
                min-height: 375px;
                padding: 1.8rem 0.6rem 1.4rem 0.6rem;
                border-radius: 26px;
                border-width: 3px;
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
            }

            .card-num-badge {
                width: 34px;
                height: 34px;
                font-size: 0.95rem;
                top: 10px;
                left: 10px;
            }

            .card-star-badge {
                font-size: 0.68rem;
                padding: 3px 8px;
                top: 10px;
                right: 8px;
            }

            .emoji-wrapper {
                width: 115px;
                height: 115px;
                margin-top: 0.2rem;
                margin-bottom: 0.8rem;
                filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.16));
            }

            .rating-card-label {
                font-size: 0.95rem;
                margin-bottom: 0.45rem;
                letter-spacing: 0.4px;
            }

            .rating-stars {
                font-size: 1.1rem;
                gap: 3px;
                margin-bottom: 0.8rem;
            }

            .rating-score-badge {
                font-size: 0.85rem;
                padding: 6px 14px;
            }

            .survey-footer {
                padding-top: 0.5rem;
                font-size: 0.78rem;
            }
        }

        /* ========================================================
           TABLET PORTRAIT (e.g. iPad 768x1024, Galaxy Tab 800x1280)
           ======================================================== */
        @media screen and (max-width: 900px) and (min-width: 577px) and (orientation: portrait),
               screen and (max-width: 900px) and (min-height: 900px) {
            .survey-kiosk-container {
                padding: 0.75rem 1.25rem 0.5rem 1.25rem;
                min-height: 100vh;
            }

            .survey-header {
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 8px;
                padding-bottom: 0.35rem;
            }

            .brand-logo-card {
                padding: 5px 14px;
                gap: 10px;
                margin-right: 0.5rem;
            }

            .brand-img {
                max-height: 34px;
            }

            .brand-img-prima {
                max-height: 36px;
            }

            .brand-divider {
                height: 24px;
            }

            .brand-title {
                font-size: 1.05rem;
            }

            .brand-tagline {
                font-size: 0.78rem;
            }

            .header-controls {
                margin-top: 0.2rem;
                gap: 8px;
            }

            .live-clock-card {
                padding: 4px 12px;
            }

            .clock-time {
                font-size: 1.2rem;
            }

            .kiosk-ctrl-btn {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .survey-main-content {
                margin-top: -6px;
                padding: 0.2rem 0 0.8rem 0;
            }

            .hero-badge-wrap {
                margin-bottom: 0.3rem;
            }

            .hero-pill-badge {
                font-size: 0.72rem;
                padding: 4px 12px;
            }

            .survey-hero-title {
                font-size: 1.7rem;
                margin-bottom: 0.25rem;
            }

            .survey-hero-subtitle {
                font-size: 0.95rem;
                margin-bottom: 1.2rem;
                min-height: 20px;
            }

            /* 3 + 2 Centered Grid for Portrait Tablet */
            .rating-cards-grid {
                gap: 14px;
                max-width: 680px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .rating-card-col {
                flex: 1 1 180px;
                max-width: 205px;
                min-width: 175px;
            }

            .rating-card {
                min-height: 290px;
                padding: 1.4rem 0.6rem 1.1rem 0.6rem;
                border-radius: 22px;
                box-shadow: 0 10px 22px rgba(0, 0, 0, 0.16);
            }

            .card-num-badge {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
                top: 10px;
                left: 10px;
            }

            .card-star-badge {
                font-size: 0.65rem;
                padding: 3px 8px;
                top: 10px;
                right: 8px;
            }

            .emoji-wrapper {
                width: 95px;
                height: 95px;
                margin-top: 0.15rem;
                margin-bottom: 0.55rem;
            }

            .rating-card-label {
                font-size: 0.92rem;
                margin-bottom: 0.35rem;
                letter-spacing: 0.3px;
            }

            .rating-stars {
                font-size: 1.05rem;
                gap: 3px;
                margin-bottom: 0.65rem;
            }

            .rating-score-badge {
                font-size: 0.85rem;
                padding: 5px 14px;
            }

            .survey-footer {
                padding-top: 0.35rem;
                font-size: 0.75rem;
            }

            .footer-company, .footer-address {
                white-space: nowrap;
            }
        }

        /* ========================================================
           MOBILE PHONES (width <= 576px)
           ======================================================== */
        @media screen and (max-width: 576px) {
            .survey-kiosk-container {
                padding: 0.75rem 0.75rem;
            }

            .survey-header {
                justify-content: center;
                gap: 8px;
            }

            .header-branding {
                flex-direction: column;
                text-align: center;
            }

            .brand-logo-card {
                margin-right: 0;
                margin-bottom: 6px;
            }

            .brand-title {
                font-size: 1rem;
            }

            .live-clock-card {
                text-align: center;
                padding: 6px 12px;
            }

            .clock-time {
                font-size: 1.2rem;
                justify-content: center;
            }

            .survey-hero-title {
                font-size: 1.35rem;
            }

            .survey-hero-subtitle {
                font-size: 0.88rem;
                margin-bottom: 1.2rem;
            }

            .rating-cards-grid {
                gap: 10px;
            }

            .rating-card-col {
                flex: 1 1 130px;
                max-width: 155px;
            }

            .rating-card {
                min-height: 250px;
                padding: 1.2rem 0.5rem 1rem 0.5rem;
                border-radius: 20px;
            }

            .emoji-wrapper {
                width: 75px;
                height: 75px;
                margin-bottom: 0.5rem;
            }

            .rating-card-label {
                font-size: 0.78rem;
                margin-bottom: 0.35rem;
            }

            .rating-stars {
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }

            .rating-score-badge {
                font-size: 0.75rem;
                padding: 4px 10px;
            }
        }
    </style>

    <!-- Interactive Logic: Web Audio, Confetti, AJAX Submission & Countdown -->
    <script>
        var soundEnabled = true;
        var countdownTimer = null;
        var countdownCount = 5;
        var audioCtx = null;
        var isSubmitting = false;

        // Subtitle text definitions on hover
        var subtitleHoverTexts = {
            1: "😡 Sangat Tidak Puas — Kami mohon maaf dan berkomitmen untuk segera berbenah.",
            2: "🙁 Kurang Puas — Terima kasih, masukan Anda membantu kami memperbaiki layanan.",
            3: "😐 Cukup Puas — Terima kasih, kami siap meningkatkan pelayanan lebih baik lagi.",
            4: "😊 Puas — Senang dapat melayani Anda dengan baik dan profesional!",
            5: "😍 Sangat Puas — Luar biasa! Terima kasih atas apresiasi dan kepercayaan Anda!"
        };

        var defaultSubtitleText = "Sentuh salah satu emotikon di bawah untuk memberikan penilaian Anda";

        function setHoverSubtitle(rating) {
            var sub = document.getElementById('dynamicSubtitle');
            if (sub && subtitleHoverTexts[rating]) {
                sub.style.opacity = '0';
                setTimeout(function() {
                    sub.innerText = subtitleHoverTexts[rating];
                    sub.style.opacity = '1';
                }, 150);
            }
        }

        function resetHoverSubtitle() {
            var sub = document.getElementById('dynamicSubtitle');
            if (sub) {
                sub.style.opacity = '0';
                setTimeout(function() {
                    sub.innerText = defaultSubtitleText;
                    sub.style.opacity = '1';
                }, 150);
            }
        }

        // Live Clock Function
        function updateLiveClock() {
            var now = new Date();
            var h = String(now.getHours()).padStart(2, '0');
            var m = String(now.getMinutes()).padStart(2, '0');
            var s = String(now.getSeconds()).padStart(2, '0');
            var timeElem = document.getElementById('liveClockTime');
            if (timeElem) {
                timeElem.innerHTML = '<i class="far fa-clock text-warning mr-2" style="font-size: 1.4rem;"></i>' + h + ':' + m + ':' + s + '<span class="clock-tz">WIB</span>';
            }
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // Audio Sound Synthesis using Web Audio API
        function playChime(rating) {
            if (!soundEnabled) return;
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                var now = audioCtx.currentTime;
                var notes = [];

                if (rating === 5) {
                    // C5 -> E5 -> G5 -> C6 (Major triumphant arpeggio)
                    notes = [523.25, 659.25, 783.99, 1046.50];
                } else if (rating === 4) {
                    // G4 -> C5 -> E5 (Cheerful chime)
                    notes = [392.00, 523.25, 659.25];
                } else if (rating === 3) {
                    // E4 -> G4 (Warm chime)
                    notes = [329.63, 392.00];
                } else if (rating === 2) {
                    // G3 -> C4
                    notes = [196.00, 261.63];
                } else {
                    // C3 -> G3
                    notes = [130.81, 196.00];
                }

                notes.forEach(function(freq, idx) {
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.type = rating >= 4 ? 'triangle' : 'sine';
                    osc.frequency.setValueAtTime(freq, now + idx * 0.08);
                    gain.gain.setValueAtTime(0.001, now + idx * 0.08);
                    gain.gain.exponentialRampToValueAtTime(0.2, now + idx * 0.08 + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.08 + 0.35);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now + idx * 0.08);
                    osc.stop(now + idx * 0.08 + 0.36);
                });
            } catch (err) {
                console.log("Audio not allowed yet or unsupported", err);
            }
        }

        // Toggle Sound Button
        function toggleSound() {
            soundEnabled = !soundEnabled;
            var icon = document.getElementById('soundIcon');
            if (icon) {
                if (soundEnabled) {
                    icon.className = 'fas fa-volume-up';
                    playChime(5);
                } else {
                    icon.className = 'fas fa-volume-mute';
                }
            }
        }

        // Toggle Fullscreen Kiosk Mode (Cross-browser for Tablets & Desktops)
        function toggleFullScreen() {
            var icon = document.getElementById('fsIcon');
            var doc = document;
            var el = doc.documentElement;
            var isFs = doc.fullscreenElement || doc.webkitFullscreenElement || doc.mozFullScreenElement || doc.msFullscreenElement;
            if (!isFs) {
                if (el.requestFullscreen) {
                    el.requestFullscreen().catch(function(e){ console.log(e); });
                } else if (el.webkitRequestFullscreen) {
                    el.webkitRequestFullscreen();
                } else if (el.mozRequestFullScreen) {
                    el.mozRequestFullScreen();
                } else if (el.msRequestFullscreen) {
                    el.msRequestFullscreen();
                }
                if (icon) icon.className = 'fas fa-compress';
            } else {
                if (doc.exitFullscreen) {
                    doc.exitFullscreen();
                } else if (doc.webkitExitFullscreen) {
                    doc.webkitExitFullscreen();
                } else if (doc.mozCancelFullScreen) {
                    doc.mozCancelFullScreen();
                } else if (doc.msExitFullscreen) {
                    doc.msExitFullscreen();
                }
                if (icon) icon.className = 'fas fa-expand';
            }
        }

        // Lightweight Canvas Confetti Generator
        function fireConfetti() {
            var canvas = document.getElementById('confettiCanvas');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            var pieces = [];
            var numberOfPieces = 120;
            var colors = ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#eab308'];

            for (var i = 0; i < numberOfPieces; i++) {
                pieces.push({
                    x: canvas.width / 2 + (Math.random() - 0.5) * 200,
                    y: canvas.height * 0.45,
                    vx: (Math.random() - 0.5) * 18,
                    vy: (Math.random() - 1.2) * 16,
                    size: Math.random() * 8 + 6,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    rotation: Math.random() * 360,
                    rotSpeed: (Math.random() - 0.5) * 12,
                    gravity: 0.35,
                    opacity: 1
                });
            }

            var animId;
            var frameCount = 0;

            function update() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                frameCount++;

                for (var i = 0; i < pieces.length; i++) {
                    var p = pieces[i];
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += p.gravity;
                    p.rotation += p.rotSpeed;
                    if (frameCount > 40) {
                        p.opacity -= 0.015;
                    }

                    if (p.opacity > 0) {
                        ctx.save();
                        ctx.translate(p.x, p.y);
                        ctx.rotate((p.rotation * Math.PI) / 180);
                        ctx.fillStyle = p.color;
                        ctx.globalAlpha = Math.max(0, p.opacity);
                        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
                        ctx.restore();
                    }
                }

                if (frameCount < 120) {
                    animId = requestAnimationFrame(update);
                } else {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
            }

            update();
        }

        // Rating Submission Handler (AJAX + Animation + Tablet Haptics)
        function submitRating(rating) {
            if (isSubmitting) return;
            isSubmitting = true;

            // Play sound effect
            playChime(rating);

            // Tablet Haptic Vibration (Tactile response on touchscreen)
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate(rating === 5 ? [40, 60, 40] : 45);
            }

            // Trigger confetti for high satisfaction (4 & 5 stars)
            if (rating >= 4) {
                fireConfetti();
            }

            // Perform AJAX background post to server
            var targetUrl = "<?= base_url('nilai_ipkp/') ?>" + rating + "?ajax=1";
            fetch(targetUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function(res) {
                return res.json().catch(function() { return {}; });
            }).catch(function(err) {
                console.log("Submit completed or redirect intercepted", err);
            });

            // Display Interactive Thank You Modal Overlay
            showThankYouModal(rating);
        }

        // Show Thank You Overlay with Custom Messaging per Rating
        function showThankYouModal(rating) {
            var overlay = document.getElementById('thankYouOverlay');
            var emojiBox = document.getElementById('thankYouEmojiBox');
            var title = document.getElementById('thankYouTitle');
            var stars = document.getElementById('thankYouStars');
            var msg = document.getElementById('thankYouMsg');

            // Clone selected emoji svg into the modal
            var card = document.querySelector('.card-rating-' + rating);
            if (card && emojiBox) {
                var svg = card.querySelector('svg');
                if (svg) {
                    emojiBox.innerHTML = svg.outerHTML;
                }
            }

            // Generate star visuals
            var starsHtml = '';
            for (var i = 1; i <= 5; i++) {
                if (i <= rating) {
                    starsHtml += '<i class="fas fa-star text-warning mx-1"></i>';
                } else {
                    starsHtml += '<i class="far fa-star text-muted mx-1" style="opacity:0.35;"></i>';
                }
            }
            stars.innerHTML = starsHtml;

            // Tailor messages
            if (rating === 5) {
                title.innerText = "Luar Biasa, Terima Kasih!";
                msg.innerText = "Penilaian 5 bintang Anda sangat memotivasi seluruh tim kami untuk selalu menghadirkan pelayanan terbaik dan prima di PT Karisma Indoagro Universal.";
            } else if (rating === 4) {
                title.innerText = "Terima Kasih Banyak!";
                msg.innerText = "Kepuasan Anda adalah kebanggaan kami. Kami senang dapat melayani kebutuhan Anda dengan baik.";
            } else if (rating === 3) {
                title.innerText = "Terima Kasih atas Masukan Anda!";
                msg.innerText = "Penilaian Anda sangat berharga bagi kami. Kami berkomitmen untuk terus meningkatkan mutu dan kenyamanan pelayanan.";
            } else {
                title.innerText = "Terima Kasih atas Evaluasi Anda!";
                msg.innerText = "Mohon maaf jika pelayanan kami belum optimal. Evaluasi Anda akan segera kami teruskan ke manajemen untuk perbaikan segera.";
            }

            // Show overlay
            overlay.classList.remove('d-none');

            // Start Countdown Timer
            countdownCount = 5;
            var countdownElem = document.getElementById('countdownSec');
            if (countdownElem) countdownElem.innerText = countdownCount;

            clearInterval(countdownTimer);
            countdownTimer = setInterval(function() {
                countdownCount--;
                if (countdownElem) countdownElem.innerText = countdownCount;
                if (countdownCount <= 0) {
                    dismissThankYou();
                }
            }, 1000);
        }

        // Dismiss Thank You Modal and reset state
        function dismissThankYou() {
            clearInterval(countdownTimer);
            var overlay = document.getElementById('thankYouOverlay');
            if (overlay) {
                overlay.classList.add('d-none');
            }
            isSubmitting = false;
            resetHoverSubtitle();
        }

        // Listen for ESC key to dismiss modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dismissThankYou();
            }
        });

        // Auto-unlock Web Audio on first touch or click (crucial for iPad / Android tablets)
        function unlockAudio() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }
        document.addEventListener('touchstart', unlockAudio, { once: true, passive: true });
        document.addEventListener('pointerdown', unlockAudio, { once: true, passive: true });
        document.addEventListener('click', unlockAudio, { once: true, passive: true });

        // Touch Feedback & Subtitle interaction binding for Tablet Touchscreens
        document.addEventListener('DOMContentLoaded', function() {
            var cards = document.querySelectorAll('.rating-card');
            cards.forEach(function(card) {
                var r = parseInt(card.getAttribute('data-rating'), 10);
                card.addEventListener('touchstart', function() {
                    card.classList.add('is-touched');
                    if (r) setHoverSubtitle(r);
                }, { passive: true });

                card.addEventListener('touchend', function() {
                    card.classList.remove('is-touched');
                }, { passive: true });

                card.addEventListener('touchcancel', function() {
                    card.classList.remove('is-touched');
                }, { passive: true });
            });
        });
    </script>