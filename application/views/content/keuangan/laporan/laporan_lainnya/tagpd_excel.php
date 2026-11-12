<?php
/**
 * Template Export Excel (.xls) untuk Laporan TAGPD (Daftar Tagihan Piutang).
 */
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="utf-8">
    <style>
        body, table { font-family: Arial, sans-serif; font-size: 11px; }
        th { background-color: #d9e8f5; font-weight: bold; text-align: center; vertical-align: middle; }
        .tagpd-head-letters th { background-color: #f1f5f9; color: #475569; font-size: 10px; }
        .tagpd-total-row td { background-color: #e2e8f0; font-weight: bold; }
        .text-right { text-align: right; mso-number-format: "\#\,\#\#0"; }
        .text-center { text-align: center; }
        .font-weight-bold { font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="12" style="font-weight:bold;">Jangka Waktu JT</td>
            <td style="font-weight:bold;">Today:</td>
            <td style="font-weight:bold;"><?= html_escape($today_date) ?></td>
            <td colspan="32"></td>
        </tr>
        <tr>
            <td colspan="46" style="font-size:14px; font-weight:bold;">Daftar Tagihan Piutang</td>
        </tr>
    </table>
    <?php
    $is_excel = true;
    include APPPATH . 'views/content/keuangan/laporan/laporan_lainnya/_tagpd_table.php';
    ?>
</body>
</html>

