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
      <li class="nav-item">
        <a class="nav-link" href="<?= base_url('profile') ?>" title="Profile Setting">
          <i class="fas fa-user-cog"></i> <span class="d-none d-md-inline ml-1"><?= $this->session->userdata('nama') ?></span>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->