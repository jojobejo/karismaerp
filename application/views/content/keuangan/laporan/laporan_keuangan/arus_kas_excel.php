<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($report_title) ?></title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .title-company { font-size: 14pt; font-weight: bold; }
        .title-report { font-size: 16pt; font-weight: bold; color: #007ba4; }
        .title-period { font-size: 11pt; font-weight: bold; }
        .sec-header { font-weight: bold; color: #007ba4; font-size: 12pt; }
        .subtotal { font-weight: bold; color: #007ba4; border-top: 1px solid #000; }
        .grand-total { font-weight: bold; color: #007ba4; }
        .num { mso-number-format:"\#\,\#\#0\.00"; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="4" class="text-center title-company"><?= htmlspecialchars($company_name) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="text-center title-report"><?= htmlspecialchars($report_title) ?> (<?= $is_rincian ? 'Rincian' : 'Rangkuman' ?>)</td>
        </tr>
        <tr>
            <td colspan="4" class="text-center title-period"><?= date('d F Y', strtotime($start_date)) ?> - <?= date('d F Y', strtotime($end_date)) ?></td>
        </tr>
        <tr><td></td></tr>

        <!-- TOP ITEMS -->
        <?php if (!empty($data['top_items'])): ?>
            <?php foreach ($data['top_items'] as $cat): ?>
                <?php if ($is_rincian): ?>
                    <tr>
                        <td colspan="3" class="font-bold" style="color: #007ba4;"><?= htmlspecialchars($cat['name']) ?></td>
                        <td></td>
                    </tr>
                    <?php foreach ($cat['accounts'] as $acc): ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['kode_akun']) ?></td>
                            <td colspan="2"><?= htmlspecialchars($acc['nama_akun']) ?></td>
                            <td class="text-right num"><?= number_format($acc['total'], 2, '.', '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="subtotal">Total <?= htmlspecialchars($cat['name']) ?> :</td>
                        <td class="text-right subtotal num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="3"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="text-right num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- OPERATING ACTIVITIES -->
        <tr>
            <td colspan="4" class="sec-header">Operating Activities</td>
        </tr>
        <?php if (!empty($data['operating'])): ?>
            <?php foreach ($data['operating'] as $cat): ?>
                <?php if ($is_rincian): ?>
                    <tr>
                        <td colspan="3" class="font-bold" style="color: #007ba4; padding-left: 20px;"><?= htmlspecialchars($cat['name']) ?></td>
                        <td></td>
                    </tr>
                    <?php foreach ($cat['accounts'] as $acc): ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['kode_akun']) ?></td>
                            <td colspan="2"><?= htmlspecialchars($acc['nama_akun']) ?></td>
                            <td class="text-right num"><?= number_format($acc['total'], 2, '.', '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="subtotal">Total <?= htmlspecialchars($cat['name']) ?> :</td>
                        <td class="text-right subtotal num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding-left: 20px;"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="text-right num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <tr>
            <td colspan="3" class="subtotal" style="color: #007ba4;">Total Operating Activities :</td>
            <td class="text-right subtotal num"><?= number_format($data['total_operating'], 2, '.', '') ?></td>
        </tr>

        <!-- INVESTING ACTIVITIES -->
        <tr>
            <td colspan="4" class="sec-header">Investing Activities</td>
        </tr>
        <?php if (!empty($data['investing'])): ?>
            <?php foreach ($data['investing'] as $cat): ?>
                <?php if ($is_rincian): ?>
                    <tr>
                        <td colspan="3" class="font-bold" style="color: #007ba4; padding-left: 20px;"><?= htmlspecialchars($cat['name']) ?></td>
                        <td></td>
                    </tr>
                    <?php foreach ($cat['accounts'] as $acc): ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['kode_akun']) ?></td>
                            <td colspan="2"><?= htmlspecialchars($acc['nama_akun']) ?></td>
                            <td class="text-right num"><?= number_format($acc['total'], 2, '.', '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="subtotal">Total <?= htmlspecialchars($cat['name']) ?> :</td>
                        <td class="text-right subtotal num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding-left: 20px;"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="text-right num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <tr>
            <td colspan="3" class="subtotal" style="color: #007ba4;">Total Investing Activities :</td>
            <td class="text-right subtotal num"><?= number_format($data['total_investing'], 2, '.', '') ?></td>
        </tr>

        <!-- FINANCING ACTIVITIES -->
        <tr>
            <td colspan="4" class="sec-header">Financing Activities</td>
        </tr>
        <?php if (!empty($data['financing'])): ?>
            <?php foreach ($data['financing'] as $cat): ?>
                <?php if ($is_rincian): ?>
                    <tr>
                        <td colspan="3" class="font-bold" style="color: #007ba4; padding-left: 20px;"><?= htmlspecialchars($cat['name']) ?></td>
                        <td></td>
                    </tr>
                    <?php foreach ($cat['accounts'] as $acc): ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['kode_akun']) ?></td>
                            <td colspan="2"><?= htmlspecialchars($acc['nama_akun']) ?></td>
                            <td class="text-right num"><?= number_format($acc['total'], 2, '.', '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="subtotal">Total <?= htmlspecialchars($cat['name']) ?> :</td>
                        <td class="text-right subtotal num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="padding-left: 20px;"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="text-right num"><?= number_format($cat['total'], 2, '.', '') ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <tr>
            <td colspan="3" class="subtotal" style="color: #007ba4;">Total Financing Activities :</td>
            <td class="text-right subtotal num"><?= number_format($data['total_financing'], 2, '.', '') ?></td>
        </tr>

        <tr><td></td></tr>
        <tr>
            <td colspan="2" class="grand-total">Total Keluar/Masuk Kas :</td>
            <td class="grand-total">Rp</td>
            <td class="text-right grand-total num"><?= number_format($data['total_keluar_masuk'], 2, '.', '') ?></td>
        </tr>
        <tr>
            <td colspan="2" class="grand-total">Saldo Awal :</td>
            <td class="grand-total">Rp</td>
            <td class="text-right grand-total num"><?= number_format($data['saldo_awal'], 2, '.', '') ?></td>
        </tr>
        <tr>
            <td colspan="2" class="grand-total">Saldo Akhir :</td>
            <td class="grand-total" style="border-top: 1px solid #000;">Rp</td>
            <td class="text-right grand-total num" style="border-top: 1px solid #000;"><?= number_format($data['saldo_akhir'], 2, '.', '') ?></td>
        </tr>
    </table>
</body>
</html>
