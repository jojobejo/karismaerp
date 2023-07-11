  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-2">
    <!-- Brand Logo -->
    <a href="<?php echo base_url('admin') ?>" class="brand-link">
      <img src="<?php echo base_url("assets/images/Karisma.png") ?>" style="width: 50px; height: 30px;" alt="Karisma Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Halo , <?= $this->session->userdata('nama_user') ?><br></span>
    </a>


    <?php $idsession = $this->session->userdata('akses_lv') ?>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
          with font-awesome or any other icon font library -->

          <!-- START AKSES LV 1 -->
          <?php if ($this->session->userdata('akses_lv') == '1') : ?>
            <li class="nav-item">
              <a href="<?php echo base_url('inventaris') ?>" class="nav-link">
                <i class="nav-icon fas fa-desktop"></i>
                <p>
                  Inventaris
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('faktur_pending') ?>" class="nav-link">
                <i class="nav-icon far fa-life-ring"></i>
                <p>
                  Ticketing
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('userAdmin') ?>" class="nav-link">
                <i class="nav-icon fa fa-user"></i>
                <p>
                  User List
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>
                  Log Out
                </p>
              </a>
            </li>
            <!-- END AKSES LV 1 -->

            <!-- START AKSES LV 2 -->
          <?php elseif ($this->session->userdata('akses_lv') == '2') : ?>
            <li class="nav-item">
              <a href="<?php echo base_url('#') ?>" class="nav-link">
                <i class="nav-icon fas fa-desktop"></i>
                <p>
                  Inventaris Saya
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('#') ?>" class="nav-link">
                <i class="nav-icon fa fa-clock"></i>
                <p>
                  Maintenance Ticketing
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('#') ?>" class="nav-link">
                <i class="nav-icon fa fa-user"></i>
                <p>
                  User Setting
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>
                  Log Out
                </p>
              </a>
            </li>
          <?php endif; ?>
          <!-- END AKSES LV 2 -->
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>