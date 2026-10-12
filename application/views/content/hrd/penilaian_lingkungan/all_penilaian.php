<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper env-page">
            <section class="content-header pb-2">
                <div class="container-fluid">
                    <div class="env-toolbar">
                        <div>
                            <h1 class="env-title">All Data Penilaian Lingkungan</h1>
                            <p class="env-subtitle mb-0">Daftar nilai penilaian lokasi yang telah diinput.</p>
                        </div>
                        <div class="btn-group env-nav">
                            <a href="<?= base_url('hrd/penilaian_lingkungan/admin') ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-chart-pie mr-1"></i> Dashboard</a>
                            <a href="<?= base_url('hrd/penilaian_lingkungan/semua-penilaian') ?>" class="btn btn-sm btn-dark active"><i class="fas fa-list mr-1"></i> All Data</a>
                            <a href="<?= base_url('penilaian_lingkungan') ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-plus mr-1"></i> Form</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card env-card">
                        <div class="card-body py-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-3 mb-md-0">
                                    <label>Lokasi</label>
                                    <select id="filterLocation" class="form-control form-control-sm">
                                        <option value="">Semua lokasi</option>
                                        <?php foreach ($lokasi as $loc) : ?>
                                            <option value="<?= $loc->id ?>"><?= htmlspecialchars($loc->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 mb-md-0">
                                    <label>Status</label>
                                    <select id="filterStatus" class="form-control form-control-sm">
                                        <option value="">Semua status</option>
                                        <?php foreach ($status as $st) : ?>
                                            <option value="<?= $st->id ?>"><?= htmlspecialchars($st->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 mb-md-0">
                                    <label>Mulai</label>
                                    <input id="filterFrom" type="date" class="form-control form-control-sm">
                                </div>
                                <div class="form-group col-md-2 mb-md-0">
                                    <label>Akhir</label>
                                    <input id="filterTo" type="date" class="form-control form-control-sm">
                                </div>
                                <div class="form-group col-md-2 mb-md-0">
                                    <button id="reloadAllAssessments" class="btn btn-sm btn-dark btn-block"><i class="fas fa-sync-alt mr-1"></i> Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card env-card">
                        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold">Data Penilaian Terinput</h3>
                            <span class="badge badge-light" id="allAssessmentCount">0 data</span>
                        </div>
                        <div class="card-body pt-2">
                            <div id="allAssessmentFeedback"></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover env-table" id="allAssessmentTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal</th>
                                            <th>Lokasi</th>
                                            <th>Nilai</th>
                                            <th>Deskripsi</th>
                                            <th>Pelapor</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="issueUpdateModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content env-modal">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Penilaian</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form id="issueUpdateForm" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <div id="updateFeedback"></div>
                                        <input type="hidden" name="issue_id" id="updateIssueId">
                                        <input type="hidden" name="update_context" value="assessment">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label>Nilai</label>
                                                <select class="form-control" name="rating_id" id="updateRating" required>
                                                    <option value="">Pilih nilai</option>
                                                    <?php foreach ($rating as $rate) : ?>
                                                        <option value="<?= $rate->id ?>"><?= htmlspecialchars($rate->name) ?> (<?= $rate->score ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Status</label>
                                                <select class="form-control" name="status_id" id="updateStatus" required>
                                                    <option value="">Pilih status</option>
                                                    <?php foreach ($status as $st) : ?>
                                                        <option value="<?= $st->id ?>"><?= htmlspecialchars($st->name) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Tanggal penyelesaian</label>
                                                <input type="date" class="form-control" name="due_date" id="updateDueDate">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Catatan update</label>
                                            <textarea class="form-control" name="note" id="updateNote" rows="3"></textarea>
                                        </div>
                                        <div id="currentEvidence" class="mb-3"></div>
                                        <div id="historyLogs"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-dark"><i class="fas fa-save mr-1"></i> Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
