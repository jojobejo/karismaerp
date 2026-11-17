<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
            <div class="container">
                <?php if ($this->session->userdata('hak_akses') == '1') : ?>
                    <a href="<?php echo base_url('dashboardsales') ?>" class="navbar-brand">
                        <img src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTE Logo" class="brand-image img-circle elevation-2" style="opacity: .8">
                        <span class="brand-text font-weight-light">KIU KATALOG-PRICELIST</span>
                    </a>
                <?php else : ?>
                    <a href="<?php echo base_url('dashboard') ?>" class="navbar-brand">
                        <img src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTE Logo" class="brand-image img-circle elevation-2" style="opacity: .8">
                        <span class="brand-text font-weight-light">KIU KATALOG-PRICELIST</span>
                    </a>
                <?php endif; ?>

                <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                    <!-- Left navbar links -->
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="<?php echo base_url('dashboardsales') ?>" class="nav-link">Home</a>
                        </li>
                        <?php if ($this->session->userdata('hak_akses') == '1') : ?>
                            <li class="nav-item">
                                <a href="#" class="nav-link">Katalog</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo base_url('user') ?>" class="nav-link">User</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="<?php echo base_url('logout') ?>" class="nav-link">Log Out</a>
                        </li>
                        <li class="nav-item">
                            <b><a href="#" class="nav-link"> Selamat Datang : <?php echo $this->session->userdata('nama_user') ?> </a></b>
                        </li>
                    </ul>

                </div>
        </nav>