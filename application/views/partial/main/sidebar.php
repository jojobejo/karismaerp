  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-2">
    <!-- Brand Logo -->
    <a href="<?php echo base_url('dashboard') ?>" class="brand-link">
      <img src="<?php echo base_url("assets/images/Karisma.png") ?>" style="width: 50px; height: 30px;" alt="Karisma Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Halo , <?= $this->session->userdata('nama') ?><br></span>
    </a>

    <!-- Sidebar -->
    <?php if ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMINKEU') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan') ?>" class="nav-link">
                <i class="nav-icon fas fa-quran"></i>
                <p>
                  Daily Stock Product
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
        </nav>
        <!-- /.sidebar-menu -->
      </div>

    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMINGA') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('schedule_direktur') ?>" class="nav-link">
                <i class="nav-icon fas fa-book"></i>
                <p>
                  Jadwal Tamu
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
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '5' && $this->session->userdata('jobdesk') == 'DIREKTUR') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan') ?>" class="nav-link">
                <i class="nav-icon fas fa-book"></i>
                <p>
                  Daily Stock Product
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('schedule_direktur') ?>" class="nav-link">
                <i class="nav-icon fas fa-user-friends"></i>
                <p>
                  Jadwal Tamu
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
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('logistik') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('create_do') ?>" class="nav-link">
                <i class="nav-icon fas fa-pen-fancy"></i>
                <p>
                  Create DO
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
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMINICS') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('ics/ics_diffrent') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('ics/master_barang') ?>" class="nav-link">
                <i class="nav-icon fas fa-database"></i>
                <p>
                  Master Barang
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
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'STOCKOPNAME') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('s') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
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
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
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
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '2' && $this->session->userdata('jobdesk') == 'ADMKEU') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('kmt/dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/omset') ?>" class="nav-link">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>Omset</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/operasional') ?>" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>Operasional</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/dca') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>DCA</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/promo') ?>" class="nav-link">
                <i class="nav-icon fas fa-bullhorn"></i>
                <p>Promo / Peralatan</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/others') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Others</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Log Out</p>
              </a>
            </li>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'KADEPKS') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('kmt/dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/omset') ?>" class="nav-link">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>Omset</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/operasional') ?>" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>Operasional</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/dca') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>DCA</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/promo') ?>" class="nav-link">
                <i class="nav-icon fas fa-bullhorn"></i>
                <p>Promo / Peralatan</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/gaji') ?>" class="nav-link">
                <i class="nav-icon fas fa-money-bill-wave"></i>
                <p>Gaji</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/others') ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Others</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Log Out</p>
              </a>
            </li>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '3' && $this->session->userdata('jobdesk') == 'ABM') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('kmt/dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/operasional') ?>" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>Operasional</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('kmt/dca') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-alt"></i>
                <p>DCA</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Log Out</p>
              </a>
            </li>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php else : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>
                  Log Out
                </p>
              </a>
            </li>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php endif; ?>

    <!-- /.sidebar -->
  </aside>