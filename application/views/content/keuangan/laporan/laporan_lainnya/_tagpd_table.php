<?php
/**
 * Partial tabel 46 kolom TAGPD (Daftar Tagihan Piutang) untuk tampilan web maupun export Excel.
 *
 * Variabel yang diharapkan:
 * - $rows           array daftar baris TAGPD
 * - $totals         array total ringkasan nominal
 * - $column_letters array daftar huruf kolom A..AT (46 item)
 * - $is_excel       bool true jika dirender untuk file Excel
 */
$isExcel = !empty($is_excel);
$formatMoney = function ($val) use ($isExcel) {
    if ($val === '' || $val === null) {
        return '';
    }
    $num = (float)$val;
    if ($isExcel) {
        return number_format($num, 0, '.', '');
    }
    return number_format($num, 0, ',', '.');
};
?>
<table id="<?= $isExcel ? 'tagpdExcelTable' : 'tagpdTable' ?>" class="<?= $isExcel ? '' : 'tagpd-table' ?>" <?= $isExcel ? 'border="1" cellspacing="0" cellpadding="4"' : '' ?>>
    <thead>
        <tr class="tagpd-head-row1">
            <th rowspan="2" class="sticky-col col-no">No</th>
            <th rowspan="2">A/Q</th>
            <th rowspan="2">Tanggal Faktur</th>
            <th rowspan="2">Tanggal Tax</th>
            <th rowspan="2" class="sticky-col col-faktur">No. Faktur</th>
            <th rowspan="2">FP</th>
            <th rowspan="2">Plafon</th>
            <th rowspan="2">Sales</th>
            <th rowspan="2">Collection</th>
            <th rowspan="2">Tgl Masuk Kios Baru</th>
            <th>Metode</th>
            <th rowspan="2">KODE</th>
            <th rowspan="2">Nama Customer/Toko</th>
            <th rowspan="2">Nama Customer</th>
            <th colspan="2">TAX</th>
            <th colspan="2">Pengiriman</th>
            <th rowspan="2">Wilayah1</th>
            <th rowspan="2">SE</th>
            <th rowspan="2">Jatuh Tempo</th>
            <th rowspan="2">Tanggal JT</th>
            <th rowspan="2">Bts JT (hr)</th>
            <th rowspan="2">Saldo Awal (Rp)</th>
            <th rowspan="2">Saldo Awal/Faktur (Rp)</th>
            <th rowspan="2">Jumlah/Faktur (Rp)</th>
            <th colspan="7">Pelunasan</th>
            <th rowspan="2">Tanggal Pelunasan</th>
            <th>Diselesaikan dg</th>
            <th rowspan="2">Tgl Regt. (KU)</th>
            <th rowspan="2">Saldo PD (Rp)</th>
            <th>Saldo PD/Faktur</th>
            <th rowspan="2">Program Bonus OTP</th>
            <th rowspan="2">Keterangan</th>
            <th rowspan="2">PH/Bonus/ Faktur</th>
            <th rowspan="2">Jumlah PH/Bonus/ Faktur</th>
            <th colspan="3" rowspan="2">Cocokan Saldo Tax/Non Tax</th>
            <th rowspan="2">Bts JT (Hari)</th>
        </tr>
        <tr class="tagpd-head-row2">
            <th>Bayar</th>
            <th>Alamat</th>
            <th>Kota</th>
            <th>Alamat</th>
            <th>Kota</th>
            <th>Retur, Pot, Bonus</th>
            <th>BG</th>
            <th>Tgl JT BG</th>
            <th>No. BG</th>
            <th>BG Cair</th>
            <th>Tgl BG Cair</th>
            <th>KU/Cash</th>
            <th>ket byr</th>
            <th>Rp.</th>
        </tr>
        <tr class="tagpd-head-letters">
            <?php foreach ($column_letters as $idx => $letter) : ?>
                <th class="<?= $idx === 0 ? 'sticky-col col-no' : ($idx === 4 ? 'sticky-col col-faktur' : '') ?>"><?= html_escape($letter) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rows)) : ?>
            <tr>
                <td colspan="46" class="text-center py-4 text-muted">Tidak ada data tagihan piutang untuk filter yang dipilih.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($rows as $r) :
                $btsClass = '';
                if (!$isExcel && $r['bts_jt_hr'] !== '') {
                    $btsVal = (int)$r['bts_jt_hr'];
                    if ($btsVal > 0 && (float)$r['saldo_pd'] > 0) {
                        $btsClass = 'text-danger font-weight-bold';
                    } elseif ($btsVal <= 0 && (float)$r['saldo_pd'] > 0) {
                        $btsClass = 'text-success';
                    }
                }
            ?>
                <tr>
                    <td class="text-center sticky-col col-no"><?= (int)$r['no'] ?></td>
                    <td class="text-center"><?= html_escape($r['a_q']) ?></td>
                    <td class="text-center text-nowrap"><?= html_escape($r['tanggal_faktur']) ?></td>
                    <td class="text-center text-nowrap"><?= html_escape($r['tanggal_tax']) ?></td>
                    <td class="text-nowrap sticky-col col-faktur font-weight-bold"><?= html_escape($r['no_faktur']) ?></td>
                    <td><?= html_escape($r['fp']) ?></td>
                    <td class="text-right"><?= $formatMoney($r['plafon']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['sales']) ?></td>
                    <td><?= html_escape($r['collection']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['tgl_masuk_kios_baru']) ?></td>
                    <td class="text-center"><?= html_escape($r['metode_bayar']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['kode_customer']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['nama_customer_toko']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['nama_customer']) ?></td>
                    <td class="col-address"><?= html_escape($r['tax_alamat']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['tax_kota']) ?></td>
                    <td class="col-address"><?= html_escape($r['pengiriman_alamat']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['pengiriman_kota']) ?></td>
                    <td><?= html_escape($r['wilayah1']) ?></td>
                    <td><?= html_escape($r['se']) ?></td>
                    <td class="text-center"><?= $r['jatuh_tempo'] !== '' ? (int)$r['jatuh_tempo'] : '' ?></td>
                    <td class="text-center text-nowrap"><?= html_escape($r['tanggal_jt']) ?></td>
                    <td class="text-center <?= $btsClass ?>"><?= $r['bts_jt_hr'] !== '' ? (int)$r['bts_jt_hr'] : '' ?></td>
                    <td class="text-right"><?= $formatMoney($r['saldo_awal']) ?></td>
                    <td class="text-right"><?= $formatMoney($r['saldo_awal_faktur']) ?></td>
                    <td class="text-right"><?= $formatMoney($r['jumlah_faktur']) ?></td>
                    <td class="text-right"><?= $formatMoney($r['pelunasan_retur_pot']) ?></td>
                    <td class="text-right"><?= $formatMoney($r['pelunasan_bg']) ?></td>
                    <td class="text-center text-nowrap"><?= html_escape($r['tgl_jt_bg']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['no_bg']) ?></td>
                    <td class="text-right"><?= $formatMoney($r['pelunasan_bg_cair']) ?></td>
                    <td class="text-center text-nowrap"><?= html_escape($r['tgl_bg_cair']) ?></td>
                    <td class="text-right"><?= $formatMoney($r['pelunasan_ku_cash']) ?></td>
                    <td class="text-center text-nowrap"><?= html_escape($r['tanggal_pelunasan']) ?></td>
                    <td class="text-nowrap"><?= html_escape($r['diselesaikan_dg']) ?></td>
                    <td class="text-center text-nowrap"><?= html_escape($r['tgl_regt_ku']) ?></td>
                    <td class="text-right font-weight-bold"><?= $formatMoney($r['saldo_pd']) ?></td>
                    <td class="text-right font-weight-bold"><?= $formatMoney($r['saldo_pd_faktur']) ?></td>
                    <td><?= html_escape($r['program_bonus_otp']) ?></td>
                    <td class="col-address"><?= html_escape($r['keterangan']) ?></td>
                    <td><?= html_escape($r['ph_bonus_faktur']) ?></td>
                    <td class="text-right"><?= html_escape($r['jumlah_ph_bonus_faktur']) ?></td>
                    <td class="text-right"><?= html_escape($r['cocokan_saldo_1']) ?></td>
                    <td class="text-right"><?= html_escape($r['cocokan_saldo_2']) ?></td>
                    <td class="text-right"><?= html_escape($r['cocokan_saldo_3']) ?></td>
                    <td class="text-center <?= $btsClass ?>"><?= $r['bts_jt_hari'] !== '' ? (int)$r['bts_jt_hari'] : '' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="tagpd-total-row">
            <td colspan="23" class="text-right font-weight-bold">TOTAL</td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['saldo_awal'] ?? 0) ?></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['saldo_awal_faktur'] ?? 0) ?></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['jumlah_faktur'] ?? 0) ?></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['pelunasan_retur_pot'] ?? 0) ?></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['pelunasan_bg'] ?? 0) ?></td>
            <td></td>
            <td></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['pelunasan_bg_cair'] ?? 0) ?></td>
            <td></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['pelunasan_ku_cash'] ?? 0) ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['saldo_pd'] ?? 0) ?></td>
            <td class="text-right font-weight-bold"><?= $formatMoney($totals['saldo_pd_faktur'] ?? 0) ?></td>
            <td colspan="8"></td>
        </tr>
    </tfoot>
</table>

