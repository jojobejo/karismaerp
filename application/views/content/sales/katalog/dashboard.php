<style>
    :root {
        --sales-primary: #2d5b7d;
        --sales-primary-soft: #e9f2f9;
        --sales-accent: #ff7a45;
        --sales-surface: #ffffff;
        --sales-border: #dce7f0;
        --sales-text: #203447;
        --sales-muted: #708394;
        --sales-shadow: 0 18px 40px rgba(31, 58, 86, 0.10);
    }

    .content-wrapper {
        background:
            radial-gradient(circle at top left, rgba(123, 176, 201, 0.18), transparent 26%),
            linear-gradient(180deg, #f7fbfd 0%, #eef4f8 100%);
    }

    .sales-dashboard {
        padding: 0 10px 28px;
    }

    .stats-row .info-box {
        min-height: 98px;
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--sales-shadow);
    }

    .stats-row .info-box-icon {
        min-height: 98px;
    }

    .stats-row .info-box-content {
        padding: 15px 14px 15px 12px;
    }

    .stats-row .info-box-text {
        font-size: 12px;
        line-height: 1.35;
        white-space: normal;
        color: #5f7285;
    }

    .stats-row .info-box-number {
        font-size: 24px;
        line-height: 1.1;
        color: var(--sales-text);
    }

    .catalog-shell,
    .filter-card,
    .editor-modal .modal-content {
        border: 0;
        border-radius: 22px;
        box-shadow: var(--sales-shadow);
    }

    .filter-card,
    .catalog-shell {
        overflow: hidden;
        background: var(--sales-surface);
    }

    .filter-card .card-body,
    .catalog-shell .card-body {
        padding: 1rem;
    }

    .catalog-shell .card-body {
        padding: 1.15rem;
    }

    .catalog-topbar {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(200px, 0.7fr);
        gap: 14px;
        align-items: stretch;
        margin-bottom: 1rem;
    }

    .search-panel,
    .summary-panel {
        border-radius: 20px;
        padding: 1rem 1.1rem;
        border: 1px solid var(--sales-border);
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .panel-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--sales-muted);
        margin-bottom: 8px;
        display: block;
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap .fa-search {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #7a8ea0;
        font-size: 14px;
    }

    .search-input {
        height: 50px;
        border-radius: 16px;
        border: 1px solid #d6e3ec;
        padding-left: 40px;
        padding-right: 42px;
        font-size: 14px;
        color: var(--sales-text);
        box-shadow: none;
    }

    .search-clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #86a0b3;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: none;
    }

    .search-clear.is-visible {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .search-hint {
        margin-top: 8px;
        font-size: 12px;
        color: var(--sales-muted);
    }

    .summary-number {
        font-size: 30px;
        font-weight: 700;
        line-height: 1;
        color: var(--sales-primary);
    }

    .summary-caption {
        margin-top: 8px;
        font-size: 13px;
        line-height: 1.5;
        color: var(--sales-muted);
    }

    .filter-section {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-label {
        min-width: 120px;
        margin: 0;
        font-size: 13px;
        color: #44576a;
        padding-top: 6px;
    }

    .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .filter-group .btn {
        border-radius: 999px;
        padding: 7px 14px;
        font-weight: 600;
        box-shadow: none;
    }

    .toolbar-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        margin-bottom: 1rem;
    }

    .toolbar-meta {
        font-size: 13px;
        color: var(--sales-muted);
    }

    .toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .toolbar-actions .form-control {
        min-width: 180px;
        height: 44px;
        border-radius: 14px;
        border-color: #d4e2eb;
        box-shadow: none;
    }

    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .catalog-card {
        position: relative;
        border-radius: 22px;
        padding: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
        border: 1px solid var(--sales-border);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        min-height: 100%;
    }

    .catalog-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(31, 58, 86, 0.12);
    }

    .catalog-card-head {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .catalog-image {
        width: 88px;
        height: 88px;
        border-radius: 18px;
        object-fit: cover;
        border: 1px solid #dbe6ef;
        background: #fff;
        flex: 0 0 88px;
        cursor: zoom-in;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .catalog-image:hover {
        transform: scale(1.03);
        box-shadow: 0 12px 24px rgba(31, 58, 86, 0.16);
    }

    .catalog-body {
        min-width: 0;
        flex: 1 1 auto;
    }

    .focus-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 11px;
        border-radius: 999px;
        background: var(--sales-primary-soft);
        color: var(--sales-primary);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        margin-bottom: 10px;
    }

    .catalog-name {
        margin: 0 0 6px;
        font-size: 16px;
        line-height: 1.35;
        color: var(--sales-text);
        word-break: break-word;
    }

    .catalog-code,
    .catalog-supplier {
        margin: 0;
        font-size: 12px;
        color: var(--sales-muted);
        line-height: 1.5;
        word-break: break-word;
    }

    .catalog-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .meta-box {
        border-radius: 16px;
        border: 1px solid #e4edf5;
        background: #f5f9fc;
        padding: 10px 12px;
    }

    .meta-label {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #7a8c9d;
    }

    .meta-value {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
        color: #294157;
        word-break: break-word;
    }

    .status-row {
        margin-top: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-badge.is-on {
        background: #e8f8ee;
        color: #237149;
        border: 1px solid #bfe6ce;
    }

    .status-badge.is-off {
        background: #f3f6f9;
        color: #7c90a1;
        border: 1px solid #dde7ef;
    }

    .catalog-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 15px;
    }

    .catalog-actions .btn {
        flex: 1 1 calc(33.333% - 8px);
        min-width: 0;
        border-radius: 14px;
        padding: 10px 12px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 10px 22px rgba(96, 150, 180, 0.12);
    }

    .catalog-actions .btn:hover {
        box-shadow: 0 14px 28px rgba(96, 150, 180, 0.18);
    }

    .catalog-pagination {
        margin-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .pagination-meta {
        font-size: 13px;
        color: var(--sales-muted);
    }

    .pagination-controls {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .pagination-controls .btn {
        border-radius: 999px;
        min-width: 42px;
    }

    .catalog-empty,
    .catalog-loading,
    .catalog-error {
        display: none;
        text-align: center;
        padding: 28px 18px;
        border-radius: 18px;
        border: 1px dashed #cfdae4;
        background: linear-gradient(180deg, #f9fbfd 0%, #f2f7fb 100%);
    }

    .catalog-empty.is-visible,
    .catalog-loading.is-visible,
    .catalog-error.is-visible {
        display: block;
    }

    .catalog-empty strong,
    .catalog-loading strong,
    .catalog-error strong {
        display: block;
        color: var(--sales-text);
        margin-bottom: 6px;
    }

    .catalog-empty span,
    .catalog-loading span,
    .catalog-error span {
        color: var(--sales-muted);
        font-size: 13px;
    }

    .loading-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .skeleton-card {
        border-radius: 22px;
        border: 1px solid #e2eaf1;
        background: #fff;
        padding: 16px;
    }

    .skeleton-wave {
        position: relative;
        overflow: hidden;
        background: #edf3f8;
    }

    .skeleton-wave::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
        animation: salesSkeleton 1.4s infinite;
    }

    .skeleton-avatar {
        width: 88px;
        height: 88px;
        border-radius: 18px;
    }

    .skeleton-line {
        height: 12px;
        border-radius: 999px;
        margin-bottom: 8px;
    }

    .skeleton-line.lg {
        height: 16px;
        width: 78%;
    }

    .skeleton-line.md {
        width: 58%;
    }

    .skeleton-line.sm {
        width: 42%;
        margin-bottom: 0;
    }

    .editor-modal .modal-content {
        overflow: hidden;
    }

    .editor-modal .modal-header {
        background: linear-gradient(135deg, #4a7ea0, #7bb0c9);
        color: #fff;
        border-bottom: 0;
    }

    .editor-modal .modal-title {
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .editor-modal .close {
        color: #fff;
        opacity: 0.95;
    }

    .editor-modal .modal-body {
        background: linear-gradient(180deg, #f8fbfd 0%, #ffffff 100%);
        padding: 1.5rem;
    }

    .product-identity {
        padding: 14px 16px;
        border-radius: 14px;
        background: #eef6fb;
        border: 1px solid #d9eaf5;
        margin-bottom: 1rem;
    }

    .product-identity .label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6b8191;
        margin-bottom: 4px;
    }

    .product-identity .value {
        font-size: 16px;
        font-weight: 600;
        color: #234;
        word-break: break-word;
    }

    .editor-modal .form-control {
        border-radius: 12px;
        min-height: 46px;
        border-color: #cfe0eb;
        box-shadow: none;
    }

    .editor-modal textarea.form-control {
        min-height: 110px;
        resize: vertical;
    }

    .editor-modal .custom-file-label {
        border-radius: 12px;
        height: 46px;
        padding-top: 10px;
        border-color: #cfe0eb;
    }

    .editor-modal .modal-footer {
        border-top: 0;
        background: #fff;
        padding: 1rem 1.5rem 1.4rem;
    }

    .preview-image-box {
        min-height: 240px;
        border-radius: 16px;
        border: 1px dashed #b8cfde;
        background: linear-gradient(135deg, #f7fbfe, #edf5fa);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
    }

    .preview-image-box img {
        max-width: 100%;
        max-height: 220px;
        object-fit: contain;
        border-radius: 12px;
    }

    .product-preview-modal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 26px 60px rgba(18, 34, 53, 0.22);
    }

    .product-preview-modal .modal-header {
        background: linear-gradient(135deg, #274d6a, #4f82a3);
        color: #fff;
        border-bottom: 0;
    }

    .product-preview-modal .close {
        color: #fff;
        opacity: 0.95;
    }

    .product-preview-modal .modal-body {
        padding: 1.2rem;
        background: linear-gradient(180deg, #f8fbfd 0%, #ffffff 100%);
    }

    .product-preview-stage {
        border-radius: 20px;
        border: 1px solid #dce6ef;
        background: linear-gradient(135deg, #f6fbff, #edf4f9);
        min-height: 360px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .product-preview-stage img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: 16px;
        box-shadow: 0 18px 36px rgba(31, 58, 86, 0.16);
    }

    .product-preview-caption {
        margin-top: 12px;
        text-align: center;
    }

    .product-preview-caption strong {
        display: block;
        color: var(--sales-text);
        font-size: 15px;
    }

    .product-preview-caption span {
        display: block;
        margin-top: 4px;
        color: var(--sales-muted);
        font-size: 12px;
        word-break: break-word;
    }

    .ajax-feedback {
        display: none;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        margin-bottom: 1rem;
    }

    .ajax-feedback.is-success {
        display: block;
        background: #e9f8ef;
        color: #1f7a45;
        border: 1px solid #bde8cc;
    }

    .ajax-feedback.is-error {
        display: block;
        background: #fff0f0;
        color: #b53a3a;
        border: 1px solid #f3c4c4;
    }

    @keyframes salesSkeleton {
        100% {
            transform: translateX(100%);
        }
    }

    @media (max-width: 1199.98px) {

        .catalog-grid,
        .loading-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .sales-dashboard {
            padding: 0 4px 18px;
        }

        .stats-row {
            margin-top: 0.75rem;
        }

        .stats-row .info-box {
            min-height: 84px;
        }

        .stats-row .info-box-icon {
            min-height: 84px;
            width: 64px;
        }

        .stats-row .info-box-icon svg {
            width: 30px;
            height: 30px;
        }

        .stats-row .info-box-number {
            font-size: 20px;
        }

        .catalog-topbar,
        .toolbar-row {
            grid-template-columns: 1fr;
        }

        .filter-section {
            gap: 8px;
        }

        .filter-label {
            width: 100%;
            min-width: 0;
            padding-top: 0;
            margin-bottom: 2px;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group .btn {
            flex: 1 1 calc(50% - 8px);
            min-width: 0;
            padding: 8px 10px;
        }

        .catalog-grid,
        .loading-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {

        .catalog-shell .card-body,
        .filter-card .card-body {
            padding: 0.9rem;
        }

        .search-panel,
        .summary-panel {
            padding: 0.9rem;
        }

        .summary-number {
            font-size: 26px;
        }

        .catalog-card {
            padding: 14px;
            border-radius: 18px;
        }

        .catalog-card-head {
            gap: 12px;
        }

        .catalog-image {
            width: 74px;
            height: 74px;
            flex-basis: 74px;
            border-radius: 15px;
        }

        .catalog-name {
            font-size: 15px;
        }

        .catalog-meta {
            grid-template-columns: 1fr;
        }

        .catalog-actions .btn {
            flex: 1 1 calc(50% - 8px);
        }

        .editor-modal .modal-dialog {
            margin: 0.75rem;
        }

        .editor-modal .modal-body,
        .editor-modal .modal-footer {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .editor-modal .modal-footer {
            gap: 8px;
        }

        .editor-modal .modal-footer .btn {
            width: 100%;
        }

        .preview-image-box {
            min-height: 180px;
        }

        .product-preview-stage {
            min-height: 250px;
            padding: 12px;
        }
    }
</style>
<?php $this->load->view('partial/katalog/navbar') ?>
<div class="content-wrapper">

    <div class="content-header">
        <div class="container"></div>
    </div>
    <?php if ($this->session->userdata('hak_akses') == '4') : ?>
        <div class="sales-dashboard">
            <div class="row mb-3 mt-3 px-2 stats-row">
                <div class="col-6 col-md-3 mb-2">
                    <div class="info-box shadow-sm mb-0">
                        <span class="info-box-icon bg-primary">
                            <i class="fa fa-boxes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Produk</span>
                            <span class="info-box-number"><?= $statistik['total'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="info-box shadow-sm mb-0" style="border-left: 4px solid #ee4d2d;">
                        <span class="info-box-icon d-flex align-items-center justify-content-center" style="background-color:#ee4d2d;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                                <g stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                    <path d="m4 7l.867 12.143a2 2 0 0 0 2 1.857h10.276a2 2 0 0 0 2-1.857L20.01 7z" />
                                    <path d="M8.5 7c0-1.653 1.5-4 3.5-4s3.5 2.347 3.5 4" />
                                    <path d="M9.5 17c.413.462 1 1 2.5 1s2.5-.897 2.5-2s-1-1.5-2.5-2s-2-1.47-2-2c0-1.104 1-2 2-2s1.5 0 2.5 1" />
                                </g>
                            </svg>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Shopee</span>
                            <span class="info-box-number"><?= $statistik['shopee'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="info-box shadow-sm mb-0" style="border-left: 4px solid #42b549;">
                        <span class="info-box-icon d-flex align-items-center justify-content-center" style="background-color:#42b549;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 48 48">
                                <g stroke="white" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="4">
                                    <path d="M27.043 12.942c-3.43-2.897-16.85-2.247-16.85-2.247l-.473 32.65s17.855.134 23.353 0s9.341-4.508 9.4-7.878s0-24.18 0-24.18c-6.858-.829-11.942-.178-15.43 1.655" />
                                    <circle cx="19.531" cy="24.172" r="6.976" />
                                    <path d="M32.043 29.33a6.272 6.272 0 1 0-2.3-1.786" />
                                </g>
                            </svg>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tokopedia</span>
                            <span class="info-box-number"><?= $statistik['tokopedia'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="info-box shadow-sm mb-0" style="border-left: 4px solid #6096B4;">
                        <span class="info-box-icon" style="background-color:#6096B4;">
                            <i class="fa fa-shopping-cart" style="color:white;"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">KiuShop</span>
                            <span class="info-box-number"><?= $statistik['kiushop'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3 px-2">
                <div class="col-12">
                    <div class="card shadow-sm filter-card">
                        <div class="card-body py-2">
                            <div class="filter-section mb-2">
                                <label class="filter-label"><strong>Produk Fokus:</strong></label>
                                <div class="filter-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus active" data-fokus="">Semua</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="A">A</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="B">B</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="C">C</button>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="filter-section">
                                <label class="filter-label"><strong>Online Shop:</strong></label>
                                <div class="filter-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-filter-online active" data-online="">Semua</button>
                                    <button type="button" class="btn btn-sm btn-filter-online" data-online="shopee" style="border:1px solid #ee4d2d; color:#ee4d2d; background:white;">Shopee</button>
                                    <button type="button" class="btn btn-sm btn-filter-online" data-online="tokopedia" style="border:1px solid #42b549; color:#42b549; background:white;">Tokopedia</button>
                                    <button type="button" class="btn btn-sm btn-filter-online" data-online="kiushop" style="border:1px solid #6096B4; color:#6096B4; background:white;">KiuShop</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-filter-online" data-online="kosong">
                                        <i class="fa fa-times-circle"></i> Belum Ada
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else : ?>
            <div class="row mb-3 px-2">
                <div class="col-12">
                    <div class="card shadow-sm filter-card">
                        <div class="card-body py-2">
                            <div class="filter-section mb-2">
                                <label class="filter-label"><strong>Produk Fokus:</strong></label>
                                <div class="filter-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus active" data-fokus="">Semua</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="A">A</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="B">B</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-filter-fokus" data-fokus="C">C</button>
                                </div>
                            </div>
                            <hr class="my-2">
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>





        <div class="content px-2" style="margin-top:-10px;">
            <div class="row">
                <div class="col-lg">
                    <div class="card shadow-sm catalog-shell">
                        <div class="card-body">
                            <div class="catalog-topbar">
                                <div class="search-panel">
                                    <span class="panel-title">Pencarian Cepat</span>
                                    <div class="search-input-wrap">
                                        <i class="fa fa-search"></i>
                                        <input type="text" class="form-control search-input" id="catalogSearchInput" placeholder="Cari nama barang, kode, supplier, kelompok, atau bahan aktif">
                                        <button type="button" class="search-clear" id="catalogSearchClear" aria-label="Bersihkan pencarian">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="summary-panel">
                                    <span class="panel-title">Ringkasan Hasil</span>
                                    <div class="summary-number" id="catalogResultCount"><?= (int) $statistik['total'] ?></div>
                                    <div class="summary-caption" id="catalogResultCaption">Menampilkan seluruh katalog produk sales.</div>
                                </div>

                            </div>

                            <div class="toolbar-row" hidden>
                                <div class="toolbar-meta" id="catalogPagingMeta">Siap memuat data katalog.</div>
                                <div class="toolbar-actions">
                                    <select id="catalogSortSelect" class="form-control" hidden>
                                        <option value="fokus">Urutkan: Fokus Teratas</option>
                                        <option value="terbaru">Urutkan: Data Terbaru</option>
                                        <option value="nama_asc">Urutkan: Nama A-Z</option>
                                        <option value="nama_desc">Urutkan: Nama Z-A</option>
                                        <option value="supplier">Urutkan: Supplier</option>
                                    </select>
                                    <select id="catalogPerPageSelect" class="form-control" hidden>
                                        <option value="6">6 per halaman</option>
                                        <option value="10">10 per halaman</option>
                                        <option value="25">25 per halaman</option>
                                    </select>
                                </div>
                            </div>

                            <div id="catalogLoadingState" class="catalog-loading is-visible">
                                <strong>Memuat katalog...</strong>
                                <span>Menyiapkan data produk terbaru.</span>
                            </div>

                            <div id="catalogLoadingGrid" class="loading-grid">
                                <?php for ($i = 0; $i < 6; $i++) : ?>
                                    <div class="skeleton-card">
                                        <div class="d-flex">
                                            <div class="skeleton-avatar skeleton-wave mr-3"></div>
                                            <div class="flex-fill">
                                                <div class="skeleton-line lg skeleton-wave"></div>
                                                <div class="skeleton-line md skeleton-wave"></div>
                                                <div class="skeleton-line sm skeleton-wave"></div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="skeleton-line skeleton-wave"></div>
                                            <div class="skeleton-line skeleton-wave"></div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <div id="catalogErrorState" class="catalog-error">
                                <strong>Data katalog belum berhasil dimuat.</strong>
                                <span>Periksa koneksi atau coba muat ulang daftar produk.</span>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="catalogRetryButton">
                                        <i class="fa fa-sync-alt mr-1"></i>Coba Lagi
                                    </button>
                                </div>
                            </div>

                            <div id="catalogEmptyState" class="catalog-empty">
                                <strong>Tidak ada produk yang cocok.</strong>
                                <span>Ubah kata kunci pencarian atau kombinasi filter untuk melihat hasil lain.</span>
                            </div>

                            <div id="catalogGrid" class="catalog-grid"></div>

                            <div class="catalog-pagination">
                                <div class="pagination-meta" id="catalogPaginationInfo"></div>
                                <div class="pagination-controls">
                                    <button type="button" class="btn btn-light" id="catalogPrevButton">
                                        <i class="fa fa-angle-left mr-1"></i>Sebelumnya
                                    </button>
                                    <button type="button" class="btn btn-primary" id="catalogPageIndicator">1</button>
                                    <button type="button" class="btn btn-light" id="catalogNextButton">
                                        Berikutnya<i class="fa fa-angle-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

</div>

<div class="modal fade editor-modal" id="modalEditBahanAktif" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formEditBahanAktif">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-pencil-alt mr-2"></i>Edit Bahan Aktif</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="feedbackBahanAktif" class="ajax-feedback"></div>
                    <input type="hidden" name="id_barang" id="edit_bahan_id_barang">

                    <div class="product-identity">
                        <span class="label">Produk</span>
                        <div class="value" id="edit_bahan_nama_barang">-</div>
                        <span class="label mt-3">Kode Barang</span>
                        <div class="value" id="edit_bahan_kode_barang">-</div>
                    </div>

                    <div class="form-group">
                        <label for="edit_kelompok">Kelompok Bahan</label>
                        <input type="text" class="form-control" name="kelompok" id="edit_kelompok" placeholder="Masukkan kelompok bahan">
                    </div>

                    <div class="form-group mb-0">
                        <label for="edit_bahan_aktif">Bahan Aktif</label>
                        <textarea class="form-control" name="bahan_aktif" id="edit_bahan_aktif" placeholder="Masukkan bahan aktif produk"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanBahanAktif">
                        <i class="fa fa-save mr-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade editor-modal" id="modalEditGambarProduk" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formEditGambarProduk" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-image mr-2"></i>Edit Gambar Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="feedbackGambarProduk" class="ajax-feedback"></div>
                    <input type="hidden" name="id_barang" id="edit_gambar_id_barang">

                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="product-identity">
                                <span class="label">Produk</span>
                                <div class="value" id="edit_gambar_nama_barang">-</div>
                                <span class="label mt-3">Kode Barang</span>
                                <div class="value" id="edit_gambar_kode_barang">-</div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="gambar_produk">Upload gambar baru</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="gambar_produk" name="gambar_produk" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                                    <label class="custom-file-label" for="gambar_produk">Pilih file gambar</label>
                                </div>
                                <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 10 MB.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="d-block">Preview</label>
                            <div class="preview-image-box">
                                <img id="previewGambarProduk" src="<?= base_url('images/Karisma.png') ?>" alt="Preview gambar produk">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-info" id="btnSimpanGambarProduk">
                        <i class="fa fa-cloud-upload-alt mr-1"></i>Update Gambar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade product-preview-modal" id="modalPreviewProduk" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-search-plus mr-2"></i>Preview Gambar Produk</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="product-preview-stage">
                    <img id="productPreviewImage" src="<?= base_url('images/Karisma.png') ?>" alt="Preview gambar produk">
                </div>
                <div class="product-preview-caption">
                    <strong id="productPreviewName">Produk</strong>
                    <span id="productPreviewCode">Kode Barang</span>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="main-footer">
    <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong> All rights reserved.
</footer>
</div>