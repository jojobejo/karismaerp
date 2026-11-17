<style>
    .tcenter {
        text-align: center;
    }

    .dt-body-center {
        text-align: center;
    }

    .table-striped>tbody>tr:nth-child(2n+1)>td,
    .table-striped>tbody>tr:nth-child(2n+1)>th {
        background-color: #FFFBF5;
    }
</style>
<?php $this->load->view('partial/katalog/navbar') ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="background-color:  #F5F5F5;">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container">
            <div class="row">

            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
     <?php $this->load->view('content/katalog/modal') ?> 

        <!-- Main content -->
        <div class="content mr-1 ml-1" style="margin-top: -15px;">
            <div class="row">
                <div class="col-lg">
                    <div class="card">
                        <div class="card-body" style="background-color: white;">
                            <div>
                                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#tambahbarang"><i class="fas fa-plus"></i>&nbsp;Tambah Data</button>
                            </div>
                            <table id="example2" class="table table-bordered table-striped display mt-2" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th hidden>Kode Barang</th>
                                        <th class="tcenter" style="background-color: #6096B4; color: white;" width="50">Produk Fokus</th>
                                        <th class="tcenter" style="background-color: #6096B4; color: white;">Nama Barang</th>
                                        <th class="tcenter" style="background-color: #6096B4; color: white;">Nama Suplier</th>
                                        <th class="tcenter" style="background-color: #6096B4; color: white;">Kelompok Bahan</th>
                                        <th class="tcenter" style="background-color: #6096B4; color: white;">Bahan Aktif</th>
                                        <th class="tcenter" style="background-color: #6096B4; color: white;">Gambar Produk</th>
                                        <th class="tcenter" style="background-color: #6096B4; color: white;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="dt-body-center">

                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <!-- Default to the left -->
    <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong> All rights reserved.
</footer>
</div>
<!-- ./wrapper -->