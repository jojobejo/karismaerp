<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php // views/sales_order/index.php ?>

<?php $this->load->view('partial/main/navbar') ?>
<?php $this->load->view('partial/main/sidebar') ?>

<div class="container-fluid px-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-file-invoice"></i> Sales Order</h4>
    <a href="<?= site_url('sales_order/create') ?>" class="btn btn-primary">
      <i class="fas fa-plus"></i> Buat SO
    </a>
  </div>

  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= $this->session->flashdata('success') ?>
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= $this->session->flashdata('error') ?>
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show">
      <?= $this->session->flashdata('warning') ?>
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  <?php endif; ?>

  <!-- FILTER -->
  <div class="card mb-3">
    <div class="card-body py-2">
      <form method="get" action="<?= site_url('sales_order') ?>" class="form-inline flex-wrap gap-2">
        <div class="form-group mr-2 mb-1">
          <label class="mr-1 small">Dari</label>
          <input type="date" name="tanggal_dari" class="form-control form-control-sm"
            value="<?= htmlspecialchars($filter['tanggal_dari'] ?? '') ?>">
        </div>
        <div class="form-group mr-2 mb-1">
          <label class="mr-1 small">S/D</label>
          <input type="date" name="tanggal_ke" class="form-control form-control-sm"
            value="<?= htmlspecialchars($filter['tanggal_ke'] ?? '') ?>">
        </div>
        <div class="form-group mr-2 mb-1">
          <select name="customer_id" class="form-control form-control-sm">
            <option value="">-- Semua Customer --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c->id ?>"
                <?= ($filter['customer_id'] ?? '') == $c->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($c->nama_customer) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group mr-2 mb-1">
          <select name="status" class="form-control form-control-sm">
            <option value="">-- Semua Status --</option>
            <?php foreach (['draft','waiting_approval','approved','partial_delivered','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= ($filter['status'] ?? '') === $s ? 'selected' : '' ?>>
                <?= ucfirst(str_replace('_', ' ', $s)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-sm btn-secondary mb-1">
          <i class="fas fa-search"></i> Filter
        </button>
        <a href="<?= site_url('sales_order') ?>" class="btn btn-sm btn-outline-secondary mb-1">Reset</a>
      </form>
    </div>
  </div>

  <!-- TABLE -->
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>No SO</th>
              <th>Tanggal</th>
              <th>Customer</th>
              <th class="text-right">Item</th>
              <th class="text-right">Tonase (kg)</th>
              <th class="text-right">Kubikasi (m³)</th>
              <th class="text-center">Status</th>
              <th class="text-center">Nego</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($so_list)): ?>
              <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data</td></tr>
            <?php else: ?>
              <?php foreach ($so_list as $row): ?>
              <tr>
                <td><a href="<?= site_url('sales_order/detail/' . $row->id_so) ?>">
                  <?= htmlspecialchars($row->id_so) ?>
                </a></td>
                <td><?= date('d/m/Y', strtotime($row->tanggal_transaksi)) ?></td>
                <td><?= htmlspecialchars($row->customer_name) ?></td>
                <td class="text-right"><?= number_format($row->jumlah_item) ?></td>
                <td class="text-right"><?= number_format($row->total_tonase, 2) ?></td>
                <td class="text-right"><?= number_format($row->total_kubikasi, 4) ?></td>
                <td class="text-center">
                  <?php
                  $badge = [
                    'draft'            => 'secondary',
                    'waiting_approval' => 'warning',
                    'approved'         => 'info',
                    'partial_delivered'=> 'primary',
                    'completed'        => 'success',
                    'cancelled'        => 'danger',
                  ];
                  $b = $badge[$row->status] ?? 'secondary';
                  ?>
                  <span class="badge badge-<?= $b ?>">
                    <?= ucfirst(str_replace('_', ' ', $row->status)) ?>
                  </span>
                </td>
                <td class="text-center">
                  <?= $row->is_nego ? '<span class="badge badge-warning">Nego</span>' : '-' ?>
                </td>
                <td class="text-center">
                  <a href="<?= site_url('sales_order/detail/' . $row->id_so) ?>"
                     class="btn btn-xs btn-info" title="Detail">
                    <i class="fas fa-eye"></i>
                  </a>
                  <?php if ($row->status === 'draft'): ?>
                  <a href="<?= site_url('sales_order/edit/' . $row->id_so) ?>"
                     class="btn btn-xs btn-warning" title="Edit">
                    <i class="fas fa-pencil-alt"></i>
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
</div>

<?php $this->load->view('templates/footer'); ?>