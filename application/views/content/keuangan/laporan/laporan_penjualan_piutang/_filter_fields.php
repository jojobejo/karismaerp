<?php
$selected = $selected ?? [];
$renderSelect = static function ($key, $label, array $options) use ($selected) {
    ?>
    <div class="form-group row align-items-center mb-2">
        <label for="sales-filter-<?= $key ?>" class="col-sm-4 mb-1 mb-sm-0"><?= $label ?></label>
        <div class="col-sm-8">
            <select class="form-control form-control-sm" id="sales-filter-<?= $key ?>" name="<?= $key ?>">
                <option value="">Semua</option>
                <?php foreach ($options as $value => $name): ?>
                    <option value="<?= html_escape($value) ?>" <?= (string) $value === ($selected[$key] ?? '') ? 'selected' : '' ?>><?= html_escape($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php
};
?>
<fieldset class="mb-3">
    <legend class="h6"><?= $filter_type === 'advance' ? 'Pelanggan / Supplier' : 'Pelanggan' ?></legend>
    <?php $renderSelect('customer', 'Nama', $filter_options['customer']); ?>
    <?php if ($filter_type !== 'advance'): ?>
        <?php $renderSelect('customer_class', 'Klasifikasi', $filter_options['customer_class']); ?>
    <?php endif; ?>
</fieldset>
<?php if ($filter_type === 'sales'): ?>
    <fieldset class="mb-3">
        <legend class="h6">Salesman</legend>
        <?php $renderSelect('salesman', 'Karyawan', $filter_options['salesman']); ?>
        <?php $renderSelect('salesman_class', 'Klasifikasi', $filter_options['salesman_class']); ?>
    </fieldset>
<?php endif; ?>
<fieldset class="mb-3">
    <legend class="h6">Periode</legend>
    <div class="form-group row align-items-end mb-0">
        <span class="col-sm-4 mb-1">Tanggal</span>
        <div class="col-sm-8">
            <div class="form-row align-items-end">
                <div class="col mb-1">
                    <label class="small mb-1" for="filterStartDate">Dari</label>
                    <input type="date" class="form-control form-control-sm" id="filterStartDate" name="start_date" required value="<?= html_escape($selected['start_date'] ?? date('Y-m-01')) ?>">
                </div>
                <div class="col-auto pb-2" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
                <div class="col mb-1">
                    <label class="small mb-1" for="filterEndDate">Hingga</label>
                    <input type="date" class="form-control form-control-sm" id="filterEndDate" name="end_date" required value="<?= html_escape($selected['end_date'] ?? date('Y-m-t')) ?>">
                </div>
            </div>
        </div>
    </div>
</fieldset>
<?php if ($filter_type === 'efaktur'): ?>
    <?php $renderSelect('export_status', 'Status Ekspor', ['recorded' => 'Ekspor tercatat', 'missing' => 'Ekspor belum tercatat']); ?>
    <?php $renderSelect('serial_status', 'Nomor Seri', ['recorded' => 'Ada nomor seri', 'missing' => 'Tanpa nomor seri']); ?>
<?php else: ?>
    <?php $renderSelect('status', 'Status', $filter_options['status']); ?>
<?php endif; ?>
