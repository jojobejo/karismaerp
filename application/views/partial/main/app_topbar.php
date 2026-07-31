<?php
$CI =& get_instance();
$dept = strtoupper(trim((string)($CI->session->userdata('departemen') ?: $CI->session->userdata('departement'))));
$jobdesk = strtoupper(trim((string)$CI->session->userdata('jobdesk')));
$moduleLabel = $dept ?: ($jobdesk ?: 'KARISMAERP');
$hasSidebar = !isset($karisma_topbar_has_sidebar) || $karisma_topbar_has_sidebar;
?>
<nav class="main-header navbar navbar-expand karisma-app-topbar">
  <ul class="navbar-nav align-items-center">
    <li class="nav-item">
      <?php if ($hasSidebar) : ?>
        <a class="nav-link karisma-topbar-toggle" data-widget="pushmenu" href="#" role="button" aria-label="Toggle menu">
          <i class="fas fa-th-large"></i>
        </a>
      <?php else : ?>
        <a class="nav-link karisma-topbar-toggle" href="<?= base_url('dashboard') ?>" aria-label="Dashboard">
          <i class="fas fa-th-large"></i>
        </a>
      <?php endif; ?>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('dashboard') ?>" class="nav-link karisma-topbar-title">Dashboard</a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto align-items-center">
    <li class="nav-item">
      <span class="nav-link karisma-topbar-module"><?= html_escape($moduleLabel) ?></span>
    </li>
    <li class="nav-item">
      <a href="<?= base_url('logout') ?>" class="nav-link karisma-topbar-logout" aria-label="Logout">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </li>
  </ul>
</nav>
