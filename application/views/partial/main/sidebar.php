  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-2">
    <!-- Brand Logo -->
    <a href="<?php echo base_url('dashboard') ?>" class="brand-link">
      <img src="<?php echo base_url("assets/images/Karisma.png") ?>" style="width: 50px; height: 30px;" alt="Karisma Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Halo , <?= $this->session->userdata('nama') ?><br></span>
    </a>

    <?php 
    $allowed_roles = ['DIREKTURCK', 'ADMLOG', 'MANAGERWH', 'SALESCK', 'MANAGERCK'];
    $CI =& get_instance();
    $hasMasterUserManagementAccess = (strtolower((string) $CI->session->userdata('username')) === 'admin');
    $dynamicSidebarTree = [];
    if (isset($CI->db) && $CI->db->table_exists('tb_menu')) {
      $CI->load->model('master/M_Menu', 'sidebarMenuModel');
      $dynamicSidebarTree = $CI->sidebarMenuModel->sidebar_tree($CI->session->userdata('akses_lv_id') ?: $CI->session->userdata('lv'));
    }

    $renderDynamicMenu = function ($menus) use (&$renderDynamicMenu) {
      foreach ($menus as $menu) {
        $hasChildren = !empty($menu['children']);
        $url = $hasChildren ? '#' : base_url($menu['url']);
        ?>
        <li class="nav-item <?= $hasChildren ? 'has-treeview' : '' ?>">
          <a href="<?= $url ?>" class="nav-link">
            <i class="nav-icon <?= html_escape($menu['icon'] ?: 'fas fa-circle') ?>"></i>
            <p>
              <?= html_escape($menu['nama_menu']) ?>
              <?php if ($hasChildren) : ?><i class="right fas fa-angle-left"></i><?php endif; ?>
            </p>
          </a>
          <?php if ($hasChildren) : ?>
            <ul class="nav nav-treeview">
              <?php $renderDynamicMenu($menu['children']); ?>
            </ul>
          <?php endif; ?>
        </li>
        <?php
      }
    };
    ?>

    <?php if ($hasMasterUserManagementAccess) : ?>
      <div class="sidebar pb-0">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item has-treeview">
              <a href="#" class="nav-link">
                <i class="nav-icon fas fa-user-shield"></i>
                <p>
                  Master User
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?= base_url('master/user-management') ?>" class="nav-link">
                    <i class="fas fa-users nav-icon"></i>
                    <p>User Management</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url('master/jobdesk') ?>" class="nav-link">
                    <i class="fas fa-briefcase nav-icon"></i>
                    <p>Jobdesk</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url('master/akses-level') ?>" class="nav-link">
                    <i class="fas fa-key nav-icon"></i>
                    <p>Akses Level</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="<?= base_url('master/menu') ?>" class="nav-link">
                    <i class="fas fa-bars nav-icon"></i>
                    <p>Menu</p>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <?php if (!empty($dynamicSidebarTree)) : ?>
      <div class="sidebar">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <?php $renderDynamicMenu($dynamicSidebarTree); ?>
            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Log Out</p>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMINKEU') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan') ?>" class="nav-link">
                <i class="nav-icon fas fa-quran"></i>
                <p>
                  Daily Stock Product
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('jurnal') ?>" class="nav-link">
                <i class="nav-icon fas fa-book-open"></i>
                <p>
                  Jurnal
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan/pembayaran-supplier') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>
                  Pembayaran Supplier
              <a href="<?php echo base_url('keuangan/buku_besar') ?>" class="nav-link">
                <i class="nav-icon fas fa-book"></i>
                <p>
                  Buku Besar
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan/kas_keluar') ?>" class="nav-link">
                <i class="nav-icon fas fa-money-check-alt"></i>
                <p>
                  Kas Keluar
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan/kas_masuk') ?>" class="nav-link">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>
                  Kas Masuk
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

    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'KIUKEU') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan/pembayaran') ?>" class="nav-link">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>
                  Pembayaran Faktur
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('retur_penjualan/retur') ?>" class="nav-link">
                <i class="nav-icon fas fa-undo-alt"></i>
                <p>
                  Retur Penjualan
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan/pembayaran-supplier') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>
                  Pembayaran Supplier
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
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
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
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
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
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
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
    <?php elseif ($this->session->userdata('lv') == '1' && in_array(strtoupper((string)$this->session->userdata('jobdesk')), ['SC', 'KIUSCC', 'SALESCOUNTER'], true)) : ?>
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

          <li class="nav-item">
            <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <!-- Sales Order -->
          <li class="nav-item">
            <a href="<?php echo base_url('sales_order') ?>" class="nav-link">
              <i class="nav-icon fas fa-shopping-cart"></i>
              <p>Sales Order</p>
            </a>
          </li>

          <!-- Logout -->
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
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMINICS') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
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
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('stockopname/input') ?>" class="nav-link">
                <i class="nav-icon fas fa-mobile-alt"></i>
                <p>
                  Input Opname
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
    <?php elseif ($this->session->userdata('lv') == '1' && in_array(str_replace(['-', ' '], '_', strtoupper((string)$this->session->userdata('jobdesk'))), ['SUPERVISIOR_OPNAME', 'SUPERVISOR_OPNAME'], true)) : ?>
      <div class="sidebar">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i><p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('supervisi-opname') ?>" class="nav-link">
                <i class="nav-icon fas fa-clipboard-check"></i><p>Supervisi Opname</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('supervisi-opname/tracking') ?>" class="nav-link">
                <i class="nav-icon fas fa-map-marked-alt"></i><p>Tracking Inputer Wilayah</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i><p>Log Out</p>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan/pembayaran-supplier') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>
                  Pembayaran Supplier
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
      <?php elseif ($this->session->userdata('lv') == '1' && in_array($this->session->userdata('jobdesk'), $allowed_roles)) : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('checker') ?>" class="nav-link">
                <i class="nav-icon fas fa-warehouse"></i>
                <p>
                  Warehouse Activity
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
    <?php elseif ($this->session->userdata('lv') == '1' && in_array(strtoupper((string)$this->session->userdata('jobdesk')), ['ADMINSC'], true)) : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('sales_order/admin_sc') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>
                  Admin SC
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
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && in_array($this->session->userdata('jobdesk'), ['ADMPNJ', 'KADEPUB', 'KADEPSC', 'MANAGERSC', 'KADEP'])) : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('retur_penjualan') ?>" class="nav-link">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>
                  Daftar SPR
                </p>
              </a>
            </li>
            <?php if ($this->session->userdata('jobdesk') !== 'ADMPNJ') : ?>
            <li class="nav-item">
              <a href="<?php echo base_url('retur_penjualan/retur') ?>" class="nav-link">
                <i class="nav-icon fas fa-undo-alt"></i>
                <p>
                  Retur Penjualan
                </p>
              </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>
                  Log Out
                </p>
              </a>
            </li>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && in_array($this->session->userdata('jobdesk'), ['ADMRETUR', 'COLLECTION', 'KASIR', 'MANAGERACC', 'MANAGERSE', 'DIREKTUROP', 'DIREKTURUTAMA'])) : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('retur_penjualan/retur') ?>" class="nav-link">
                <i class="nav-icon fas fa-undo-alt"></i>
                <p>
                  Retur Penjualan
                </p>
              </a>
            </li>
            <?php if ($this->session->userdata('jobdesk') == 'KASIR') : ?>
            <li class="nav-item">
              <a href="<?php echo base_url('keuangan/pembayaran/kasir') ?>" class="nav-link">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>
                  Pembayaran Kasir
                </p>
              </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a href="<?php echo base_url('logout') ?>" class="nav-link">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>
                  Log Out
                </p>
              </a>
            </li>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php elseif ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') == 'ADMLPB2') : ?>
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
                <i class="nav-icon fas fa-home"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo base_url('retur_penjualan/admlpb2') ?>" class="nav-link">
                <i class="nav-icon fas fa-truck-loading"></i>
                <p>
                  SPR Siap Retur
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
              <a href="<?php echo base_url('dashboard') ?>" class="nav-link">
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
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    <?php endif; ?>

    <!-- /.sidebar -->
  </aside>
