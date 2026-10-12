<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
          <i class="fas fa-user-circle fa-lg mr-1 text-secondary"></i>
          <span class="d-none d-md-inline font-weight-bold text-dark"><?= htmlspecialchars($this->session->userdata('nama') ?: 'User') ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow-sm border-0">
          <div class="dropdown-item dropdown-header bg-light text-center py-3">
            <h6 class="font-weight-bold mb-1 text-dark"><?= htmlspecialchars($this->session->userdata('nama')) ?></h6>
            <span class="badge badge-primary px-2 py-1"><?= htmlspecialchars($this->session->userdata('jobdesk')) ?></span>
            <?php if ($this->session->userdata('nik')): ?>
              <div class="text-muted small mt-1">NIK: <?= htmlspecialchars($this->session->userdata('nik')) ?></div>
            <?php endif; ?>
          </div>
          <div class="dropdown-divider"></div>
          <a href="<?= base_url('profile') ?>" class="dropdown-item py-2">
            <i class="fas fa-user-cog mr-2 text-primary"></i> Profile Setting
          </a>
          <div class="dropdown-divider"></div>
          <a href="<?= base_url('logout') ?>" class="dropdown-item py-2 text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Log Out
          </a>
        </div>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->