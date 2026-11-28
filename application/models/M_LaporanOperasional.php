<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_LaporanOperasional
 * Menyusun data laporan & export Excel transaksi operasional per periode
 * (Ringkasan, PO Detail, LPB Barang Datang, Faktur Penjualan, Retur Pembelian,
 *  Retur Penjualan, Pembayaran Customer, Pembayaran Supplier, Laba Rugi, Mutasi Akun).
 */
class M_LaporanOperasional extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('M_PenyesuaianBarang');
    }

    /**
     * Mengambil seluruh data transaksi operasional pada rentang tanggal tertentu.
     */
    public function get_operasional_report($dateFrom, $dateTo)
    {
        $dateFrom = $this->_normalizeDate($dateFrom, date('Y-m-01'));
        $dateTo   = $this->_normalizeDate($dateTo, date('Y-m-d'));
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $hppCache = [];
        $getMovingAvgHpp = function ($kdBarang, $gudangId = null) use (&$hppCache) {
            $kd = trim((string)$kdBarang);
            if ($kd === '') return 0.0;
            $key = $kd . '|' . (int)$gudangId;
            if (!array_key_exists($key, $hppCache)) {
                $hppCache[$key] = (float)$this->M_PenyesuaianBarang->get_item_hpp($kd, $gudangId ? (int)$gudangId : null);
            }
            return $hppCache[$key];
        };

        $poDetail           = $this->_getPoDetail($dateFrom, $dateTo);
        $lpbDetail          = $this->_getLpbDetail($dateFrom, $dateTo);
        $fakturPenjualan    = $this->_getFakturPenjualan($dateFrom, $dateTo, $getMovingAvgHpp);
        $returPembelian     = $this->_getReturPembelian($dateFrom, $dateTo);
        $returPenjualan     = $this->_getReturPenjualan($dateFrom, $dateTo, $getMovingAvgHpp);
        $pembayaranCustomer = $this->_getPembayaranCustomer($dateFrom, $dateTo);
        $pembayaranSupplier = $this->_getPembayaranSupplier($dateFrom, $dateTo);
        $labaRugi           = $this->_buildLabaRugi($fakturPenjualan, $returPenjualan);
        $mutasiAkun         = $this->_getMutasiAkun($dateFrom, $dateTo);
        $soOpenNominal      = $this->_getSoOpenNominal($dateFrom, $dateTo);

        $ringkasan = $this->_buildRingkasan(
            $dateFrom,
            $dateTo,
            $poDetail,
            $lpbDetail,
            $fakturPenjualan,
            $returPenjualan,
            $pembayaranCustomer,
            $pembayaranSupplier,
            $labaRugi,
            $soOpenNominal
        );

        return [
            'date_from'           => $dateFrom,
            'date_to'             => $dateTo,
            'ringkasan'           => $ringkasan,
            'po_detail'           => $poDetail,
            'lpb_detail'          => $lpbDetail,
            'faktur_penjualan'    => $fakturPenjualan,
            'retur_pembelian'     => $returPembelian,
            'retur_penjualan'     => $returPenjualan,
            'pembayaran_customer' => $pembayaranCustomer,
            'pembayaran_supplier' => $pembayaranSupplier,
            'laba_rugi'           => $labaRugi,
            'mutasi_akun'         => $mutasiAkun,
        ];
    }

    private function _normalizeDate($val, $fallback)
    {
        $val = trim((string)$val);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return $val;
        }
        return $fallback;
    }

    private function _getPoDetail($dateFrom, $dateTo)
    {
        if (!$this->db->table_exists('tbpo_po') || !$this->db->table_exists('tbpo_detail_po')) {
            return [];
        }

        $sql = "
            SELECT
                p.no_po,
                p.tgl_transaksi,
                COALESCE(NULLIF(s.nama_suplier, ''), p.kd_suplier, '-') AS nama_suplier,
                d.kd_barang,
                d.nama_barang,
                CASE WHEN COALESCE(d.qty_kecil, 0) > 0 THEN d.qty_kecil ELSE COALESCE(d.qty, 0) END AS qty,
                COALESCE(NULLIF(d.satuan, ''), 'Pcs') AS satuan,
                CASE
                    WHEN COALESCE(d.harga_satuan_kecil, 0) > 0 THEN d.harga_satuan_kecil
                    ELSE COALESCE(d.hrg_satuan, 0)
                END AS harga_po,
                COALESCE(NULLIF(d.keterangan_harga_ppn, ''), NULLIF(p.keterangan_harga_ppn, ''), '-') AS skema_ppn,
                CASE
                    WHEN COALESCE(d.total_harga_setelah_diskon, 0) > 0 THEN d.total_harga_setelah_diskon
                    WHEN COALESCE(d.hrg_total_diskon, 0) > 0 THEN d.hrg_total_diskon
                    ELSE COALESCE(d.hrg_total, 0)
                END AS nilai_barang
            FROM tbpo_po p
            JOIN tbpo_detail_po d ON d.kd_po = p.kd_po AND d.no_po = p.no_po
            LEFT JOIN tbpo_suplier s ON s.kd_suplier = p.kd_suplier
            WHERE p.tgl_transaksi >= ? AND p.tgl_transaksi <= ?
              AND LOWER(COALESCE(p.status, '')) NOT IN ('batal', 'cancelled', 'ditolak')
            ORDER BY p.tgl_transaksi ASC, p.no_po ASC, d.id_det_po ASC
        ";
        return $this->db->query($sql, [$dateFrom, $dateTo])->result_array();
    }

    private function _getLpbDetail($dateFrom, $dateTo)
    {
        if (!$this->db->table_exists('tb_lpb') || !$this->db->table_exists('tb_lpb_detail')) {
            return [];
        }

        // Menampilkan detail barang datang, harga barang, tanggal realisasi PO, dan sisa PO
        $sql = "
            SELECT
                COALESCE(l.tgl_sj, DATE(l.input_at)) AS tanggal_realisasi,
                COALESCE(NULLIF(l.no_po, ''), '-') AS no_po,
                COALESCE(NULLIF(l.nomor_lpb, ''), CONCAT('LPB #', l.id_lpb)) AS nomor_lpb,
                COALESCE(NULLIF(l.nama_suplier, ''), s.nama_suplier, '-') AS nama_suplier,
                ld.kd_barang,
                COALESCE(NULLIF(b.nama_barang, ''), NULLIF(mb.nm_barang, ''), ld.kd_barang) AS nama_barang,
                CASE
                    WHEN UPPER(COALESCE(g.DESKRIPSI, '')) LIKE '%BKPS%' THEN 'BKPS'
                    WHEN UPPER(SUBSTR(COALESCE(ld.kd_barang, ''), 1, 1)) = 'Q' THEN 'BKP'
                    WHEN UPPER(SUBSTR(COALESCE(ld.kd_barang, ''), 1, 1)) = 'A' THEN 'DAGANGAN'
                    WHEN UPPER(SUBSTR(COALESCE(ld.kd_barang, ''), 1, 1)) = 'Z' THEN 'PROMOSI'
                    ELSE '-'
                END AS kategori_pajak,
                COALESCE(po_item.qty_po, 0) AS qty_po,
                COALESCE(ld.qty_diterima, 0) AS qty_datang,
                GREATEST(0, COALESCE(po_item.qty_po, 0) - COALESCE(recv_all.total_datang, ld.qty_diterima, 0)) AS qty_sisa,
                COALESCE(NULLIF(mb.satuan, ''), 'Pcs') AS satuan,
                COALESCE(ld.harga_satuan, 0) AS harga_dpp,
                CASE
                    WHEN UPPER(COALESCE(g.DESKRIPSI, '')) LIKE '%BKPS%' THEN COALESCE(ld.harga_satuan, 0)
                    WHEN UPPER(SUBSTR(COALESCE(ld.kd_barang, ''), 1, 1)) = 'Q' THEN ROUND(COALESCE(ld.harga_satuan, 0) * 1.11, 2)
                    ELSE COALESCE(ld.harga_satuan, 0)
                END AS harga_inc_ppn,
                COALESCE(ld.total_harga, 0) AS nilai_datang,
                CASE
                    WHEN UPPER(COALESCE(g.DESKRIPSI, '')) LIKE '%BKPS%' THEN COALESCE(ld.total_harga, 0)
                    WHEN UPPER(SUBSTR(COALESCE(ld.kd_barang, ''), 1, 1)) = 'Q' THEN ROUND(COALESCE(ld.total_harga, 0) * 1.11, 2)
                    ELSE COALESCE(ld.total_harga, 0)
                END AS nilai_datang_inc,
                CASE
                    WHEN COALESCE(po_item.qty_po, 0) <= 0 THEN CONCAT('LPB ', COALESCE(NULLIF(l.nomor_lpb, ''), l.id_lpb))
                    WHEN COALESCE(recv_all.total_datang, 0) >= po_item.qty_po THEN CONCAT('Lengkap, LPB ', COALESCE(NULLIF(l.nomor_lpb, ''), l.id_lpb))
                    ELSE CONCAT('Sebagian datang, LPB ', COALESCE(NULLIF(l.nomor_lpb, ''), l.id_lpb))
                END AS status_realisasi
            FROM tb_lpb l
            JOIN tb_lpb_detail ld ON ld.id_lpb = l.id_lpb
            LEFT JOIN tbpo_barang b ON b.kode_barang = ld.kd_barang
            LEFT JOIN tbkeu_kelompok_dagang g ON g.NOINDEX = b.kelompok_dagang
            LEFT JOIN tb_master_barang mb ON mb.kode_barang = ld.kd_barang OR mb.kd_system = ld.kd_barang
            LEFT JOIN tbpo_po p ON p.kd_po = l.kd_po AND p.no_po = l.no_po
            LEFT JOIN tbpo_suplier s ON s.kd_suplier = COALESCE(NULLIF(l.kd_suplier, ''), p.kd_suplier)
            LEFT JOIN (
                SELECT no_po, kd_barang, SUM(CASE WHEN COALESCE(qty_kecil, 0) > 0 THEN qty_kecil ELSE COALESCE(qty, 0) END) AS qty_po
                FROM tbpo_detail_po
                GROUP BY no_po, kd_barang
            ) po_item ON po_item.no_po = l.no_po AND po_item.kd_barang = ld.kd_barang
            LEFT JOIN (
                SELECT l2.no_po, ld2.kd_barang, SUM(ld2.qty_diterima) AS total_datang
                FROM tb_lpb l2
                JOIN tb_lpb_detail ld2 ON ld2.id_lpb = l2.id_lpb
                WHERE l2.status_lpb = 1
                GROUP BY l2.no_po, ld2.kd_barang
            ) recv_all ON recv_all.no_po = l.no_po AND recv_all.kd_barang = ld.kd_barang
            WHERE l.status_lpb = 1
              AND COALESCE(l.tgl_sj, DATE(l.input_at)) >= ? AND COALESCE(l.tgl_sj, DATE(l.input_at)) <= ?
            ORDER BY tanggal_realisasi ASC, l.id_lpb ASC, ld.id_detail_lpb ASC
        ";
        return $this->db->query($sql, [$dateFrom, $dateTo])->result_array();
    }

    private function _getFakturPenjualan($dateFrom, $dateTo, callable $getMovingAvgHpp)
    {
        if (!$this->db->table_exists('tbso_faktur_penjualan') || !$this->db->table_exists('tbso_faktur_detail')) {
            return [];
        }

        $sql = "
            SELECT
                f.tanggal_faktur,
                f.no_faktur,
                f.gudang_id,
                COALESCE(NULLIF(f.customer_name, ''), c.nama_customer, '-') AS customer_name,
                COALESCE(NULLIF(f.cara_pembayaran, ''), 'cash') AS cara_pembayaran,
                COALESCE(f.jtempo, 0) AS jtempo,
                fd.kd_barang,
                fd.nama_barang,
                COALESCE(fd.qty, 0) AS qty,
                COALESCE(NULLIF(fd.satuan, ''), 'Pcs') AS satuan,
                COALESCE(fd.hrg_satuan, 0) AS hrg_satuan,
                COALESCE(fd.subtotal_after_disc, 0) AS dpp,
                COALESCE(fd.total_harga, 0) AS nilai_faktur,
                COALESCE(fd.hrg_pokok, 0) AS hrg_pokok_snapshot,
                COALESCE(b.hpp_average, 'T') AS hpp_average
            FROM tbso_faktur_penjualan f
            JOIN tbso_faktur_detail fd ON fd.id_faktur = f.id_faktur
            LEFT JOIN tb_customer c ON c.kd_customer = f.kd_customer
            LEFT JOIN tbpo_barang b ON b.kode_barang = fd.kd_barang
            WHERE f.tanggal_faktur >= ? AND f.tanggal_faktur <= ?
              AND LOWER(COALESCE(f.status, '')) NOT IN ('cancelled', 'draft')
            ORDER BY f.tanggal_faktur ASC, f.no_faktur ASC, fd.id ASC
        ";
        $rows = $this->db->query($sql, [$dateFrom, $dateTo])->result_array();

        foreach ($rows as &$r) {
            $qty = (float)$r['qty'];
            $dpp = (float)$r['dpp'];
            $nilaiFaktur = (float)$r['nilai_faktur'];
            $ppn = max(0.0, $nilaiFaktur - $dpp);

            // HPP wajib mengikuti moving average per gudang (sama dengan perhitungan jurnal akuntansi)
            $usesAvg = strtoupper(trim((string)$r['hpp_average'])) === 'T';
            $avgHpp = $usesAvg ? $getMovingAvgHpp($r['kd_barang'], $r['gudang_id']) : 0.0;
            $hppUnit = $avgHpp > 0 ? $avgHpp : (float)$r['hrg_pokok_snapshot'];
            $isZeroPrice = ((float)$r['hrg_satuan'] <= 0 || $dpp <= 0);
            $hppTotal = $isZeroPrice ? 0.0 : round($qty * $hppUnit, 2);

            $r['ppn_keluaran']     = $ppn;
            $r['hpp_unit']         = $hppUnit;
            $r['hpp_total']        = $hppTotal;
            $r['laba_kotor']       = round($dpp - $hppTotal, 2);
            $r['cara_bayar_tempo'] = ucfirst(strtolower((string)$r['cara_pembayaran'])) . ', ' . (int)$r['jtempo'] . ' hari';
        }
        unset($r);

        return $rows;
    }

    private function _getReturPembelian($dateFrom, $dateTo)
    {
        if (!$this->db->table_exists('tb_retur_pembelian') || !$this->db->table_exists('tb_retur_pembelian_detail')) {
            return [];
        }

        $sql = "
            SELECT
                rb.tanggal_retur,
                COALESCE(j.nomor_jurnal, '-') AS nomor_jurnal,
                rb.no_retur_pembelian,
                COALESCE(NULLIF(s.nama_suplier, ''), rb.kd_supplier, '-') AS nama_suplier,
                CONCAT(COALESCE(NULLIF(rb.no_po, ''), '-'), ' / ', COALESCE(NULLIF(l.nomor_lpb, ''), '-')) AS referensi,
                CONCAT(
                    COALESCE(rbd.kd_barang, ''),
                    ' ',
                    COALESCE(mb.nm_barang, rbd.kd_barang, ''),
                    CASE WHEN COALESCE(rbd.no_lot, '') != '' THEN CONCAT('; lot ', rbd.no_lot) ELSE '' END,
                    CASE WHEN rbd.expired_date IS NOT NULL THEN CONCAT(', exp. ', DATE_FORMAT(rbd.expired_date, '%d-%m-%Y')) ELSE '' END
                ) AS barang_lot,
                COALESCE(rbd.qty_retur, 0) AS qty_retur,
                COALESCE(NULLIF(mb.satuan, ''), 'Pcs') AS satuan,
                COALESCE(rbd.dpp, COALESCE(rbd.qty_retur, 0) * COALESCE(rbd.harga_satuan, 0)) AS dpp,
                COALESCE(rbd.ppn, 0) AS ppn,
                COALESCE(rbd.total, 0) AS total_retur
            FROM tb_retur_pembelian rb
            JOIN tb_retur_pembelian_detail rbd ON rbd.id_retur_pembelian = rb.id_retur_pembelian
            LEFT JOIN tbpo_suplier s ON s.kd_suplier = rb.kd_supplier
            LEFT JOIN tb_lpb l ON l.id_lpb = rb.id_lpb
            LEFT JOIN tb_master_barang mb ON mb.kode_barang = rbd.kd_barang OR mb.kd_system = rbd.kd_barang
            LEFT JOIN tbkeu_jurnal j ON j.id_jurnal = COALESCE(
                NULLIF(rb.id_jurnal, 0),
                (SELECT j2.id_jurnal FROM tbkeu_jurnal j2
                 WHERE j2.source_module = 'LOGISTIK' AND j2.source_type = 'RETUR_PEMBELIAN' AND j2.source_id = CAST(rb.id_retur_pembelian AS CHAR)
                 ORDER BY (CASE WHEN j2.status = 'POSTED' THEN 1 ELSE 2 END) ASC, j2.id_jurnal DESC
                 LIMIT 1)
            )
            WHERE rb.tanggal_retur >= ? AND rb.tanggal_retur <= ?
            ORDER BY rb.tanggal_retur ASC, rb.id_retur_pembelian ASC
        ";
        return $this->db->query($sql, [$dateFrom, $dateTo])->result_array();
    }

    private function _getReturPenjualan($dateFrom, $dateTo, callable $getMovingAvgHpp)
    {
        if (!$this->db->table_exists('tbrp_retur_penjualan_header') || !$this->db->table_exists('tbrp_retur_penjualan_detail')) {
            return [];
        }

        $sql = "
            SELECT
                rp.tanggal_retur,
                COALESCE(j.nomor_jurnal, '-') AS nomor_jurnal,
                rp.no_retur,
                COALESCE(NULLIF(rp.nama_customer, ''), '-') AS nama_customer,
                COALESCE(NULLIF(rpd.no_faktur, ''), NULLIF(rp.no_faktur_potong, ''), NULLIF(rp.no_spr, ''), '-') AS faktur_asal,
                rp.gudang_id,
                COALESCE(NULLIF(rpd.kd_barang, ''), mb.kode_barang, mb.kd_system, '') AS kd_barang,
                CONCAT(
                    COALESCE(NULLIF(rpd.kd_barang, ''), mb.kode_barang, mb.kd_system, ''),
                    CASE WHEN COALESCE(NULLIF(rpd.kd_barang, ''), mb.kode_barang, mb.kd_system, '') != '' THEN ' ' ELSE '' END,
                    COALESCE(rpd.nama_barang, ''),
                    CASE WHEN COALESCE(rpd.no_batch, '') != '' THEN CONCAT('; lot ', rpd.no_batch) ELSE '' END,
                    CASE WHEN rpd.expired_date IS NOT NULL THEN CONCAT(', exp. ', DATE_FORMAT(rpd.expired_date, '%d-%m-%Y')) ELSE '' END
                ) AS barang_lot,
                COALESCE(NULLIF(rp.catatan_admretur, ''), NULLIF(rp.catatan_logistik, ''), CONCAT('Retur ', UPPER(COALESCE(rp.tipe_retur, 'biasa')))) AS alasan,
                COALESCE(rpd.qty_retur, 0) AS qty_retur,
                COALESCE(NULLIF(rpd.satuan, ''), NULLIF(mb.satuan, ''), 'Pcs') AS satuan,
                (COALESCE(rpd.qty_retur, 0) * COALESCE(rpd.harga_satuan, 0)) AS nilai_retur,
                COALESCE(rp.sisa_saldo_retur, (COALESCE(rpd.qty_retur, 0) * COALESCE(rpd.harga_satuan, 0))) AS saldo_retur_aktif,
                COALESCE(b.hpp_average, 'T') AS hpp_average,
                COALESCE(rpd.harga_satuan, 0) AS harga_satuan
            FROM tbrp_retur_penjualan_header rp
            JOIN tbrp_retur_penjualan_detail rpd ON rpd.id_retur = rp.id_retur
            LEFT JOIN tb_master_barang mb ON (mb.kode_barang = rpd.kd_barang OR TRIM(LOWER(mb.nm_barang)) = TRIM(LOWER(rpd.nama_barang)))
            LEFT JOIN tbpo_barang b ON b.kode_barang = COALESCE(NULLIF(rpd.kd_barang, ''), mb.kode_barang, mb.kd_system)
            LEFT JOIN tbkeu_jurnal j ON j.id_jurnal = (
                SELECT j2.id_jurnal FROM tbkeu_jurnal j2
                WHERE j2.source_module = 'SALES' AND j2.source_type = 'RETUR_PENJUALAN'
                  AND (j2.source_no = rp.no_retur OR j2.source_id = CAST(rp.id_retur AS CHAR))
                ORDER BY (CASE WHEN j2.status = 'POSTED' THEN 1 ELSE 2 END) ASC, j2.id_jurnal DESC
                LIMIT 1
            )
            WHERE rp.tanggal_retur >= ? AND rp.tanggal_retur <= ?
              AND LOWER(COALESCE(rp.status_retur, '')) NOT IN ('ditolak', 'batal')
            ORDER BY rp.tanggal_retur ASC, rp.id_retur ASC
        ";
        $rows = $this->db->query($sql, [$dateFrom, $dateTo])->result_array();

        foreach ($rows as &$r) {
            $avgHpp = $getMovingAvgHpp($r['kd_barang'], $r['gudang_id']);
            $hppUnit = $avgHpp > 0 ? $avgHpp : (float)$r['harga_satuan'];
            $r['hpp_reversal'] = round((float)$r['qty_retur'] * $hppUnit, 2);
        }
        unset($r);

        return $rows;
    }

    private function _getPembayaranCustomer($dateFrom, $dateTo)
    {
        if (!$this->db->table_exists('tbkeu_pembayaran_faktur')) {
            return [];
        }

        $sql = "
            SELECT
                pf.tanggal_pembayaran,
                COALESCE(j.nomor_jurnal, CONCAT('BYR-', pf.id_pembayaran)) AS nomor_transaksi,
                CONCAT(
                    COALESCE(NULLIF(fp.customer_name, ''), c.nama_customer, 'Customer'),
                    CASE WHEN COALESCE(fp.kd_customer, '') != '' THEN CONCAT(' (', fp.kd_customer,
                        CASE WHEN COALESCE(c.nama_kios, '') != '' THEN CONCAT('; ', c.nama_kios) ELSE '' END,
                    ')') ELSE '' END
                ) AS customer_kios,
                COALESCE(pf.no_faktur, '-') AS no_faktur,
                CONCAT(
                    COALESCE(NULLIF(pf.cara_pembayaran, ''), NULLIF(pf.metode_pembayaran, ''), 'Cash'),
                    CASE WHEN COALESCE(pf.nama_bank, '') != '' THEN CONCAT(' - ', pf.nama_bank) ELSE '' END
                ) AS metode_rekening,
                COALESCE(
                    NULLIF(pf.keterangan, ''),
                    CASE WHEN COALESCE(pf.no_bg, '') != '' THEN CONCAT('BG ', pf.no_bg,
                        CASE WHEN pf.tanggal_bg_cair IS NOT NULL THEN CONCAT(', estimasi cair ', DATE_FORMAT(pf.tanggal_bg_cair, '%d-%m-%Y')) ELSE '' END
                    ) ELSE 'Pembayaran tunai/transfer' END
                ) AS rincian,
                (COALESCE(pf.jumlah_pembayaran, 0) + COALESCE(pf.jumlah_diskon, 0)) AS nilai,
                CASE
                    WHEN LOWER(COALESCE(pf.status_bg, '')) = 'pending' THEN 'PENDING'
                    ELSE UPPER(COALESCE(NULLIF(pf.status, ''), NULLIF(pf.status_kasir, ''), 'POSTED'))
                END AS status_bayar
            FROM tbkeu_pembayaran_faktur pf
            LEFT JOIN tbso_faktur_penjualan fp ON (
                CASE WHEN pf.id_faktur IS NOT NULL AND pf.id_faktur > 0 THEN fp.id_faktur = pf.id_faktur ELSE fp.no_faktur = pf.no_faktur END
            )
            LEFT JOIN tb_customer c ON c.kd_customer = fp.kd_customer
            LEFT JOIN tbkeu_jurnal j ON j.id_jurnal = (
                SELECT j2.id_jurnal FROM tbkeu_jurnal j2
                WHERE j2.source_module = 'KEUANGAN' AND j2.source_type = 'PEMBAYARAN_FAKTUR' AND j2.source_id = CAST(pf.id_pembayaran AS CHAR)
                ORDER BY (CASE WHEN j2.status = 'POSTED' THEN 1 ELSE 2 END) ASC, j2.id_jurnal DESC
                LIMIT 1
            )
            WHERE pf.tanggal_pembayaran >= ? AND pf.tanggal_pembayaran <= ?
            ORDER BY pf.tanggal_pembayaran ASC, pf.id_pembayaran ASC
        ";
        return $this->db->query($sql, [$dateFrom, $dateTo])->result_array();
    }

    private function _getPembayaranSupplier($dateFrom, $dateTo)
    {
        if (!$this->db->table_exists('tbkeu_pembayaran')) {
            return [];
        }

        $sql = "
            SELECT
                ps.tanggal_pembayaran,
                COALESCE(j.nomor_jurnal, '-') AS nomor_jurnal,
                COALESCE(NULLIF(s.nama_suplier, ''), 'Supplier') AS nama_suplier,
                COALESCE(
                    NULLIF((
                        SELECT GROUP_CONCAT(DISTINCT NULLIF(pa.invoice_no, '') ORDER BY pa.nomor_baris ASC SEPARATOR ', ')
                        FROM tbkeu_pembayaran_alokasi pa
                        WHERE pa.id_pembayaran = ps.id_pembayaran
                    ), ''),
                    NULLIF(ps.source_no, ''),
                    '-'
                ) AS no_lpb,
                COALESCE(NULLIF(ps.keterangan, ''), '-') AS rekening,
                COALESCE(ps.amount, 0) AS nilai,
                UPPER(COALESCE(ps.status, 'POSTED')) AS status_bayar,
                COALESCE(NULLIF(ps.nomor_pembayaran, ''), CONCAT('PBY-', ps.id_pembayaran)) AS no_bukti_bayar
            FROM tbkeu_pembayaran ps
            LEFT JOIN tbpo_suplier s ON s.id_suplier = ps.id_supplier
            LEFT JOIN tbkeu_jurnal j ON j.id_jurnal = COALESCE(
                NULLIF(ps.id_jurnal, 0),
                (SELECT j2.id_jurnal FROM tbkeu_jurnal j2
                 WHERE (j2.source_module = 'KEUANGAN' AND j2.source_type = 'SUPPLIER_PAYMENT'
                    AND (j2.source_id = CAST(ps.id_pembayaran AS CHAR) OR j2.source_id = ps.nomor_pembayaran OR j2.source_no = ps.nomor_pembayaran))
                    OR j2.idempotency_key = CONCAT('SUPPLIER_PAYMENT-', ps.nomor_pembayaran)
                 ORDER BY (CASE WHEN j2.status = 'POSTED' THEN 1 ELSE 2 END) ASC, j2.id_jurnal DESC
                 LIMIT 1)
            )
            WHERE ps.payment_type = 'SUPPLIER_PAYMENT'
              AND ps.tanggal_pembayaran >= ? AND ps.tanggal_pembayaran <= ?
            ORDER BY ps.tanggal_pembayaran ASC, ps.id_pembayaran ASC
        ";
        return $this->db->query($sql, [$dateFrom, $dateTo])->result_array();
    }

    private function _buildLabaRugi(array $fakturPenjualan, array $returPenjualan)
    {
        $perCustomer = [];
        $totalDpp = 0.0;
        $totalHpp = 0.0;

        foreach ($fakturPenjualan as $f) {
            $cust = $f['customer_name'] ?: 'Customer';
            if (!isset($perCustomer[$cust])) {
                $perCustomer[$cust] = [
                    'customer'    => $cust,
                    'penjualan'   => 0.0,
                    'retur'       => 0.0,
                    'hpp_faktur'  => 0.0,
                    'hpp_retur'   => 0.0,
                ];
            }
            $perCustomer[$cust]['penjualan']  += (float)$f['dpp'];
            $perCustomer[$cust]['hpp_faktur'] += (float)$f['hpp_total'];
            $totalDpp += (float)$f['dpp'];
            $totalHpp += (float)$f['hpp_total'];
        }

        $totalRetur = 0.0;
        $totalHppRetur = 0.0;
        foreach ($returPenjualan as $r) {
            $cust = $r['nama_customer'] ?: 'Customer';
            if (!isset($perCustomer[$cust])) {
                $perCustomer[$cust] = [
                    'customer'    => $cust,
                    'penjualan'   => 0.0,
                    'retur'       => 0.0,
                    'hpp_faktur'  => 0.0,
                    'hpp_retur'   => 0.0,
                ];
            }
            $perCustomer[$cust]['retur']     += (float)$r['nilai_retur'];
            $perCustomer[$cust]['hpp_retur'] += (float)$r['hpp_reversal'];
            $totalRetur    += (float)$r['nilai_retur'];
            $totalHppRetur += (float)$r['hpp_reversal'];
        }

        $customerRows = [];
        foreach ($perCustomer as $c) {
            $bersih = $c['penjualan'] - $c['retur'];
            $hppBersih = $c['hpp_faktur'] - $c['hpp_retur'];
            $laba = $bersih - $hppBersih;
            $margin = $bersih > 0 ? ($laba / $bersih) * 100 : 0.0;
            $customerRows[] = [
                'customer'         => $c['customer'],
                'penjualan_dpp'    => $c['penjualan'],
                'retur_dpp'        => $c['retur'],
                'penjualan_bersih' => $bersih,
                'hpp_faktur'       => $c['hpp_faktur'],
                'hpp_retur'        => $c['hpp_retur'],
                'hpp_bersih'       => $hppBersih,
                'laba_kotor'       => $laba,
                'margin'           => $margin,
            ];
        }

        $penjualanBersih = $totalDpp - $totalRetur;
        $hppBersih = $totalHpp - $totalHppRetur;
        $labaKotor = $penjualanBersih - $hppBersih;
        $margin = $penjualanBersih > 0 ? ($labaKotor / $penjualanBersih) * 100 : 0.0;

        return [
            'summary' => [
                'penjualan_dpp'    => $totalDpp,
                'retur_dpp'        => $totalRetur,
                'penjualan_bersih' => $penjualanBersih,
                'hpp_faktur'       => $totalHpp,
                'hpp_retur'        => $totalHppRetur,
                'hpp_bersih'       => $hppBersih,
                'laba_kotor'       => $labaKotor,
                'margin'           => $margin,
            ],
            'per_customer' => $customerRows,
        ];
    }

    private function _getMutasiAkun($dateFrom, $dateTo)
    {
        if (!$this->db->table_exists('tbkeu_jurnal') || !$this->db->table_exists('tbkeu_jurnal_detail')) {
            return [];
        }

        $sql = "
            SELECT
                a.kode_akun,
                a.nama_akun,
                COALESCE(SUM(jd.debit), 0) AS total_debit,
                COALESCE(SUM(jd.kredit), 0) AS total_kredit
            FROM tbkeu_jurnal j
            JOIN tbkeu_jurnal_detail jd ON jd.id_jurnal = j.id_jurnal
            JOIN tbkeu_akun a ON a.id_akun = jd.id_akun
            WHERE j.status = 'POSTED'
              AND j.tanggal_transaksi >= ? AND j.tanggal_transaksi <= ?
            GROUP BY a.id_akun, a.kode_akun, a.nama_akun
            ORDER BY a.kode_akun ASC
        ";
        $raw = $this->db->query($sql, [$dateFrom, $dateTo])->result_array();

        $neracaSaldo = [];
        $sumDebit = 0.0;
        $sumKredit = 0.0;
        $penjualanRows = [];
        $totalPenjualan = 0.0;
        $hppRows = [];
        $totalHpp = 0.0;

        foreach ($raw as $r) {
            $kode = trim((string)$r['kode_akun']);
            $nama = trim((string)$r['nama_akun']);
            $d = (float)$r['total_debit'];
            $k = (float)$r['total_kredit'];
            $firstChar = substr($kode, 0, 1);

            // Akun kepala 2, 3, dan 4 (non-retur) bersaldo normal Kredit; kepala 1, 5+, dan retur bersaldo normal Debit
            $isCreditNormal = in_array($firstChar, ['2', '3'], true) || ($firstChar === '4' && $k >= $d);

            if ($isCreditNormal) {
                $val = $k - $d;
                $debitSaldo = null;
                $kreditSaldo = $val;
                $sumKredit += $val;
            } else {
                $val = $d - $k;
                $debitSaldo = $val;
                $kreditSaldo = null;
                $sumDebit += $val;
            }

            $neracaSaldo[] = [
                'kode_akun'    => $kode,
                'nama_akun'    => $nama,
                'debit_saldo'  => $debitSaldo,
                'kredit_saldo' => $kreditSaldo,
            ];

            if ($firstChar === '4') {
                $netSales = $k - $d;
                $penjualanRows[] = ['kode_akun' => $kode, 'nama_akun' => $nama, 'nilai' => $netSales];
                $totalPenjualan += $netSales;
            } elseif (strpos($kode, '51') === 0 || $firstChar === '5') {
                $netHpp = $d - $k;
                $hppRows[] = ['kode_akun' => $kode, 'nama_akun' => $nama, 'nilai' => $netHpp];
                $totalHpp += $netHpp;
            }
        }

        return [
            'neraca_saldo'       => $neracaSaldo,
            'total_debit'        => $sumDebit,
            'total_kredit'       => $sumKredit,
            'penjualan_accounts' => $penjualanRows,
            'total_penjualan'    => $totalPenjualan,
            'hpp_accounts'       => $hppRows,
            'total_hpp'          => $totalHpp,
            'laba_kotor'         => $totalPenjualan - $totalHpp,
        ];
    }

    private function _getSoOpenNominal($dateFrom, $dateTo)
    {
        if (!$this->db->table_exists('tbso_sales_order') || !$this->db->table_exists('tbso_sales_order_detail')) {
            return 0.0;
        }

        $sql = "
            SELECT COALESCE(SUM(sod.total_harga), 0) AS nilai_open
            FROM tbso_sales_order so
            JOIN tbso_sales_order_detail sod ON sod.id_so = so.id_so
            WHERE DATE(so.tanggal_transaksi) >= ? AND DATE(so.tanggal_transaksi) <= ?
              AND LOWER(COALESCE(so.status, '')) IN ('draft', 'approval', 'approved', 'siap_faktur', 'partial', 'sedang_verifikasi')
              AND NOT EXISTS (
                  SELECT 1 FROM tbso_faktur_detail fd WHERE fd.id_so_detail = sod.id
              )
        ";
        $row = $this->db->query($sql, [$dateFrom, $dateTo])->row_array();
        return (float)($row['nilai_open'] ?? 0);
    }

    private function _buildRingkasan(
        $dateFrom,
        $dateTo,
        array $poDetail,
        array $lpbDetail,
        array $fakturPenjualan,
        array $returPenjualan,
        array $pembayaranCustomer,
        array $pembayaranSupplier,
        array $labaRugi,
        $soOpenNominal
    ) {
        $poUnik = count(array_unique(array_column($poDetail, 'no_po')));
        $lpbQty = array_sum(array_map(function ($r) { return (float)$r['qty_datang']; }, $lpbDetail));
        $nilaiFakturTotal = array_sum(array_map(function ($r) { return (float)$r['nilai_faktur']; }, $fakturPenjualan));
        $nilaiRetur = array_sum(array_map(function ($r) { return (float)$r['nilai_retur']; }, $returPenjualan));

        $totalBayarCust = 0.0;
        $bgPending = 0.0;
        foreach ($pembayaranCustomer as $pc) {
            $val = (float)$pc['nilai'];
            $totalBayarCust += $val;
            if (strtoupper((string)$pc['status_bayar']) === 'PENDING') {
                $bgPending += $val;
            }
        }
        $totalBayarSupp = array_sum(array_map(function ($r) { return (float)$r['nilai']; }, $pembayaranSupplier));

        return [
            ['indikator' => 'Periode Data', 'nilai' => date('d/m/Y', strtotime($dateFrom)) . ' s/d ' . date('d/m/Y', strtotime($dateTo))],
            ['indikator' => 'Purchase Order', 'nilai' => $poUnik . ' PO / ' . count($poDetail) . ' item'],
            ['indikator' => 'Barang diterima melalui LPB', 'nilai' => number_format($lpbQty, 2, ',', '.') . ' unit'],
            ['indikator' => 'Nilai penjualan terfaktur (termasuk PPN)', 'nilai' => 'Rp ' . number_format($nilaiFakturTotal, 2, ',', '.')],
            ['indikator' => 'Penjualan bersih (DPP)', 'nilai' => 'Rp ' . number_format($labaRugi['summary']['penjualan_bersih'], 2, ',', '.')],
            ['indikator' => 'Retur penjualan', 'nilai' => 'Rp ' . number_format($nilaiRetur, 2, ',', '.')],
            ['indikator' => 'HPP bersih (Moving Average)', 'nilai' => 'Rp ' . number_format($labaRugi['summary']['hpp_bersih'], 2, ',', '.')],
            ['indikator' => 'Laba kotor setelah retur', 'nilai' => 'Rp ' . number_format($labaRugi['summary']['laba_kotor'], 2, ',', '.')],
            ['indikator' => 'Margin laba kotor', 'nilai' => number_format($labaRugi['summary']['margin'], 2, ',', '.') . '%'],
            ['indikator' => 'Pembayaran customer tercatat', 'nilai' => 'Rp ' . number_format($totalBayarCust, 2, ',', '.')],
            ['indikator' => 'Di antaranya BG pending', 'nilai' => 'Rp ' . number_format($bgPending, 2, ',', '.')],
            ['indikator' => 'Pembayaran supplier', 'nilai' => 'Rp ' . number_format($totalBayarSupp, 2, ',', '.')],
            ['indikator' => 'SO open belum difaktur (nilai order)', 'nilai' => 'Rp ' . number_format($soOpenNominal, 2, ',', '.')],
        ];
    }
}
