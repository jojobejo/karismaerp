<div class="section-stack">
    <div class="mobile-card">
        <div class="card-title-row">
            <div>
                <span class="app-eyebrow">Form input</span>
                <h2>Laporan Lingkungan</h2>
            </div>
            <span class="status-badge status-muted"><?= date('d M H:i') ?></span>
        </div>

        <div class="form-tabs" role="tablist" aria-label="Jenis form input">
            <button type="button" class="form-tab-button is-active" data-form-tab="issue">Laporan Issue</button>
            <button type="button" class="form-tab-button" data-form-tab="assessment">Penilaian Lingkungan</button>
        </div>

        <?php
        $ratingByScore = array();
        foreach (($rating ?? array()) as $rate) {
            $score = intval($rate->score);
            if ($score >= 1 && $score <= 5) {
                $ratingByScore[$score] = intval($rate->id);
            }
        }
        $defaultIssueRatingId = isset($ratingByScore[5]) ? $ratingByScore[5] : 5;
        ?>

        <form id="mobileIssueForm" class="mobile-submit-form form-tab-panel is-active" data-form-type="issue" enctype="multipart/form-data">
            <input type="hidden" name="submission_type" value="issue">
            <input type="hidden" name="rating_id" value="<?= $defaultIssueRatingId ?>">

            <div class="mb-3">
                <label class="form-label fw-bold">Lokasi</label>
                <select class="form-select mobile-select2" name="location_id" required data-placeholder="Cari lokasi kerja" data-ajax-url="<?= site_url('hrd/penilaian_lingkungan/locations') ?>">
                    <option value=""></option>
                    <?php foreach (($lokasi ?? []) as $loc) : ?>
                        <option value="<?= $loc->id ?>"><?= htmlspecialchars($loc->name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Lokasi wajib dipilih.</div>
            </div>

            <div class="mb-3">
                <label for="issueDescription" class="form-label fw-bold">Deskripsi issue</label>
                <textarea class="form-control mobile-control description-input" id="issueDescription" name="description" maxlength="800" required placeholder="Berikan deskripsi issue pada lingkungan yang dipilih"></textarea>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted-soft">Jelaskan temuan secara singkat dan jelas.</small>
                    <small class="text-muted-soft"><span class="description-count">0</span>/800</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Bukti Foto</label>
                <label class="upload-zone" for="issueEvidence">
                    <span>
                        <i class="fas fa-cloud-arrow-up d-block mb-2"></i>
                        <strong>Upload foto temuan</strong>
                        <small class="d-block">JPG/PNG, maksimal 5MB per file</small>
                    </span>
                </label>
                <input type="file" id="issueEvidence" name="evidence[]" accept="image/jpeg,image/png" class="d-none evidence-input" multiple required>
                <div class="preview-grid evidence-preview"></div>
            </div>

            <button type="submit" class="btn btn-primary mobile-btn w-100">
                <i class="fas fa-paper-plane me-2"></i>Kirim Laporan
            </button>
        </form>

        <form id="mobileAssessmentForm" class="mobile-submit-form form-tab-panel" data-form-type="assessment" enctype="multipart/form-data">
            <input type="hidden" name="submission_type" value="assessment">
            <input type="hidden" name="rating_id" class="rating-id" value="" required>
            <input type="hidden" name="star_rating" class="star-rating-value" value="" required>

            <div class="mb-3">
                <label class="form-label fw-bold">Lokasi</label>
                <select class="form-select mobile-select2" name="location_id" required data-placeholder="Cari lokasi kerja" data-ajax-url="<?= site_url('hrd/penilaian_lingkungan/locations') ?>">
                    <option value=""></option>
                    <?php foreach (($lokasi ?? []) as $loc) : ?>
                        <option value="<?= $loc->id ?>"><?= htmlspecialchars($loc->name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Lokasi wajib dipilih.</div>
            </div>

            <div class="mb-3">
                <div class="rating-head">
                    <label class="form-label fw-bold mb-0">Penilaian Lingkungan</label>
                    <span class="rating-value">Pilih nilai</span>
                </div>
                <div class="star-rating" role="radiogroup" aria-label="Penilaian lingkungan">
                    <?php for ($star = 1; $star <= 5; $star++) : ?>
                        <button
                            type="button"
                            class="star-button"
                            data-score="<?= $star ?>"
                            data-rating-id="<?= isset($ratingByScore[$star]) ? $ratingByScore[$star] : $star ?>"
                            aria-label="Nilai <?= $star ?>"
                            aria-checked="false"
                            role="radio">
                            <i class="far fa-star"></i>
                        </button>
                    <?php endfor; ?>
                </div>
                <small class="text-muted-soft">Klik bintang sesuai nilai kebersihan/kenyamanan lingkungan.</small>
            </div>

            <div class="mb-3">
                <label for="assessmentDescription" class="form-label fw-bold">Deskripsi penilaian</label>
                <textarea class="form-control mobile-control description-input" id="assessmentDescription" name="description" maxlength="800" required placeholder="Berikan deskripsi penilaian terhadap lingkungan yang dipilih"></textarea>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted-soft">Jelaskan temuan secara singkat dan jelas dan berikan penilaian anda</small>
                    <small class="text-muted-soft"><span class="description-count">0</span>/800</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mobile-btn w-100">
                <i class="fas fa-paper-plane me-2"></i>Kirim Penilaian
            </button>
        </form>
    </div>

    <div class="mobile-card">
        <div class="d-flex align-items-start gap-3">
            <span class="stat-icon"><i class="far fa-user"></i></span>
            <div>
                <strong>Pelapor</strong>
                <p class="text-muted-soft mb-0"><?= htmlspecialchars($created_by ?: 'User Operasional', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
</div>
