<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model laporan Daftar Tagihan Piutang (TAGPD).
 * Menyusun data tagihan faktur penjualan & faktur pecahan beserta mutasi pelunasan bulanan.
 */
class M_LaporanTagpd extends CI_Model
{
    /**
     * Daftar huruf kolom Excel A sampai AT (46 kolom) sesuai format TAGPD.
     *
     * @return array
     */
    public function get_column_letters()
    {
        $letters = [];
        for ($i = 0; $i < 46; $i++) {
            if ($i < 26) {
                $letters[] = chr(65 + $i);
            } else {
                $first = intdiv($i, 26) - 1;
                $second = $i % 26;
                $letters[] = chr(65 + $first) . chr(65 + $second);
            }
        }
        return $letters;
    }

    /**
     * Mengambil data lengkap laporan TAGPD beserta total ringkasan dan opsi filter.
     *
     * @param string $todayDate Tanggal acuan (Today) format YYYY-MM-DD
     * @param array  $filters   Filter opsional: sales, status_bayar, keyword
     * @return array
     */
    public function get_tagpd_report($todayDate, array $filters = [])
    {
        $todayDate = $this->normalize_date($todayDate);
        $monthStart = date('Y-m-01', strtotime($todayDate));
        $monthEnd = $todayDate;

        $salesFilter = trim((string)($filters['sales'] ?? ''));
        $statusFilter = trim((string)($filters['status_bayar'] ?? ''));
        $keyword = strtolower(trim((string)($filters['keyword'] ?? '')));

        $invoices = $this->fetch_all_invoices($todayDate);
        $paymentsByKey = $this->fetch_payments_map($todayDate);
        $pecahPaymentsByNo = $this->fetch_pecah_journal_payments_map($todayDate);

        $rows = [];
        $salesOptionsMap = [];
        $totals = [
            'saldo_awal'            => 0.0,
            'saldo_awal_faktur'     => 0.0,
            'jumlah_faktur'         => 0.0,
            'pelunasan_retur_pot'   => 0.0,
            'pelunasan_bg'          => 0.0,
            'pelunasan_bg_cair'     => 0.0,
            'pelunasan_ku_cash'     => 0.0,
            'saldo_pd'              => 0.0,
            'saldo_pd_faktur'       => 0.0,
        ];

        $rowIndex = 0;
        foreach ($invoices as $inv) {
            $salesName = trim((string)($inv['salesman'] ?? ''));
            if ($salesName !== '') {
                $salesOptionsMap[$salesName] = $salesName;
            }

            $totalTagihan = (float)$inv['total_tagihan'];
            $tglFaktur = (string)$inv['tanggal_faktur'];
            $isBeforeMonth = ($tglFaktur !== '' && $tglFaktur < $monthStart);
            $isInMonth = ($tglFaktur !== '' && $tglFaktur >= $monthStart && $tglFaktur <= $monthEnd);

            // Hitung pelunasan sebelum bulan berjalan dan selama bulan berjalan
            $paymentSummary = $this->summarize_invoice_payments(
                $inv,
                $monthStart,
                $monthEnd,
                $paymentsByKey,
                $pecahPaymentsByNo
            );

            $saldoAwal = $isBeforeMonth ? max(0.0, round($totalTagihan - $paymentSummary['paid_before_month'], 2)) : 0.0;
            $jumlahFaktur = $isInMonth ? round($totalTagihan, 2) : 0.0;

            // Abaikan faktur lama yang sudah lunas sebelum awal bulan berjalan dan tidak ada mutasi bulan ini
            if ($isBeforeMonth && $saldoAwal <= 0.009 && $paymentSummary['total_paid_in_month'] <= 0.009 && $paymentSummary['bg_pending_in_month'] <= 0.009) {
                continue;
            }

            $totalPelunasanEfective = $paymentSummary['retur_pot_in_month']
                + $paymentSummary['bg_cair_in_month']
                + $paymentSummary['ku_cash_in_month'];

            $saldoPd = max(0.0, round(($saldoAwal + $jumlahFaktur) - $totalPelunasanEfective, 2));

            // Terapkan filter Sales
            if ($salesFilter !== '' && strcasecmp($salesName, $salesFilter) !== 0) {
                continue;
            }

            // Terapkan filter Status Piutang
            if ($statusFilter === 'belum_lunas' && $saldoPd <= 0.009) {
                continue;
            }
            if ($statusFilter === 'lunas' && $saldoPd > 0.009) {
                continue;
            }

            // Terapkan filter Pencarian
            if ($keyword !== '') {
                $haystack = strtolower(
                    ($inv['no_faktur'] ?? '') . ' ' .
                    ($inv['kd_customer'] ?? '') . ' ' .
                    ($inv['nama_customer_toko'] ?? '') . ' ' .
                    ($inv['nama_customer'] ?? '') . ' ' .
                    $salesName
                );
                if (strpos($haystack, $keyword) === false) {
                    continue;
                }
            }

            $btsJt = $this->calculate_bts_jt($todayDate, (string)($inv['tanggal_jatuh_tempo'] ?? ''));
            $rowIndex++;

            $row = [
                'no'                        => $rowIndex,
                'a_q'                       => '', // Belum tersedia di sistem
                'tanggal_faktur'            => $tglFaktur,
                'tanggal_tax'               => '', // Belum tersedia di sistem
                'no_faktur'                 => (string)($inv['no_faktur'] ?? ''),
                'fp'                        => '', // Belum tersedia di sistem
                'plafon'                    => $inv['plafon'] !== null ? (float)$inv['plafon'] : '',
                'sales'                     => $salesName,
                'collection'                => trim((string)($inv['collection'] ?? '')),
                'tgl_masuk_kios_baru'       => '', // Belum tersedia di sistem
                'metode_bayar'              => (string)($inv['cara_pembayaran'] ?? ''),
                'kode_customer'             => (string)($inv['kd_customer'] ?? ''),
                'nama_customer_toko'        => (string)($inv['nama_customer_toko'] ?? ''),
                'nama_customer'             => (string)($inv['nama_customer'] ?? ''),
                'tax_alamat'                => (string)($inv['tax_alamat'] ?? ''),
                'tax_kota'                  => (string)($inv['tax_kota'] ?? ''),
                'pengiriman_alamat'         => (string)($inv['pengiriman_alamat'] ?? ''),
                'pengiriman_kota'           => (string)($inv['pengiriman_kota'] ?? ''),
                'wilayah1'                  => '', // Belum tersedia di sistem
                'se'                        => '', // Belum tersedia di sistem
                'jatuh_tempo'               => $inv['tempo'] !== null ? (int)$inv['tempo'] : '',
                'tanggal_jt'                => (string)($inv['tanggal_jatuh_tempo'] ?? ''),
                'bts_jt_hr'                 => $btsJt,
                'saldo_awal'                => $saldoAwal > 0 ? $saldoAwal : '',
                'saldo_awal_faktur'         => $saldoAwal > 0 ? $saldoAwal : '',
                'jumlah_faktur'             => $jumlahFaktur > 0 ? $jumlahFaktur : '',
                'pelunasan_retur_pot'       => $paymentSummary['retur_pot_in_month'] > 0 ? $paymentSummary['retur_pot_in_month'] : '',
                'pelunasan_bg'              => $paymentSummary['bg_pending_in_month'] > 0 ? $paymentSummary['bg_pending_in_month'] : '',
                'tgl_jt_bg'                 => $paymentSummary['tgl_jt_bg'],
                'no_bg'                     => $paymentSummary['no_bg'],
                'pelunasan_bg_cair'         => $paymentSummary['bg_cair_in_month'] > 0 ? $paymentSummary['bg_cair_in_month'] : '',
                'tgl_bg_cair'               => $paymentSummary['tgl_bg_cair'],
                'pelunasan_ku_cash'         => $paymentSummary['ku_cash_in_month'] > 0 ? $paymentSummary['ku_cash_in_month'] : '',
                'tanggal_pelunasan'         => $paymentSummary['tgl_pelunasan'],
                'diselesaikan_dg'           => $paymentSummary['diselesaikan_dg'],
                'tgl_regt_ku'               => $paymentSummary['tgl_regt_ku'],
                'saldo_pd'                  => $saldoPd,
                'saldo_pd_faktur'           => $saldoPd,
                // Kolom tambahan akhir
                'program_bonus_otp'         => '', // Belum tersedia di sistem
                'keterangan'                => trim((string)($inv['catatan'] ?? '')),
                'ph_bonus_faktur'           => '', // Belum tersedia di sistem
                'jumlah_ph_bonus_faktur'    => '', // Belum tersedia di sistem
                'cocokan_saldo_1'           => '', // Belum tersedia di sistem
                'cocokan_saldo_2'           => '', // Belum tersedia di sistem
                'cocokan_saldo_3'           => '', // Belum tersedia di sistem
                'bts_jt_hari'               => $btsJt,
            ];

            $rows[] = $row;

            $totals['saldo_awal']          += $saldoAwal;
            $totals['saldo_awal_faktur']   += $saldoAwal;
            $totals['jumlah_faktur']       += $jumlahFaktur;
            $totals['pelunasan_retur_pot'] += $paymentSummary['retur_pot_in_month'];
            $totals['pelunasan_bg']        += $paymentSummary['bg_pending_in_month'];
            $totals['pelunasan_bg_cair']   += $paymentSummary['bg_cair_in_month'];
            $totals['pelunasan_ku_cash']   += $paymentSummary['ku_cash_in_month'];
            $totals['saldo_pd']            += $saldoPd;
            $totals['saldo_pd_faktur']     += $saldoPd;
        }

        ksort($salesOptionsMap);

        return [
            'today_date'    => $todayDate,
            'month_start'   => $monthStart,
            'rows'          => $rows,
            'totals'        => $totals,
            'sales_options' => array_values($salesOptionsMap),
        ];
    }

    /**
     * Mengambil semua faktur penjualan aktif (induk non-pecah + pecahan H).
     *
     * @param string $cutoffDate
     * @return array
     */
    private function fetch_all_invoices($cutoffDate)
    {
        $cutoffSql = $this->db->escape($cutoffDate);
        $hasSplitParent = $this->db->field_exists('is_split_parent', 'tbso_faktur_penjualan');
        $hasParentId = $this->db->field_exists('parent_id_faktur', 'tbso_faktur_penjualan');
        $hasCustAcak = $this->db->table_exists('tb_customer_acak');
        $hasCollectionCol = $this->db->field_exists('nama_collection', 'tb_customer');

        $splitFilter = $hasSplitParent ? 'AND COALESCE(f.is_split_parent, 0) = 0' : '';
        $parentFilter = $hasParentId ? 'AND f.parent_id_faktur IS NULL' : '';
        $normalCollectionExpr = $hasCollectionCol ? "COALESCE(c.nama_collection, '')" : "''";

        $sqlNormal = "
            SELECT
                'NORMAL' AS source_type,
                f.id_faktur AS source_id,
                f.no_faktur,
                f.tanggal_faktur,
                f.tanggal_jatuh_tempo,
                COALESCE(NULLIF(f.tempo, 0), f.jtempo, 0) AS tempo,
                f.kd_customer,
                COALESCE(NULLIF(f.salesman, ''), c.nama_sales, '') AS salesman,
                {$normalCollectionExpr} AS collection,
                UPPER(COALESCE(f.cara_pembayaran, '')) AS cara_pembayaran,
                c.plafon_aktif AS plafon,
                COALESCE(NULLIF(c.nama_kios, ''), f.customer_name, '') AS nama_customer_toko,
                COALESCE(NULLIF(c.nama_customer, ''), f.customer_name, '') AS nama_customer,
                '' AS tax_alamat,
                '' AS tax_kota,
                COALESCE(c.alamat_kios, '') AS pengiriman_alamat,
                COALESCE(c.regional, '') AS pengiriman_kota,
                COALESCE(f.catatan, '') AS catatan,
                0 AS skema_potongan,
                0.00 AS pecah_total_bayar,
                NULL AS pecah_tanggal_bayar,
                '' AS pecah_metode_pembayaran,
                COALESCE(fd.total_tagihan, 0) AS total_tagihan
            FROM tbso_faktur_penjualan f
            LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
            LEFT JOIN (
                SELECT id_faktur, COALESCE(SUM(total_harga), 0) AS total_tagihan
                FROM tbso_faktur_detail
                GROUP BY id_faktur
            ) fd ON fd.id_faktur = f.id_faktur
            WHERE f.status IN ('selesai', 'selesai_do')
              {$parentFilter}
              {$splitFilter}
              AND f.tanggal_faktur <= {$cutoffSql}
        ";

        $results = $this->db->query($sqlNormal)->result_array();

        // Gabungkan Faktur Pecahan H jika tabel tbso_faktur_z_pecah tersedia
        if ($this->db->table_exists('tbso_faktur_z_pecah') && $this->db->table_exists('tbso_faktur_z_pecah_detail')) {
            $custAcakJoin = $hasCustAcak
                ? "LEFT JOIN tb_customer_acak ca ON ca.kd_customer = p.kd_customer
                   LEFT JOIN tb_customer ci ON ci.kd_customer = ca.kd_customer_induk"
                : "";
            $plafonExpr = $hasCustAcak ? "COALESCE(c.plafon_aktif, ci.plafon_aktif)" : "c.plafon_aktif";
            $salesExpr = $hasCustAcak
                ? "COALESCE(NULLIF(p.salesman, ''), c.nama_sales, ci.nama_sales, '')"
                : "COALESCE(NULLIF(p.salesman, ''), c.nama_sales, '')";
            $pecahCollectionExpr = $hasCollectionCol
                ? ($hasCustAcak ? "COALESCE(NULLIF(c.nama_collection, ''), ci.nama_collection, '')" : "COALESCE(c.nama_collection, '')")
                : "''";
            $tokoExpr = $hasCustAcak
                ? "COALESCE(NULLIF(c.nama_kios, ''), NULLIF(ca.nama_toko, ''), p.customer_name, '')"
                : "COALESCE(NULLIF(c.nama_kios, ''), p.customer_name, '')";
            $custExpr = $hasCustAcak
                ? "COALESCE(NULLIF(c.nama_customer, ''), NULLIF(ca.kontak_person, ''), p.customer_name, '')"
                : "COALESCE(NULLIF(c.nama_customer, ''), p.customer_name, '')";
            $taxAlamatExpr = $hasCustAcak ? "COALESCE(ca.alamat, '')" : "''";
            $taxKotaExpr = $hasCustAcak ? "COALESCE(ca.kota, '')" : "''";
            $shipAlamatExpr = $hasCustAcak
                ? "COALESCE(NULLIF(c.alamat_kios, ''), NULLIF(ci.alamat_kios, ''), ca.alamat, '')"
                : "COALESCE(c.alamat_kios, '')";
            $shipKotaExpr = $hasCustAcak
                ? "COALESCE(NULLIF(c.regional, ''), NULLIF(ci.regional, ''), ca.kota, '')"
                : "COALESCE(c.regional, '')";

            $skemaPotonganExpr = $this->db->field_exists('skema_potongan', 'tbso_faktur_z_pecah')
                ? "COALESCE(p.skema_potongan, 20)"
                : "20";
            $pecahTotalBayarExpr = $this->db->field_exists('total_bayar', 'tbso_faktur_z_pecah')
                ? "COALESCE(p.total_bayar, 0)"
                : "0";
            $pecahTanggalBayarExpr = $this->db->field_exists('tanggal_bayar', 'tbso_faktur_z_pecah')
                ? "p.tanggal_bayar"
                : "NULL";
            $pecahMetodeBayarExpr = $this->db->field_exists('metode_pembayaran', 'tbso_faktur_z_pecah')
                ? "COALESCE(p.metode_pembayaran, '')"
                : "''";

            $sqlPecah = "
                SELECT
                    'PECAH' AS source_type,
                    p.id_pecah AS source_id,
                    p.no_faktur,
                    p.tanggal_faktur,
                    p.tanggal_jatuh_tempo,
                    COALESCE(NULLIF(p.tempo, 0), p.jtempo, 0) AS tempo,
                    p.kd_customer,
                    {$salesExpr} AS salesman,
                    {$pecahCollectionExpr} AS collection,
                    UPPER(COALESCE(p.cara_pembayaran, '')) AS cara_pembayaran,
                    {$plafonExpr} AS plafon,
                    {$tokoExpr} AS nama_customer_toko,
                    {$custExpr} AS nama_customer,
                    {$taxAlamatExpr} AS tax_alamat,
                    {$taxKotaExpr} AS tax_kota,
                    {$shipAlamatExpr} AS pengiriman_alamat,
                    {$shipKotaExpr} AS pengiriman_kota,
                    COALESCE(p.catatan, '') AS catatan,
                    {$skemaPotonganExpr} AS skema_potongan,
                    {$pecahTotalBayarExpr} AS pecah_total_bayar,
                    {$pecahTanggalBayarExpr} AS pecah_tanggal_bayar,
                    {$pecahMetodeBayarExpr} AS pecah_metode_pembayaran,
                    COALESCE(pd.total_tagihan, 0) AS total_tagihan
                FROM tbso_faktur_z_pecah p
                LEFT JOIN tb_customer c ON c.kd_customer = p.kd_customer
                {$custAcakJoin}
                LEFT JOIN (
                    SELECT id_pecah, COALESCE(SUM(total_harga), 0) AS total_tagihan
                    FROM tbso_faktur_z_pecah_detail
                    GROUP BY id_pecah
                ) pd ON pd.id_pecah = p.id_pecah
                WHERE LOWER(COALESCE(p.status, 'confirmed')) != 'cancelled'
                  AND p.tanggal_faktur <= {$cutoffSql}
            ";

            $pecahResults = $this->db->query($sqlPecah)->result_array();
            $results = array_merge($results, $pecahResults);
        }

        // Urutkan berdasarkan nama toko, lalu tanggal faktur dan nomor faktur
        usort($results, function ($a, $b) {
            $cmpName = strcasecmp((string)$a['nama_customer_toko'], (string)$b['nama_customer_toko']);
            if ($cmpName !== 0) {
                return $cmpName;
            }
            $cmpDate = strcmp((string)$a['tanggal_faktur'], (string)$b['tanggal_faktur']);
            if ($cmpDate !== 0) {
                return $cmpDate;
            }
            return strcmp((string)$a['no_faktur'], (string)$b['no_faktur']);
        });

        return $results;
    }

    /**
     * Mengambil semua pembayaran valid dari tbkeu_pembayaran_faktur sampai tanggal cutoff.
     *
     * @param string $cutoffDate
     * @return array Map by id_faktur
     */
    private function fetch_payments_map($cutoffDate)
    {
        if (!$this->db->table_exists('tbkeu_pembayaran_faktur')) {
            return [];
        }

        $rows = $this->db
            ->select('id_pembayaran, id_faktur, no_faktur, tanggal_pembayaran, jumlah_pembayaran, jumlah_diskon, metode_pembayaran, cara_pembayaran, tanggal_bg_cair, no_bg, status_bg, bg_cair_at')
            ->from('tbkeu_pembayaran_faktur')
            ->where("COALESCE(status, 'POSTED') NOT IN ('UNPOST', 'DRAFT')", null, false)
            ->where("COALESCE(status_kasir, 'valid') != 'pending_kasir'", null, false)
            ->where('tanggal_pembayaran <=', $cutoffDate)
            ->order_by('tanggal_pembayaran', 'ASC')
            ->order_by('id_pembayaran', 'ASC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $idFaktur = (int)($row['id_faktur'] ?? 0);
            if ($idFaktur > 0) {
                $map[$idFaktur][] = $row;
            }
        }

        return $map;
    }

    /**
     * Mengambil jurnal pelunasan Faktur H dari tbkeu_jurnal sampai tanggal cutoff.
     *
     * @param string $cutoffDate
     * @return array Map by no_faktur
     */
    private function fetch_pecah_journal_payments_map($cutoffDate)
    {
        if (!$this->db->table_exists('tbkeu_jurnal')) {
            return [];
        }

        $rows = $this->db
            ->select('id_jurnal, nomor_jurnal, tanggal_transaksi, total_debit, source_no, keterangan')
            ->from('tbkeu_jurnal')
            ->where('source_module', 'KEUANGAN')
            ->where('status', 'POSTED')
            ->where('tanggal_transaksi <=', $cutoffDate)
            ->order_by('tanggal_transaksi', 'ASC')
            ->order_by('id_jurnal', 'ASC')
            ->get()
            ->result_array();

        $map = [];
        foreach ($rows as $row) {
            $noFaktur = trim((string)($row['source_no'] ?? ''));
            if ($noFaktur !== '') {
                $map[$noFaktur][] = $row;
            }
        }

        return $map;
    }

    /**
     * Merangkum mutasi pelunasan faktur (sebelum bulan berjalan dan dalam bulan berjalan).
     *
     * @param array  $inv
     * @param string $monthStart
     * @param string $monthEnd
     * @param array  $paymentsByKey
     * @param array  $pecahPaymentsByNo
     * @return array
     */
    private function summarize_invoice_payments(array $inv, $monthStart, $monthEnd, array $paymentsByKey, array $pecahPaymentsByNo)
    {
        $summary = [
            'paid_before_month'    => 0.0,
            'retur_pot_in_month'   => 0.0,
            'bg_pending_in_month'  => 0.0,
            'bg_cair_in_month'     => 0.0,
            'ku_cash_in_month'     => 0.0,
            'total_paid_in_month'  => 0.0,
            'tgl_jt_bg'            => '',
            'no_bg'                => '',
            'tgl_bg_cair'          => '',
            'tgl_pelunasan'        => '',
            'diselesaikan_dg'      => '',
            'tgl_regt_ku'          => '',
        ];

        if ($inv['source_type'] === 'PECAH') {
            $noFaktur = (string)$inv['no_faktur'];
            $skema = (int)($inv['skema_potongan'] ?? 20);
            $factorNetto = ($skema === 40) ? 0.6 : (($skema === 20) ? 0.8 : 1.0);
            $journals = $pecahPaymentsByNo[$noFaktur] ?? [];

            $methods = [];
            $lastPayDate = '';
            if (!empty($journals)) {
                foreach ($journals as $j) {
                    $tgl = (string)($j['tanggal_transaksi'] ?? '');
                    $netto = round((float)$j['total_debit'] * $factorNetto, 2);
                    if ($tgl < $monthStart) {
                        $summary['paid_before_month'] += $netto;
                    } elseif ($tgl <= $monthEnd) {
                        $summary['ku_cash_in_month'] += $netto;
                        $summary['total_paid_in_month'] += $netto;
                        if ($tgl > $lastPayDate) {
                            $lastPayDate = $tgl;
                        }
                        $methods[] = !empty($inv['pecah_metode_pembayaran']) ? $inv['pecah_metode_pembayaran'] : 'KU';
                    }
                }
            } elseif ((float)$inv['pecah_total_bayar'] > 0 && !empty($inv['pecah_tanggal_bayar'])) {
                $tgl = (string)$inv['pecah_tanggal_bayar'];
                $netto = (float)$inv['pecah_total_bayar'];
                if ($tgl < $monthStart) {
                    $summary['paid_before_month'] += $netto;
                } elseif ($tgl <= $monthEnd) {
                    $summary['ku_cash_in_month'] += $netto;
                    $summary['total_paid_in_month'] += $netto;
                    $lastPayDate = $tgl;
                    $methods[] = !empty($inv['pecah_metode_pembayaran']) ? $inv['pecah_metode_pembayaran'] : 'KU';
                }
            }

            if ($lastPayDate !== '') {
                $summary['tgl_pelunasan'] = $lastPayDate;
                $summary['tgl_regt_ku'] = $lastPayDate;
                $summary['diselesaikan_dg'] = implode(', ', array_unique($methods));
            }
            return $summary;
        }

        $idFaktur = (int)$inv['source_id'];
        $payments = $paymentsByKey[$idFaktur] ?? [];
        if (empty($payments)) {
            return $summary;
        }

        $noBgList = [];
        $tglJtBgList = [];
        $tglBgCairList = [];
        $methods = [];
        $lastPayDate = '';
        $lastKuDate = '';

        foreach ($payments as $p) {
            $tglBayar = (string)($p['tanggal_pembayaran'] ?? '');
            $jmlBayar = (float)($p['jumlah_pembayaran'] ?? 0);
            $jmlDiskon = (float)($p['jumlah_diskon'] ?? 0);
            $statusBg = strtolower(trim((string)($p['status_bg'] ?? 'not_bg')));
            $metode = trim((string)($p['metode_pembayaran'] ?? ''));
            $cara = strtolower(trim((string)($p['cara_pembayaran'] ?? '')));
            $noBg = trim((string)($p['no_bg'] ?? ''));
            $tglJtBg = trim((string)($p['tanggal_bg_cair'] ?? ''));
            $tglBgCair = !empty($p['bg_cair_at']) ? substr((string)$p['bg_cair_at'], 0, 10) : $tglJtBg;

            $isReturMetode = (
                stripos($metode, 'Retur') !== false ||
                stripos($metode, 'Hutang Non Dagang') !== false ||
                strtolower($metode) === 'retur'
            );
            $isBgEntry = ($statusBg === 'pending' || $statusBg === 'cair' || $cara === 'bg' || $noBg !== '');

            if ($tglBayar < $monthStart) {
                if ($statusBg !== 'pending') {
                    $summary['paid_before_month'] += ($jmlBayar + $jmlDiskon);
                }
                continue;
            }

            if ($tglBayar > $monthEnd) {
                continue;
            }

            // Mutasi dalam bulan berjalan
            if ($jmlDiskon > 0) {
                $summary['retur_pot_in_month'] += $jmlDiskon;
            }

            if ($statusBg === 'pending') {
                $summary['bg_pending_in_month'] += $jmlBayar;
                if ($noBg !== '') {
                    $noBgList[] = $noBg;
                }
                if ($tglJtBg !== '') {
                    $tglJtBgList[] = $tglJtBg;
                }
                $methods[] = 'BG';
            } elseif ($isReturMetode) {
                $summary['retur_pot_in_month'] += $jmlBayar;
                $summary['total_paid_in_month'] += ($jmlBayar + $jmlDiskon);
                $methods[] = $metode !== '' ? $metode : 'Retur';
            } elseif ($isBgEntry) {
                $summary['bg_cair_in_month'] += $jmlBayar;
                $summary['total_paid_in_month'] += ($jmlBayar + $jmlDiskon);
                if ($noBg !== '') {
                    $noBgList[] = $noBg;
                }
                if ($tglJtBg !== '') {
                    $tglJtBgList[] = $tglJtBg;
                }
                if ($tglBgCair !== '') {
                    $tglBgCairList[] = $tglBgCair;
                }
                $methods[] = 'BG';
            } else {
                $summary['ku_cash_in_month'] += $jmlBayar;
                $summary['total_paid_in_month'] += ($jmlBayar + $jmlDiskon);
                if ($tglBayar > $lastKuDate) {
                    $lastKuDate = $tglBayar;
                }
                $methods[] = $metode !== '' ? $metode : ($cara !== '' ? strtoupper($cara) : 'KU');
            }

            if ($statusBg !== 'pending' && $tglBayar > $lastPayDate) {
                $lastPayDate = $tglBayar;
            }
        }

        $summary['no_bg'] = implode(', ', array_unique($noBgList));
        $summary['tgl_jt_bg'] = !empty($tglJtBgList) ? end($tglJtBgList) : '';
        $summary['tgl_bg_cair'] = !empty($tglBgCairList) ? end($tglBgCairList) : '';
        $summary['tgl_pelunasan'] = $lastPayDate;
        $summary['tgl_regt_ku'] = $lastKuDate;
        $summary['diselesaikan_dg'] = implode(', ', array_unique($methods));

        return $summary;
    }

    /**
     * Menghitung selisih hari jatuh tempo terhadap tanggal acuan (Today - Tanggal JT).
     * Nilai negatif berarti belum jatuh tempo, nilai positif berarti melewati jatuh tempo.
     *
     * @param string $todayDate
     * @param string $jatuhTempoDate
     * @return int|string
     */
    private function calculate_bts_jt($todayDate, $jatuhTempoDate)
    {
        if ($jatuhTempoDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $jatuhTempoDate)) {
            return '';
        }

        $diffSeconds = strtotime($todayDate) - strtotime($jatuhTempoDate);
        return (int)round($diffSeconds / 86400);
    }

    /**
     * Memastikan tanggal acuan berformat YYYY-MM-DD yang valid.
     *
     * @param string $dateStr
     * @return string
     */
    private function normalize_date($dateStr)
    {
        $dateStr = trim((string)$dateStr);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }
        return date('Y-m-d');
    }
}

