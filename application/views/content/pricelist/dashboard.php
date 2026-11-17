<style>
    .content-wrapper {
        background: linear-gradient(180deg, #f7f8fb 0%, #eef2f7 100%);
    }

    .pricelist-page {
        padding: 1rem 1rem 2rem;
    }

    .pricelist-hero {
        max-width: 1100px;
        margin: 0 auto 1.25rem;
        padding: 1.4rem 1.2rem;
        border-radius: 22px;
        background: linear-gradient(135deg, #ffffff 0%, #f4f8fc 100%);
        box-shadow: 0 18px 40px rgba(36, 71, 107, 0.08);
    }

    .pricelist-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        line-height: 1.2;
        word-break: break-word;
    }

    .pricelist-subtitle {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        line-height: 1.3;
        word-break: break-word;
    }

    .pricelist-supplier {
        font-size: 1rem;
        margin-bottom: 0;
        color: #617285;
        line-height: 1.5;
        word-break: break-word;
    }

    .hero-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 1.25rem;
    }

    .hero-actions .btn {
        min-width: 210px;
        border-radius: 999px;
        font-weight: 600;
        padding: 10px 18px;
        box-shadow: 0 12px 28px rgba(36, 71, 107, 0.14);
    }

    .section-divider {
        max-width: 1100px;
        margin: 0 auto 1.75rem;
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.12);
    }

    .img-promo {
        width: 400px;
        height: 600px;
        margin: 5px;
    }

    .content-img {
        margin-bottom: 5px;
        text-align: center;
    }

    .dt-body-center {
        text-align: center;
    }

    .promo-wrapper {
        text-align: center;
    }

    .promo-card {
        height: 100%;
        padding: 1rem;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 18px 36px rgba(36, 71, 107, 0.08);
    }

    .promo-card-header {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 0.85rem;
        flex-wrap: wrap;
    }

    .promo-card-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .promo-image {
        max-width: 100%;
        width: 100%;
        height: auto;
        max-height: 430px;
        object-fit: contain;
        cursor: pointer;
        border-radius: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 18px 32px rgba(20, 40, 65, 0.12);
    }

    .promo-image:hover {
        transform: scale(1.05);
        box-shadow: 0 24px 40px rgba(20, 40, 65, 0.18);
    }

    .promo-empty {
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        margin-bottom: 0;
        border: 1px dashed #cad7e4;
        background: linear-gradient(135deg, #f6f9fc 0%, #eef4f8 100%);
    }

    /* Style untuk tabel pricelist */
    .table-pricelist {
        margin: 30px auto;
        max-width: 900px;
    }

    .table-pricelist th {
        background-color: #007bff;
        color: white;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }

    .table-pricelist td {
        text-align: center;
        vertical-align: middle;
    }

    .table-pricelist tbody tr:hover {
        background-color: #f5f5f5;
    }

    /* Style untuk modal gambar promo */
    .modal-promo-image {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }

    @media (max-width: 991.98px) {
        .pricelist-page {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .promo-grid > [class*="col-"] {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .pricelist-page {
            padding: 0.75rem 0.5rem 1.5rem;
        }

        .pricelist-hero {
            padding: 1.1rem 0.9rem;
            border-radius: 18px;
            margin-bottom: 1rem;
        }

        .pricelist-title {
            font-size: 1.45rem;
        }

        .pricelist-subtitle {
            font-size: 1.05rem;
        }

        .pricelist-supplier {
            font-size: 0.92rem;
        }

        .hero-actions {
            gap: 10px;
            margin-top: 1rem;
        }

        .hero-actions .btn {
            width: 100%;
            min-width: 0;
            padding: 11px 14px;
        }

        .section-divider {
            margin-bottom: 1rem;
        }

        .promo-card {
            padding: 0.85rem;
            border-radius: 18px;
        }

        .promo-card-header {
            min-height: 0;
            margin-bottom: 0.75rem;
        }

        .promo-card-title {
            font-size: 1rem;
        }

        .promo-image {
            max-height: 360px;
        }

        .promo-empty {
            min-height: 160px;
            font-size: 0.95rem;
            padding: 1rem;
        }

        .modal .modal-dialog {
            margin: 0.75rem;
        }

        .modal .modal-footer {
            gap: 8px;
        }

        .modal .modal-footer .btn {
            width: 100%;
        }
    }
</style>
<?php $this->load->view('partial/katalog/navbar') ?>
<?php $this->load->view('content/pricelist/modalAddImage') ?>
<?php $this->load->view('content/pricelist/editModalSpecial') ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">

            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content">
            <div class="pricelist-page">


            <?php $kodeID = $this->input->get('id'); ?>
            <?php $kodeKD = $this->input->get('kd'); ?>

            <div class="pricelist-hero text-center">
                <h1 class="pricelist-title">Pricelist - <?php echo $title['kode_barang'] ?></h1>
                <h4 class="pricelist-subtitle"><?php echo $title['nama_barang']; ?></h4>
                <h5 class="pricelist-supplier"><?php echo $title['nama_suplier']; ?></h5>
                <div class="hero-actions">
                    <div>
                        <a href="#" data-toggle="modal" data-target="#modalGambarProduk" class="text-center btn btn-primary" style="margin-top:-5px; font-weight: bold;">
                            Gambar Produk
                        </a>
                    </div>
                    <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '2' || $this->session->userdata('hak_akses') == '4') { ?>
                        <div>
                            <a href="#" data-toggle="modal" data-target="#modalUploadGambar" class="text-center btn btn-success" style="margin-top:-5px; font-weight: bold;">
                                Ganti Gambar Produk
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <hr class="section-divider" />

            <!-- GAMBAR PROMO -->
            <div class="row justify-content-center promo-grid">

                <?php
                $promos = [
                    ['label' => 'PROMO 1', 'key' => 'gbr_promo1', 'modal' => 'edit1', 'view_modal' => 'viewPromo1'],
                    ['label' => 'PROMO 2', 'key' => 'gbr_promo2', 'modal' => 'edit2', 'view_modal' => 'viewPromo2'],
                    ['label' => 'PROMO 3', 'key' => 'gbr_promo3', 'modal' => 'edit3', 'view_modal' => 'viewPromo3']
                ];

                foreach ($promos as $promo) :
                    $img = $title[$promo['key']];
                    $path = FCPATH . 'images/kontrak/' . $img;
                    $hasImage = ($img && $img !== '-' && file_exists($path));
                ?>

                    <div class="col-12 col-sm-10 col-md-4 promo-wrapper mb-4">
                        <div class="promo-card">

                        <!-- TITLE + BUTTON -->
                        <div class="promo-card-header">

                            <?php if ($hasImage) : ?>
                                <h4 class="promo-card-title"><?= $promo['label'] ?></h4>
                            <?php endif; ?>

                            <?php if ($this->session->userdata('hak_akses') == '1') : ?>
                                <a href="#" class="btn btn-success btn-sm" data-toggle="modal" data-target="#<?= $promo['modal'] . $kodeID ?>">
                                    <i class="fa fa-pencil-alt"></i> Edit
                                </a>
                            <?php endif; ?>

                        </div>

                        <!-- IMAGE OR EMPTY STATE -->
                        <?php if ($hasImage) : ?>

                            <a href="#" data-toggle="modal" data-target="#<?= $promo['view_modal'] ?>">
                                <img src="<?= base_url('images/kontrak/' . $img) ?>" class="promo-image" alt="<?= $promo['label'] ?>">
                            </a>

                        <?php else : ?>

                            <div class="alert alert-secondary py-4 promo-empty">
                                <strong>Belum ada kontrak jual</strong>
                            </div>

                        <?php endif; ?>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
            <!-- END GAMBAR PROMO -->

            <!-- TABEL PRICELIST -->
            <!-- <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <h3 class="text-center mb-4" style="font-weight: bold;">Daftar Harga</h3>
                        
                        <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalTambahPricelist">
                                    <i class="fa fa-plus"></i> Tambah Pricelist
                                </button>
                            </div>
                        <?php } ?>

                        <div class="table-responsive table-pricelist">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Satuan Barang</th>
                                        <th>R1</th>
                                        <th>R2</th>
                                        <th>Umum</th>
                                        <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                            <th>Aksi</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pricelist_data)) : ?>
                                        <?php foreach ($pricelist_data as $item) : ?>
                                            <tr>
                                                <td><?php echo $item['satuan']; ?></td>
                                                <td><?php echo $item['r1'] ? 'Rp ' . number_format($item['r1'], 0, ',', '.') : '-'; ?></td>
                                                <td><?php echo $item['r2'] ? 'Rp ' . number_format($item['r2'], 0, ',', '.') : '-'; ?></td>
                                                <td><?php echo $item['umum'] ? 'Rp ' . number_format($item['umum'], 0, ',', '.') : '-'; ?></td>
                                                <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEditPricelist<?php echo $item['id'] . '_' . $item['slot']; ?>">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalDeletePricelist<?php echo $item['id'] . '_' . $item['slot']; ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="<?php echo ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') ? '5' : '4'; ?>" class="text-center">
                                                <div class="alert alert-info mb-0">
                                                    Belum ada data pricelist
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- END TABEL PRICELIST -->

            <div class="card" hidden>
                <div class="card-body m-2">
                    <?php foreach ($prices as $pl) : ?>
                        <?php foreach ($hargas as $p) : ?>
                            <?php if ($pl->qty_1 > '0' || $pl->qty_2 > '0' || $pl->qty_3 > '0' || $pl->qty_4 > '0') : ?>
                                <div class="col-md">
                                    <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                        <button type="button" data-toggle="modal" data-whatever="<?php echo $title['nama_barang']; ?>" data-target="#modalAddQty" class="btn btn-block mb-2" style=" font-weight:bold;  background-color:#bce3c2">Tambah Pricelist</button>
                                    <?php } ?>
                                    <?php $this->load->view("content/pricelist/modalAddPricelist"); ?>
                                    <?php $this->load->view("content/pricelist/isiTablePrice"); ?>
                                <?php elseif ($p->total_item == 0 && $pl->qty_1 < 1) : ?>
                                    <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                        <button type="button" data-toggle="modal" data-whatever="<?php echo $title['nama_barang']; ?>" data-target="#modalAddQty" class="btn btn-block mb-2" style=" font-weight:bold;  background-color:#bce3c2">Tambah Pricelist</button>
                                    <?php } ?>
                                    <?php $this->load->view("content/pricelist/modalAddPricelist"); ?>
                                <?php endif; ?>
                                </div>
                                <?php if ($p->harga_r1 > '0' || $p->harga_r2 > '0' || $p->harga_program > '0' || $p->harga_online > '0') : ?>
                                    <div class="col-md">
                                        <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                            <button type="button" data-toggle="modal" data-whatever="super**<?php echo $title['nama_barang']; ?>" data-target="#addModalprice" class="btn btn-block mb-2" style="font-weight:bold; background-color:#8db3f0">Tambah Harga Ecer NET</button>
                                        <?php } ?>
                                        <?php $this->load->view("content/pricelist/modalAddSpecial"); ?>
                                        <?php $this->load->view("content/pricelist/isiCardPrice"); ?>
                                    </div>
                                <?php elseif ($pl->total_item == '0' && $p->harga_r1 < 1) : ?>
                                    <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                        <button type="button" data-toggle="modal" data-whatever="super**<?php echo $title['nama_barang']; ?>" data-target="#addModalprice" class="btn btn-block mb-2" style="font-weight:bold; background-color:#8db3f0">Tambah Harga Ecer NET</button>
                                    <?php } ?>
                                    <?php $this->load->view("content/pricelist/modalAddSpecial"); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                </div>
            </div>

            <!-- MODAL TAMBAH PRICELIST -->
            <!-- <div class="modal fade" id="modalTambahPricelist" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Pricelist</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <form action="<?php echo base_url('pricelist/add'); ?>" method="post">
                            <div class="modal-body">
                                <input type="hidden" name="id_barang" value="<?php echo $title['kode_barang']; ?>">
                                
                                <div class="form-group">
                                    <label>Satuan Barang</label>
                                    <input type="text" class="form-control" name="satuan" placeholder="Contoh: 1 pcs, 1-25 box" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Harga R1</label>
                                    <input type="number" class="form-control" name="r1" placeholder="Masukkan harga R1">
                                </div>
                                
                                <div class="form-group">
                                    <label>Harga R2</label>
                                    <input type="number" class="form-control" name="r2" placeholder="Masukkan harga R2">
                                </div>
                                
                                <div class="form-group">
                                    <label>Harga Umum</label>
                                    <input type="number" class="form-control" name="umum" placeholder="Masukkan harga umum">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> -->

            <!-- MODAL EDIT PRICELIST -->
            <!-- <?php if (!empty($pricelist_data)) : ?>
                <?php foreach ($pricelist_data as $item) : ?>
                    <div class="modal fade" id="modalEditPricelist<?php echo $item['id'] . '_' . $item['slot']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Pricelist</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <form action="<?php echo base_url('pricelist/edit'); ?>" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="id_pricelist" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="kode_barang" value="<?php echo $title['kode_barang']; ?>">
                                        <input type="hidden" name="qty_slot" value="<?php echo $item['slot']; ?>">
                                        
                                        <div class="form-group">
                                            <label>Satuan Barang</label>
                                            <input type="text" class="form-control" name="satuan" value="<?php echo $item['satuan']; ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Harga R1</label>
                                            <input type="number" class="form-control" name="r1" value="<?php echo $item['r1']; ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Harga R2</label>
                                            <input type="number" class="form-control" name="r2" value="<?php echo $item['r2']; ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Harga Umum</label>
                                            <input type="number" class="form-control" name="umum" value="<?php echo $item['umum']; ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?> -->

            <!-- MODAL DELETE PRICELIST -->
            <!-- <?php if (!empty($pricelist_data)) : ?>
                <?php foreach ($pricelist_data as $item) : ?>
                    <div class="modal fade" id="modalDeletePricelist<?php echo $item['id'] . '_' . $item['slot']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Hapus Pricelist</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <form action="<?php echo base_url('pricelist/delete_item'); ?>" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="id_pricelist" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="qty_slot" value="<?php echo $item['slot']; ?>">
                                        <input type="hidden" name="kode_barang" value="<?php echo $title['kode_barang']; ?>">
                                        
                                        <p class="mb-0">Apakah Anda yakin ingin menghapus pricelist untuk:</p>
                                        <div class="alert alert-warning mt-3">
                                            <strong>Satuan:</strong> <?php echo $item['satuan']; ?><br>
                                            <strong>R1:</strong> Rp <?php echo number_format($item['r1'], 0, ',', '.'); ?><br>
                                            <strong>R2:</strong> Rp <?php echo number_format($item['r2'], 0, ',', '.'); ?><br>
                                            <strong>Umum:</strong> Rp <?php echo number_format($item['umum'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?> -->

            <!-- MODAL VIEW PROMO 1 -->
            <div class="modal fade" id="viewPromo1" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">PROMO 1 - <?php echo $title['nama_barang']; ?></h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <?php 
                            $img1 = $title['gbr_promo1'];
                            $path1 = FCPATH . 'images/kontrak/' . $img1;
                            if ($img1 && $img1 !== '-' && file_exists($path1)) : 
                            ?>
                                <img src="<?php echo base_url('images/kontrak/' . $img1); ?>" alt="Promo 1" class="modal-promo-image">
                            <?php else : ?>
                                <div class="alert alert-warning">
                                    <strong>Belum ada gambar promo 1</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <?php if ($img1 && $img1 !== '-' && file_exists($path1)) : ?>
                                <a href="<?php echo base_url('images/kontrak/' . $img1); ?>" target="_blank" class="btn btn-primary">
                                    <i class="fa fa-download"></i> Buka di Tab Baru
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL VIEW PROMO 2 -->
            <div class="modal fade" id="viewPromo2" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">PROMO 2 - <?php echo $title['nama_barang']; ?></h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <?php 
                            $img2 = $title['gbr_promo2'];
                            $path2 = FCPATH . 'images/kontrak/' . $img2;
                            if ($img2 && $img2 !== '-' && file_exists($path2)) : 
                            ?>
                                <img src="<?php echo base_url('images/kontrak/' . $img2); ?>" alt="Promo 2" class="modal-promo-image">
                            <?php else : ?>
                                <div class="alert alert-warning">
                                    <strong>Belum ada gambar promo 2</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <?php if ($img2 && $img2 !== '-' && file_exists($path2)) : ?>
                                <a href="<?php echo base_url('images/kontrak/' . $img2); ?>" target="_blank" class="btn btn-primary">
                                    <i class="fa fa-download"></i> Buka di Tab Baru
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL VIEW PROMO 3 -->
            <div class="modal fade" id="viewPromo3" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">PROMO 3 - <?php echo $title['nama_barang']; ?></h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <?php 
                            $img3 = $title['gbr_promo3'];
                            $path3 = FCPATH . 'images/kontrak/' . $img3;
                            if ($img3 && $img3 !== '-' && file_exists($path3)) : 
                            ?>
                                <img src="<?php echo base_url('images/kontrak/' . $img3); ?>" alt="Promo 3" class="modal-promo-image">
                            <?php else : ?>
                                <div class="alert alert-warning">
                                    <strong>Belum ada gambar promo 3</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <?php if ($img3 && $img3 !== '-' && file_exists($path3)) : ?>
                                <a href="<?php echo base_url('images/kontrak/' . $img3); ?>" target="_blank" class="btn btn-primary">
                                    <i class="fa fa-download"></i> Buka di Tab Baru
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL EDIT GAMBAR PRODUK -->
            <!-- MODAL GANTI GAMBAR PRODUK -->
            <div class="modal fade" id="modalUploadGambar" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Ganti Gambar Produk</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">

                            <?php echo form_open_multipart('pricelist/gambarproduk_br'); ?>

                            <input type="hidden" name="id_bar" value="<?php echo $title['id_barang']; ?>">
                            <input type="hidden" name="kode_barang_isi" value="<?php echo $title['kode_barang']; ?>">
                            <input type="hidden" name="nama_barang_isi" value="<?php echo $title['nama_barang']; ?>">

                            <!-- Upload -->
                            <label>Upload Gambar Produk Baru</label>
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" id="gambar_1" name="gambar_1" accept="image/*" required>
                                <label class="custom-file-label" for="gambar_1">Pilih Gambar</label>
                            </div>

                        </div>

                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Upload</button>
                        </div>

                        </form>

                    </div>
                </div>
            </div>



            <!-- MODAL GAMBAR PRODUK -->
            <div class="modal fade" id="modalGambarProduk" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Gambar Produk</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body text-center">
                            <img src="<?php echo base_url('images/produk/' . $title['gbr_produk']); ?>" alt="Gambar Produk" class="img-fluid rounded shadow">
                        </div>

                    </div>
                </div>
            </div>


        </div>
            </div>
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
