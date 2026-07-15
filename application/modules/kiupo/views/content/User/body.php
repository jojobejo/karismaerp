<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">User</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item active">User</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
            <div>
                <?php $this->load->view('content/user/modal/modalUser') ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAddUser">
                    <i class="fas fa-plus"></i>
                    Tambah User Baru
                </button>
            </div>
            <table class="table table-bordered table-striped" id="tbuser">
                <thead>
                    <tr>
                        <td>Nama Pengguna</td>
                        <td>Username</td>
                        <td>Departement</td>
                        <td>role</td>
                        <td>aksi</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user as $s) : ?>
                        <tr>
                            <td><?= $s->nama_user ?></td>
                            <td><?= $s->username ?></td>
                            <td><?= $s->departement ?></td>
                            <td><?= $s->aksess_lv ?> </td>
                            <td>
                                <a href="#" class="btn btn-primary btn-sm " data-toggle="modal" data-target="#modalEditUser<?= $s->id_user ?>">
                                    <i class="fa fa-solid fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-warning btn-sm " data-toggle="modal" data-target="#modalEditUser<?= $s->id_user ?>">
                                    <i class="fa fa-solid fa-pencil-alt"></i>
                                </a>
                                <a href="#" class="btn btn-danger btn-sm " data-toggle="modal" data-target="#modalEditUser<?= $s->id_user ?>">
                                    <i class="fa fa-solid fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
</div>