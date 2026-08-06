<div class="text-center mb-3">
    <h5><strong>FORM PENGAJUAN OD</strong></h5>
</div>

<p>Pesanan barang-barang sebagai berikut :</p>

<div class="table-responsive">
    <table class="table table-bordered table-sm" style="font-size: 13px;">
        <thead class="text-center bg-light">
            <tr>
                <th>No.</th>
                <th>BARANG</th>
                <th>Jumlah</th>
                <th>Tgl Faktur</th>
                <th>No Faktur</th>
                <th>Cust.</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1; 
            $total = 0;
            
            // Calculate rowspans
            $faktur_counts = [];
            foreach ($details as $d) {
                if (!isset($faktur_counts[$d['no_faktur']])) {
                    $faktur_counts[$d['no_faktur']] = 0;
                }
                $faktur_counts[$d['no_faktur']]++;
            }
            
            $printed_faktur = [];
            
            foreach ($details as $i => $d) : 
                $subtotal = (float)$d['total_harga'];
                $total += $subtotal;
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= htmlspecialchars((string)$d['nama_barang']) ?></td>
                <td class="text-right"><?= number_format($subtotal, 2, '.', ',') ?></td>
                
                <?php if (!isset($printed_faktur[$d['no_faktur']])) : ?>
                    <td rowspan="<?= $faktur_counts[$d['no_faktur']] ?>" class="text-center" style="vertical-align: middle;">
                        <?= date('d-M-y', strtotime($d['tanggal_faktur'])) ?>
                    </td>
                    <td rowspan="<?= $faktur_counts[$d['no_faktur']] ?>" style="vertical-align: middle;">
                        <?= $d['no_faktur'] ?>
                    </td>
                    <?php $printed_faktur[$d['no_faktur']] = true; ?>
                <?php endif; ?>
                
                <?php if ($i == 0) : ?>
                    <td rowspan="<?= count($details) ?>" style="vertical-align: middle;">
                        <?= htmlspecialchars((string)$pengajuan['customer_name']) ?>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            
            <tr class="font-weight-bold bg-light">
                <td colspan="2" class="text-center">Total</td>
                <td class="text-right"><?= number_format($total, 2, '.', ',') ?></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="mt-2" style="font-size: 14px;">
    <strong>CATATAN :</strong><br>
    <div>
        <?= nl2br(htmlspecialchars((string)$pengajuan['catatan'])) ?>
    </div>
</div>

<hr>
<h5>Log Approval</h5>
<ul>
    <li><strong>Mng SC:</strong> <?= $pengajuan['approval_mngsc_by'] ?: '-' ?> (<?= $pengajuan['approval_mngsc_at'] ?: '-' ?>) <br> Catatan: <?= $pengajuan['catatan_mngsc'] ?></li>
    <li><strong>Mng TC:</strong> <?= $pengajuan['approval_mngtc_by'] ?: '-' ?> (<?= $pengajuan['approval_mngtc_at'] ?: '-' ?>) <br> Catatan: <?= $pengajuan['catatan_mngtc'] ?></li>
    <li><strong>Kadep SC:</strong> <?= $pengajuan['approval_kadepsc_by'] ?: '-' ?> (<?= $pengajuan['approval_kadepsc_at'] ?: '-' ?>) <br> Catatan: <?= $pengajuan['catatan_kadepsc'] ?></li>
</ul>

<?php 
$lampiran = $pengajuan['lampiran_sc'] ?: $pengajuan['lampiran_mngtc'];
if ($lampiran) : 
?>
<div class="mt-3">
    <strong>Lampiran:</strong><br>
    <a href="<?= base_url($lampiran) ?>" target="_blank" class="btn btn-sm btn-info mb-1"><i class="fas fa-image"></i> Lihat Lampiran</a>
</div>
<?php endif; ?>
