<?php
if (!function_exists('retur_dashboard_format_tanggal')) {
    function retur_dashboard_format_tanggal($date)
    {
        if (!$date || $date === '0000-00-00') {
            return '-';
        }

        $ts = strtotime($date);
        if (!$ts) {
            return '-';
        }

        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        return $hari[(int)date('w', $ts)] . ', ' . date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }
}

if (!function_exists('retur_dashboard_badge')) {
    function retur_dashboard_badge($status)
    {
        $status = (string)$status;
        if (in_array($status, ['POSTED', 'selesai', '1'], true)) {
            return 'success';
        }
        if (in_array($status, ['DRAFT', 'SUBMITTED', 'menunggu_verifikasi', 'menunggu_collection', 'menunggu_kasir', '2'], true)) {
            return 'warning';
        }
        if (in_array($status, ['PURCHASING_VERIFIED', 'ACCOUNTING_VERIFIED', 'terverifikasi'], true)) {
            return 'info';
        }
        if (in_array($status, ['VOID', 'POSTING_EXCEPTION', 'ditolak'], true)) {
            return 'danger';
        }
        return 'secondary';
    }
}

if (!function_exists('retur_dashboard_status_label')) {
    function retur_dashboard_status_label($status)
    {
        $labels = [
            '1' => 'Done',
            '2' => 'Pending',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'menunggu_collection' => 'Menunggu Collection',
            'menunggu_kasir' => 'Menunggu Kasir',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
        ];

        return $labels[(string)$status] ?? (string)$status;
    }
}

if (!function_exists('retur_dashboard_format_money')) {
    function retur_dashboard_format_money($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }
        return number_format((float)$value, 2, ',', '.');
    }
}
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">

                    <div class="card">
                        <div class="card-body">
                            <div class="container-fluid">
                                <div class="row mb-3">
                                    <div class="col-auto">
                                        <a class="btn btn-secondary" href="<?= base_url('dashboard') ?>">
                                            <i class="fas fa-arrow-left"></i> Kembali Dashboard
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <a class="btn btn-primary" href="<?= base_url('ics/icspo') ?>">
                                            <i class="fas fa-warehouse"></i> Kembali Data LPB
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <a class="btn btn-success" href="<?= base_url('ics/retur/pembelian') ?>">
                                            <i class="fas fa-plus-circle"></i> Input Retur
                                        </a>
                                    </div>
                                </div>
                                <?php if ($this->session->userdata('jobdesk') == 'ADMINLOGLPB') : ?>
                                    <div class="row">
                                        <div class="col-auto">
                                            <a class="btn btn-secondary mb-3 " href="<?= base_url('ics/retur/penjualan') ?>">
                                                <i class="fas fa-file-csv"></i> Retur
                                            </a>
                                        </div>
                                        <div class="col-auto">
                                            <a class="btn btn-success mb-3 " href="<?= base_url('ics/retur/penjualan') ?>">
                                                <i class="fas fa-file-csv"></i> Retur Penjualan
                                            </a>
                                        </div>
                                        <div class="col-auto">
                                            <a class="btn btn-success mb-3 " href="<?= base_url('ics/retur/pembelian') ?>">
                                                <i class="fas fa-file-csv"></i> Retur Pembelian
                                            </a>
                                        </div>
                                        <div class="col-auto">
                                            <a class="btn btn-warning mb-3 " href="<?= base_url('ics/retur/pembelian/adjustment') ?>">
                                                <i class="fas fa-balance-scale"></i> Adjustment Harga LPB
                                            </a>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="row">
                                        <div class="col-auto">
                                            <a class="btn btn-secondary mb-3 " href="<?= base_url('ics/retur/penjualan') ?>">
                                                <i class="fas fa-file-csv"></i> Retur
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <table class="table table-bordered table-striped table-sm" id="tb_retur_all">
                                    <thead class="bg-primary text-white text-center">
                                        <tr>
                                            <th>Tanggal Retur</th>
                                            <th>Jenis Retur</th>
                                            <th>No Retur</th>
                                            <th>LPB</th>
                                            <th>Referensi</th>
                                            <th>Partner</th>
                                            <th>Note Retur</th>
                                            <th class="text-small text-nowrap">Item</th>
                                            <th class="text-small text-nowrap">DPP</th>
                                            <th class="text-small text-nowrap">PPN</th>
                                            <th class="text-small text-nowrap">Total</th>
                                            <th class="text-small text-nowrap">Status</th>
                                            <th class="text-small text-nowrap">#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($retur_all)) : ?>
                                            <?php foreach ($retur_all as $row) : ?>
                                                <tr>
                                                    <td data-order="<?= html_escape($row->sort_at ?: $row->tanggal_retur) ?>">
                                                        <?= html_escape(retur_dashboard_format_tanggal($row->tanggal_retur)) ?>
                                                    </td>
                                                    <td class="text-center text-nowrap"><?= html_escape($row->jenis_retur) ?></td>
                                                    <td class="text-nowrap"><?= html_escape($row->no_retur) ?></td>
                                                    <td class="text-nowrap"><?= html_escape($row->nomor_lpb) ?></td>
                                                    <td class="text-nowrap"><?= html_escape($row->nomor_po) ?></td>
                                                    <td><?= html_escape($row->partner) ?></td>
                                                    <td><?= html_escape($row->keterangan ?: '-') ?></td>
                                                    <td class="text-center"><?= (int)$row->total_item ?></td>
                                                    <td class="text-right"><?= retur_dashboard_format_money($row->total_dpp) ?></td>
                                                    <td class="text-right"><?= retur_dashboard_format_money($row->total_ppn) ?></td>
                                                    <td class="text-right"><?= retur_dashboard_format_money($row->grand_total) ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-<?= retur_dashboard_badge($row->status) ?>">
                                                            <?= html_escape(retur_dashboard_status_label($row->status)) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($row->source_type === 'purchase_return') : ?>
                                                            <a class="btn btn-sm btn-info" href="<?= base_url('ics/retur/pembelian') ?>" title="Retur Pembelian">
                                                                <i class="fas fa-truck-loading"></i>
                                                            </a>
                                                        <?php elseif ($row->source_type === 'sales_return') : ?>
                                                            <a class="btn btn-sm btn-success" href="<?= base_url('retur_penjualan/retur/detail/' . (int)$row->source_id) ?>" title="Detail Retur Penjualan">
                                                                Detail
                                                            </a>
                                                        <?php else : ?>
                                                            <a class="btn btn-sm btn-primary" href="<?= base_url('ics/retur/detail_retur/' . rawurlencode($row->no_retur)) ?>">
                                                                Detail
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <script>
        $(function() {
            $('#tb_retur_all').DataTable({
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                        targets: [1, 2, 3, 4, 7, 8, 9, 10, 11],
                        width: '1%',
                        className: 'text-center text-nowrap'
                    },
                    {
                        targets: [8, 9, 10],
                        className: 'text-right text-nowrap'
                    }
                ]
            });
        });
    </script>
