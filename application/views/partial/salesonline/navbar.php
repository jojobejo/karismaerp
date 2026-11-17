<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid">

        <a href="<?php echo base_url('dashboard') ?>" class="navbar-brand">
            <img src="<?php echo base_url('assets/images/Karisma.png') ?>" 
                 class="brand-image img-circle elevation-2">
            <span class="brand-text font-weight-light">KIU KATALOG-PRICELIST</span>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" 
                data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarCollapse">

            <!-- Menu Kanan -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a href="<?php echo base_url('salesonline') ?>" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo base_url('logout') ?>" class="nav-link">Log Out</a>
                </li>
                <li class="nav-item">
                    <b><a href="#" class="nav-link">
                        Selamat Datang : <?php echo $this->session->userdata('nama_user') ?>
                    </a></b>
                </li>
            </ul>

        </div>
    </div>
</nav>